@extends('layouts.app')

@section('title', 'Laporan')

@push('styles')
<style>
    .stat-card {
        background: black;
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .stat-card h3 {
        font-size: 28px;
        margin-bottom: 5px;
    }
</style>
@endpush

@section('content')
<div class="row" id="dashboardStats">
    <div class="col-md-3">
        <div class="stat-card">
            <h3 id="totalProduk">-</h3>
            <p>Total Produk</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h3 id="produkHabis">-</h3>
            <p>Produk Habis</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h3 id="penjualanHariIni">-</h3>
            <p>Penjualan Hari Ini</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h3 id="transaksiHariIni">-</h3>
            <p>Transaksi Hari Ini</p>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-black">
                <h5 class="mb-0">Produk Terlaris</h5>
            </div>
            <div class="card-body">
                <div id="bestSellersList">Loading...</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header  text-black">
                <h5 class="mb-0">Produk Tidak Laku</h5>
            </div>
            <div class="card-body">
                <div id="slowMoversList">Loading...</div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-black">
                <h5 class="mb-0">Produk Jelek/Kadaluarsa</h5>
            </div>
            <div class="card-body">
                <div id="damagedList">Loading...</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-black">
                <h5 class="mb-0">Rekap Penjualan per Kasir</h5>
            </div>
            <div class="card-body">
                <div id="cashierSalesList">Loading...</div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
function formatRupiah(num) {
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function loadDashboardStats() {
    $.get('{{ route("reports.dashboard-stats") }}', function(data) {
        $('#totalProduk').text(data.total_produk);
        $('#produkHabis').text(data.produk_habis);
        $('#penjualanHariIni').text(formatRupiah(data.penjualan_hari_ini));
        $('#transaksiHariIni').text(data.jumlah_transaksi_hari_ini);
    });
}

function loadBestSellers() {
    $.get('{{ route("reports.best-sellers") }}', function(data) {
        let html = '<table class="table table-sm">';
        data.forEach(item => {
            html += `<tr><td>${item.nama_produk}</td><td class="text-center">${item.total_terjual} terjual</td><td class="text-end">${formatRupiah(item.total_omset)}</td></tr>`;
        });
        html += '</table>';
        if(data.length === 0) html = '<p class="text-muted">Belum ada data</p>';
        $('#bestSellersList').html(html);
    });
}

function loadSlowMovers() {
    $.get('{{ route("reports.slow-movers") }}', function(data) {
        let html = '<table class="table table-sm">';
        data.forEach(item => {
            html += `<tr><td>${item.nama_produk}</td><td class="text-center">Stok: ${item.stok}</td><td class="text-end">Terjual: ${item.total_terjual}</td></tr>`;
        });
        html += '</table>';
        if(data.length === 0) html = '<p class="text-muted">Semua produk laku</p>';
        $('#slowMoversList').html(html);
    });
}

function loadDamagedProducts() {
    $.get('{{ route("reports.damaged-products") }}', function(data) {
        let html = '<table class="table table-sm">';
        data.forEach(item => {
            html += `<tr><td>${item.nama_produk}</td><td><span class="badge ${item.status == 'jelek' ? 'bg-warning' : 'bg-danger'}">${item.status}</span></td><td class="text-end">Stok: ${item.stok}</td></tr>`;
        });
        html += '</table>';
        if(data.length === 0) html = '<p class="text-muted">Tidak ada produk bermasalah</p>';
        $('#damagedList').html(html);
    });
}

function loadCashierSales() {
    $.get('{{ route("reports.sales-by-cashier") }}', function(data) {
        let html = '<table class="table table-sm">';
        data.forEach(item => {
            html += `<tr><td>${item.name}</td><td class="text-center">${item.jumlah_transaksi} transaksi</td><td class="text-end">${formatRupiah(item.total_omset)}</td></tr>`;
        });
        html += '</table>';
        if(data.length === 0) html = '<p class="text-muted">Belum ada transaksi</p>';
        $('#cashierSalesList').html(html);
    });
}

function loadDailySales() {
    const start = $('#startDate').val();
    const end = $('#endDate').val();
    let url = '{{ route("reports.sales-by-date") }}';
    if(start && end) {
        url += `?start_date=${start}&end_date=${end}`;
    }
    $.get(url, function(data) {
        let html = '';
        data.forEach(item => {
            html += `<tr><td>${item.tanggal}</td><td class="text-center">${item.jumlah_transaksi}</td><td class="text-end">${formatRupiah(item.total_omset)}</td></tr>`;
        });
        if(data.length === 0) html = '<tr><td colspan="3" class="text-center">Tidak ada data</td></tr>';
        $('#dailySalesBody').html(html);
    });
}

$(document).ready(function() {
    loadDashboardStats();
    loadBestSellers();
    loadSlowMovers();
    loadDamagedProducts();
    loadCashierSales();
    loadDailySales();
    
    $('#filterBtn').on('click', function() {
        loadDailySales();
    });
});
</script>
@endpush