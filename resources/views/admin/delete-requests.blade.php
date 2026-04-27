@extends('layouts.app')

@section('title', 'Request Hapus Produk')

@section('content')
<div class="card">
    <div class="card-header bg-warning">
        <h5 class="mb-0">Request Penghapusan/Edit Produk dari Kasir</h5>
    </div>
    <div class="card-body">
        @if($requests->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Diminta Oleh</th>
                        <th>Tabel Target</th>
                        <th>ID Target</th>
                        <th>Alasan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $req->requester->name }}</td>
                        <td>{{ $req->tabel_target }}</td>
                        <td>{{ $req->target_id }}</td>
                        <td>{{ $req->alasan }}</td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('delete-requests.approve', $req->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui penghapusan?')">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>
                                <form action="{{ route('delete-requests.reject', $req->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tolak request?')">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted text-center">Tidak ada request pending</p>
        @endif
    </div>
</div>

<!-- Script untuk kasir buat request (dari halaman produk) -->
@if(!auth()->user()->isAdmin())
@push('scripts')
<script>
// Ini akan muncul di halaman produk untuk kasir
$(document).ready(function() {
    // Tambahkan tombol "Request Hapus" di setiap baris produk (jika kasir login)
    $('table tbody tr').each(function() {
        const productId = $(this).find('.btn-danger').closest('form').attr('action')?.split('/').pop();
        if(productId && !$(this).find('.btn-request').length) {
            $(this).find('.btn-group').append(`
                <button type="button" class="btn btn-sm btn-info btn-request" data-id="${productId}">
                    <i class="fas fa-ticket-alt"></i> Request
                </button>
            `);
        }
    });
    
    $(document).on('click', '.btn-request', function() {
        const id = $(this).data('id');
        const alasan = prompt('Alasan ingin menghapus/mengedit produk ini:');
        if(alasan) {
            $.ajax({
                url: '{{ route("delete-request.store") }}',
                method: 'POST',
                data: {
                    tabel_target: 'products',
                    target_id: id,
                    alasan: alasan,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert('Request sudah dikirim ke admin');
                },
                error: function() {
                    alert('Gagal mengirim request');
                }
            });
        }
    });
});
</script>
@endpush
@endif
@endsection