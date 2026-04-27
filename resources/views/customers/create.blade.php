@extends('layouts.app')

@section('title', 'Tambah Member Baru')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Tambah Member Baru</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Member <span class="text-danger">*</span></label>
                    <input type="text" name="kode_member" class="form-control @error('kode_member') is-invalid @enderror" 
                           value="{{ old('kode_member', $kodeMember) }}" readonly style="background-color: #e9ecef">
                    @error('kode_member') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Kode member otomatis, tidak bisa diubah</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                           value="{{ old('nama') }}" required>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Email akan dikirimi struk pembelian</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor HP <span class="text-danger">*</span></label>
                    <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" 
                           value="{{ old('no_hp') }}" required>
                    @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                              rows="2">{{ old('alamat') }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Diskon <span class="text-danger">*</span></label>
                    <select name="jenis_diskon" class="form-select @error('jenis_diskon') is-invalid @enderror" required>
                        <option value="persen" {{ old('jenis_diskon') == 'persen' ? 'selected' : '' }}>Persen (%)</option>
                        <option value="nominal" {{ old('jenis_diskon') == 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                    </select>
                    @error('jenis_diskon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai Diskon <span class="text-danger">*</span></label>
                    <input type="number" name="nilai_diskon" class="form-control @error('nilai_diskon') is-invalid @enderror" 
                           value="{{ old('nilai_diskon', 0) }}" required min="0">
                    @error('nilai_diskon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">
                        Jika jenis diskon "Persen", isi dengan angka (contoh: 10 untuk 10%). 
                        Jika "Nominal", isi dengan angka rupiah (contoh: 5000 untuk Rp5.000)
                    </small>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Member
                </button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection