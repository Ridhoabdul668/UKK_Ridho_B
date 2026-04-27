@extends('layouts.app')

@section('title', 'History Transaksi')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>History Transaksi</h5>
    </div>
    <div class="card-body">
        
        <!-- Filter Tanggal -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Dari Tanggal</label>
                <input type="date" id="startDate" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Sampai Tanggal</label>
                <input type="date" id="endDate" class="form-control">
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button class="btn btn-primary w-100" id="filterBtn">Filter</button>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button class="btn btn-secondary w-100" id="resetBtn">Reset</button>
            </div>
        </div>
        
        <!-- Tabel Transaksi -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Transaksi</th>
                        <th>Total</th>
                        <th>Kasir</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="transactionBody">
                    <tr><td colspan="6" class="text-center">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Request Hapus -->
<div class="modal fade" id="requestHapusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Request Pembatalan Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRequestHapus">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="transactionId" name="transaction_id">
                    <div class="mb-3">
                        <label class="form-label">Kode Transaksi</label>
                        <input type="text" id="transactionCode" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <textarea name="alasan" id="alasan" class="form-control" rows="3" required placeholder="Contoh: Salah input, double entry, customer batal, dll"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Setelah request disetujui admin, transaksi akan dibatalkan dan stok akan dikembalikan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    loadTransactions();
    
    $('#filterBtn').on('click', function() {
        loadTransactions();
    });
    
    $('#resetBtn').on('click', function() {
        $('#startDate').val('');
        $('#endDate').val('');
        loadTransactions();
    });
    
    function loadTransactions() {
        let url = '/transaction/history?';
        if ($('#startDate').val()) url += `start_date=${$('#startDate').val()}&`;
        if ($('#endDate').val()) url += `end_date=${$('#endDate').val()}&`;
        
        $.get(url, function(response) {
            let html = '';
            if (response.data && response.data.length > 0) {
                response.data.forEach(transaction => {
                    html += `
                        <tr>
                            <td>${transaction.tanggal}</td>
                            <td>${transaction.kode_transaksi}</td>
                            <td>Rp ${formatNumber(transaction.total)}</td>
                            <td>${transaction.user ? transaction.user.name : '-'}</td>
                            <td><span class="badge bg-success">Selesai</span></td>
                            <td>
                                <button class="btn btn-sm btn-warning request-delete" 
                                    data-id="${transaction.id}" 
                                    data-kode="${transaction.kode_transaksi}">
                                    <i class="fas fa-ticket-alt me-1"></i> Request Hapus
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center">Tidak ada data transaksi</td></tr>';
            }
            $('#transactionBody').html(html);
        }).fail(function() {
            $('#transactionBody').html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>');
        });
    }
    
    // Request hapus transaksi
    $(document).on('click', '.request-delete', function() {
        const id = $(this).data('id');
        const kode = $(this).data('kode');
        $('#transactionId').val(id);
        $('#transactionCode').val(kode);
        $('#alasan').val('');
        $('#requestHapusModal').modal('show');
    });
    
    // Submit request hapus
    $('#formRequestHapus').on('submit', function(e) {
        e.preventDefault();
        
        const transactionId = $('#transactionId').val();
        const alasan = $('#alasan').val();
        
        if (!alasan) {
            alert('Alasan harus diisi!');
            return;
        }
        
        $.ajax({
            url: '/delete-request-transaction',
            method: 'POST',
            data: {
                id_transaksi: transactionId,
                alasan_hapus: alasan,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('Request pembatalan sudah dikirim ke admin');
                    $('#requestHapusModal').modal('hide');
                    loadTransactions();
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal mengirim request');
            }
        });
    });
    
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
});
</script>
@endpush