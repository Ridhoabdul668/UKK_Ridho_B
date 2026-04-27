@extends('layouts.app')

@section('title', 'Detail Log')

@section('content')
<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detail Log Activity</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th width="200">Waktu</th>
                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <th>User</th>
                <td>{{ $log->user->name ?? 'Unknown' }} ({{ ucfirst($log->user->role ?? '') }})</td>
            </tr>
            <tr>
                <th>Aksi</th>
                <td><span class="badge bg-secondary">{{ $log->aksi }}</span></td>
            </tr>
            <tr>
                <th>Tabel Terkait</th>
                <td>{{ $log->tabel_terkait ?? '-' }}</td>
            </tr>
            <tr>
                <th>ID Record</th>
                <td>{{ $log->record_id ?? '-' }}</td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td>{{ $log->keterangan ?? '-' }}</td>
            </tr>
            <tr>
                <th>Data Lama</th>
                <td><pre>{{ json_encode(json_decode($log->data_lama), JSON_PRETTY_PRINT) ?? '-' }}</pre></td>
            </tr>
            <tr>
                <th>Data Baru</th>
                <td><pre>{{ json_encode(json_decode($log->data_baru), JSON_PRETTY_PRINT) ?? '-' }}</pre></td>
            </tr>
            <tr>
                <th>IP Address</th>
                <td>{{ $log->ip_address ?? '-' }}</td>
            </tr>
        </table>
        
        <a href="{{ route('logs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>
@endsection