@extends('layouts.app')

@section('title', 'Log Activity')

@section('content')
<div class="card">
    <div class="card-header bg-black text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Log Activity Sistem</h5>
    </div>
    <div class="card-body">
        
        <!-- Filter Form -->
        <form method="GET" class="row mb-4">
            <div class="col-md-3">
                <label>User</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->role }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Aksi</label>
                <select name="aksi" class="form-select">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $aksi)
                        <option value="{{ $aksi }}" {{ request('aksi') == $aksi ? 'selected' : '' }}>
                            {{ $aksi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label>Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('logs.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
        
        <!-- Tabel Log -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Aksi</th>
                        <th>Tabel Terkait</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->user->name ?? 'Unknown' }}</td>
                        <td>
                            <span class="badge {{ $log->user->role == 'admin' ? 'bg-danger' : 'bg-info' }}">
                                {{ ucfirst($log->user->role ?? '') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $log->aksi }}</span>
                        </td>
                        <td>{{ $log->tabel_terkait ?? '-' }}</td>
                        <td>{{ Str::limit($log->keterangan, 50) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data log</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection