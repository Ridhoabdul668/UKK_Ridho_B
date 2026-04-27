@extends('layouts.app')

@section('title', 'Manajemen Member')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-users me-2"></i>Manajemen Member</h2>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Member
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Member</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Jenis Diskon</th>
                            <th>Nilai Diskon</th>
                            <th>Poin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td><span class="badge bg-primary">{{ $customer->kode_member }}</span></td>
                                <td>{{ $customer->nama }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->no_hp }}</td>
                                <td>
                                    @if($customer->jenis_diskon == 'persen')
                                        <span class="badge bg-info">Persen (%)</span>
                                    @else
                                        <span class="badge bg-warning">Nominal (Rp)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->jenis_diskon == 'persen')
                                        {{ $customer->nilai_diskon }}%
                                    @else
                                        Rp {{ number_format($customer->nilai_diskon, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td>{{ $customer->poin }} poin</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin hapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data member</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
@endsection