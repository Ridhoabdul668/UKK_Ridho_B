@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="card">
    <div class="card-header bg-warning">
        <h5 class="mb-0">Edit Produk: {{ $product->nama_produk }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('products.update', $product) }}" method="POST">
            @csrf @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Barcode</label>
                    <input type="text" name="barcode" class="form-control" value="{{ $product->barcode }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="nama_produk" class="form-control" value="{{ $product->nama_produk }}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga <span class="text-danger">*</span></label>
                    <input type="number" name="harga" class="form-control" value="{{ $product->harga }}" required min="0">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stok <span class="text-danger">*</span></label>
                    <input type="number" name="stok" class="form-control" value="{{ $product->stok }}" required min="0">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Satuan</label>
                    <select name="satuan" class="form-select" required>
                        <option value="pcs" {{ $product->satuan == 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="kg" {{ $product->satuan == 'kg' ? 'selected' : '' }}>Kg</option>
                        <option value="liter" {{ $product->satuan == 'liter' ? 'selected' : '' }}>Liter</option>
                        <option value="porsi" {{ $product->satuan == 'porsi' ? 'selected' : '' }}>Porsi</option>
                        <option value="gelas" {{ $product->satuan == 'gelas' ? 'selected' : '' }}>Gelas</option>
                    </select>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ $product->deskripsi }}</textarea>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection