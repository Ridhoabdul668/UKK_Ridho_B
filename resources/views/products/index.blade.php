@extends('layouts.app')

@section('title', 'Manajemen Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manajemen Produk</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Produk
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Barcode</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                            <th>Status</th>
                            <th>Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->barcode ?? '-' }}</td>
                                <td>{{ $product->nama_produk }}</td>
                                <td>Rp {{ number_format($product->harga, 0, ',', '.') }}</td>
                                <td class="{{ $product->stok <= 5 ? 'text-danger fw-bold' : '' }}">
                                    {{ $product->stok }}
                                </td>
                                <td>{{ $product->satuan }}</td>
                                <td>
                                    <span class="badge 
                                                        @if($product->status == 'baik') bg-success
                                                        @elseif($product->status == 'jelek') bg-warning
                                                        @else bg-danger
                                                        @endif">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <!-- Edit: semua user bisa -->
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <!-- Hapus: hanya admin yang bisa langsung, kasir pake request -->
                                        @if(auth()->user()->isAdmin())
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin hapus?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-info btn-sm request-delete-product" data-id="{{ $product->id }}"
                                                data-nama="{{ $product->nama_produk }}">
                                                <i class="fas fa-ticket-alt"></i> Request Hapus
                                            </button>
                                        @endif
                                    </div>
                                </td>

                                <!-- Modal Ubah Status Produk -->
                                <div class="modal fade" id="statusModal{{ $product->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <form action="{{ route('products.mark-status', $product) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Ubah Status: {{ $product->nama_produk }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <select name="status" class="form-select" required>
                                                        <option value="baik" {{ $product->status == 'baik' ? 'selected' : '' }}>
                                                            Baik</option>
                                                        <option value="jelek" {{ $product->status == 'jelek' ? 'selected' : '' }}>
                                                            Jelek/Rusak</option>
                                                        <option value="kadaluarsa" {{ $product->status == 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                                                    </select>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Batal</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data produk</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $products->links() }}
        </div>
    </div>
@endsection
<script>
    $(document).ready(function () {
        // Request hapus produk untuk kasir
        $(document).on('click', '.request-delete-product', function () {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const alasan = prompt(`Alasan ingin menghapus produk "${nama}":`);

            if (alasan) {
                $.ajax({
                    url: '{{ route("delete-request.product") }}',
                    method: 'POST',
                    data: {
                        tabel_target: 'products',
                        target_id: id,
                        alasan: alasan,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Request hapus produk sudah dikirim ke admin');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert('Gagal mengirim request');
                    }
                });
            }
        });
    });
</script>