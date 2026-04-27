@extends('layouts.app')

@section('title', 'Manajemen Request Hapus Transaksi')

@section('content')
<div class="card">
    <div class="card-header bg-black text-white">
        <h5><i class="fas fa-ticket-alt me-2"></i>Request Hapus Transaksi</h5>
    </div>
    <div class="card-body">
        @if(count($transactionRequests) == 0)
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Tidak ada request pending untuk transaksi.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="150">Tanggal Request</th>
                            <th width="150">Kode Transaksi</th>
                            <th>Petugas</th>
                            <th>Total Transaksi</th>
                            <th>Alasan Hapus</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactionRequests as $index => $t)
                        <tr class="{{ $t->status == 'pending' ? 'table-warning' : '' }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $t->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <strong>{{ $t->transaksi->kode_transaksi ?? '-' }}</strong>
                            </td>
                            <td>{{ $t->petugas->name ?? '-' }}</td>
                            <td>
                                Rp {{ number_format($t->transaksi->total ?? 0, 0, ',', '.') }}
                            </td>
                            <td>{{ $t->alasan_hapus }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('delete-requests.transaction.approve', $t->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Setujui request hapus ini? Stok produk akan dikembalikan.')">
                                            <i class="fas fa-check me-1"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('delete-requests.transaction.reject', $t->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Tolak request hapus ini?')">
                                            <i class="fas fa-times me-1"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection