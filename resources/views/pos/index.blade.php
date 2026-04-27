@extends('layouts.app')

@section('title', 'POS - Kasir')

@push('styles')
    <style>
        .product-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 10px;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .cart-table th,
        .cart-table td {
            vertical-align: middle;
        }

        .cart-item-qty {
            width: 70px;
            text-align: center;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .total-display {
            font-size: 24px;
            font-weight: bold;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .member-box {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 8px;
        }

        .stok-badge {
            font-size: 11px;
        }

        /* Responsive untuk iPad */
        @media (max-width: 768px) {
            .product-card h6 {
                font-size: 12px;
            }
            .cart-table th,
            .cart-table td {
                font-size: 12px;
                padding: 8px 4px;
            }
            .total-display h3 {
                font-size: 20px;
            }
            .btn {
                font-size: 12px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="mb-0"><i class=" me-2"></i>Daftar Produk</h5>
                    </div>
                    <div class="card-body">
                        <div class="search-box">
                            <input type="text" id="searchProduct" class="form-control" placeholder="Cari produk...">
                        </div>
                        <div class="row" id="productList">
                            @foreach($products as $product)
                                <div class="col-md-4 mb-3 product-item" 
                                     data-id="{{ $product->id }}"
                                     data-nama="{{ $product->nama_produk }}" 
                                     data-harga="{{ $product->harga }}"
                                     data-stok="{{ $product->stok }}" 
                                     data-barcode="{{ $product->barcode }}">
                                    <div class="card product-card">
                                        <div class="card-body p-2 text-center">
                                            <h6 class="mt-1">{{ Str::limit($product->nama_produk, 20) }}</h6>
                                            <h6 class="text-primary">Rp {{ number_format($product->harga, 0, ',', '.') }}</h6>
                                            @if($product->status != 'baik')
                                                <span class="badge bg-warning stok-badge">{{ ucfirst($product->status) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Keranjang -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="mb-0"><i class="me-2"></i>Keranjang Belanja</h5>
                    </div>
                    <div class="card-body">
                        <!-- Member Area -->
                        <div class="member-box mb-3">
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="form-label">Kode Member</label>
                                    <div class="input-group">
                                        <input type="text" id="memberCode" class="form-control"
                                            placeholder="Masukkan kode member">
                                        <button class="btn btn-outline-primary" id="checkMemberBtn" type="button">
                                            <i class="fas fa-check"></i> Cek
                                        </button>
                                    </div>
                                    <div id="memberInfo" class="mt-2 small"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Diskon Area -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Jenis Diskon</label>
                                <select id="jenisDiskon" class="form-select">
                                    <option value="persen">Persen (%)</option>
                                    <option value="nominal">Nominal (Rp)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" id="diskonLabel">Nilai Diskon (%)</label>
                                <input type="number" id="diskonNilai" class="form-control" value="0" min="0" step="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-secondary w-100" id="applyDiscountBtn">
                                    <i class=" me-1"></i> Apply
                                </button>
                            </div>
                        </div>

                        <!-- Tabel Keranjang -->
                        <div class="table-responsive">
                            <table class="table table-bordered cart-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th width="100">Harga</th>
                                        <th width="100">Qty</th>
                                        <th width="120">Subtotal</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody">
                                    <tr>
                                        <td colspan="5" class="text-center">Keranjang kosong</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Total -->
                        <div class="total-display mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Subtotal:</strong>
                                    <span id="subtotalDisplay">Rp 0</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Total Diskon:</strong>
                                    <span id="diskonDisplay">Rp 0</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <h4><strong>TOTAL:</strong></h4>
                                    <h3 class="text-success" id="totalDisplay">Rp 0</h3>
                                </div>
                                <div class="col-md-6">
                                    <label class="mb-2">Uang Bayar:</label>
                                    <input type="number" id="bayarInput" class="form-control"
                                        placeholder="Masukkan jumlah bayar">
                                    <label class="mt-2">Kembalian:</label>
                                    <h5 id="kembalianDisplay">Rp 0</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-danger flex-grow-1" id="clearCartBtn">
                                <i class="fas fa-trash me-1"></i> Kosongkan
                            </button>
                            <button class="btn btn-success flex-grow-1" id="checkoutBtn">
                                <i class="fas fa-credit-card me-1"></i> Bayar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Struk -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🧾 Struk Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="receiptContent">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="printReceiptBtn">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- jQuery (pastikan sudah ada di layout) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Vite: Load pos.js yang sudah di-compile -->
    @vite(['resources/js/pos.js'])
@endpush