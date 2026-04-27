// ========== POS SYSTEM - MAIN SCRIPT ==========
// File: resources/js/pos.js
// Untuk menggunakan Vite: npm install && npm run dev

$(document).ready(function() {
    // ========== GLOBAL VARIABLES ==========
    let cart = [];
    let currentCustomer = null;
    let diskonPersen = 0;
    let diskonNominal = 0;

    // ========== FORMAT NUMBER ==========
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // ========== TOGGLE DISKON UI ==========
    function toggleDiskonUI() {
        const jenis = $('#jenisDiskon').val();
        if (jenis === 'persen') {
            $('#diskonLabel').text('Nilai Diskon (%)');
            $('#diskonNilai').attr({ min: 0, max: 100, step: 1 });
        } else {
            $('#diskonLabel').text('Nilai Diskon (Rp)');
            $('#diskonNilai').attr({ min: 0, max: null, step: 1000 });
        }
    }

    // ========== TOAST NOTIFICATION ==========
    function showToast(icon, message, duration = 2000) {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            Toast.fire({ icon: icon, title: message });
        } else {
            alert(message);
        }
    }

    // ========== LOAD CART FROM SERVER ==========
    function loadCart() {
        $.ajax({
            url: '/pos/cart-data',
            method: 'GET',
            success: function(response) {
                cart = response.cart;
                diskonPersen = 0;
                diskonNominal = 0;
                $('#diskonNilai').val(0);
                renderCart();
            },
            error: function() {
                console.log('Gagal memuat keranjang');
            }
        });
    }

    // ========== ADD TO CART ==========
    function addToCart(productId, qty = 1) {
        $.ajax({
            url: '/cart/add',
            method: 'POST',
            data: {
                product_id: productId,
                qty: qty,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    cart = response.cart;
                    diskonPersen = 0;
                    diskonNominal = 0;
                    $('#diskonNilai').val(0);
                    renderCart();
                    showToast('success', 'Produk ditambahkan ke keranjang');
                } else {
                    showToast('error', response.message);
                }
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Gagal menambah produk');
            }
        });
    }

    // ========== UPDATE CART QUANTITY ==========
    function updateCartQty(productId, qty) {
        if (qty < 0) return;
        
        $.ajax({
            url: '/cart/update',
            method: 'POST',
            data: {
                product_id: productId,
                qty: qty,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    cart = response.cart;
                    renderCart();
                } else {
                    showToast('error', response.message);
                }
            }
        });
    }

    // ========== REMOVE FROM CART ==========
    function removeCartItem(productId) {
        $.ajax({
            url: '/cart/remove',
            method: 'POST',
            data: {
                product_id: productId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                cart = response.cart;
                renderCart();
                showToast('success', 'Item dihapus dari keranjang');
            }
        });
    }

    // ========== CLEAR CART ==========
    function clearCart() {
        if (confirm('Yakin ingin mengosongkan keranjang?')) {
            $.ajax({
                url: '/cart/clear',
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    cart = [];
                    renderCart();
                    showToast('success', 'Keranjang berhasil dikosongkan');
                }
            });
        }
    }

    // ========== RENDER CART TABLE ==========
    function renderCart() {
        const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
        const totalDiskon = (subtotal * diskonPersen / 100) + diskonNominal;
        const total = subtotal - totalDiskon;
        
        let html = '';
        if (cart.length === 0) {
            html = '<tr><td colspan="5" class="text-center">Keranjang kosong</td></tr>';
        } else {
            cart.forEach(item => {
                html += `
                    <tr>
                        <td>${item.nama_produk}</td>
                        <td>Rp ${formatNumber(item.harga)}</td>
                        <td>
                            <input type="number" class="form-control cart-item-qty" 
                                   value="${item.qty}" min="1" max="${item.stok_max}"
                                   data-id="${item.id}" style="width:70px">
                        </td>
                        <td>Rp ${formatNumber(item.subtotal)}</td>
                        <td>
                            <button class="btn btn-sm btn-danger remove-item" data-id="${item.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#cartBody').html(html);
        
        $('#subtotalDisplay').text('Rp ' + formatNumber(subtotal));
        $('#diskonDisplay').text('Rp ' + formatNumber(totalDiskon));
        $('#totalDisplay').text('Rp ' + formatNumber(total));
        
        const bayar = parseInt($('#bayarInput').val()) || 0;
        const kembalian = bayar - total;
        $('#kembalianDisplay').text('Rp ' + formatNumber(kembalian > 0 ? kembalian : 0));
        
        // Attach events
        $('.cart-item-qty').off('change').on('change', function() {
            const productId = $(this).data('id');
            const newQty = parseInt($(this).val());
            updateCartQty(productId, newQty);
        });
        
        $('.remove-item').off('click').on('click', function() {
            const productId = $(this).data('id');
            removeCartItem(productId);
        });
    }

    // ========== CHECK MEMBER ==========
    function checkMember() {
        const kode = $('#memberCode').val();
        if (!kode) {
            currentCustomer = null;
            $('#memberInfo').html('');
            return;
        }
        
        $.ajax({
            url: `/api/customer/${kode}`,
            method: 'GET',
            success: function(data) {
                if (data && data.id) {
                    currentCustomer = data;
                    $('#memberInfo').html(`<span class="text-success">✓ Member: ${data.nama}</span>`);
                    
                    if (data.jenis_diskon === 'persen') {
                        diskonPersen = data.nilai_diskon;
                        $('#diskonNilai').val(diskonPersen);
                        $('#jenisDiskon').val('persen');
                    } else {
                        diskonNominal = data.nilai_diskon;
                        $('#diskonNilai').val(diskonNominal);
                        $('#jenisDiskon').val('nominal');
                    }
                    toggleDiskonUI();
                    renderCart();
                    showToast('success', `Selamat datang, ${data.nama}!`);
                } else {
                    currentCustomer = null;
                    $('#memberInfo').html('<span class="text-danger">✗ Kode member tidak ditemukan</span>');
                    showToast('error', 'Kode member tidak ditemukan');
                }
            },
            error: function() {
                currentCustomer = null;
                $('#memberInfo').html('<span class="text-danger">✗ Kode member tidak ditemukan</span>');
                showToast('error', 'Kode member tidak ditemukan');
            }
        });
    }

    // ========== APPLY DISCOUNT ==========
    function applyDiscount() {
        const jenis = $('#jenisDiskon').val();
        const nilai = parseInt($('#diskonNilai').val()) || 0;
        
        if (jenis === 'persen') {
            diskonPersen = Math.min(nilai, 100);
            diskonNominal = 0;
            showToast('info', `Diskon ${diskonPersen}% diterapkan`);
        } else {
            diskonNominal = nilai;
            diskonPersen = 0;
            showToast('info', `Diskon Rp ${formatNumber(diskonNominal)} diterapkan`);
        }
        renderCart();
    }

    // ========== CHECKOUT ==========
    function checkout() {
        const total = parseInt($('#totalDisplay').text().replace(/[^0-9]/g, '')) || 0;
        const bayar = parseInt($('#bayarInput').val()) || 0;
        
        if (cart.length === 0) {
            showToast('warning', 'Keranjang belanja masih kosong!');
            return;
        }
        
        if (bayar < total) {
            showToast('error', `Uang bayar kurang! Minimal Rp ${formatNumber(total)}`);
            return;
        }
        
        if (!confirm(`Total: Rp ${formatNumber(total)}\nBayar: Rp ${formatNumber(bayar)}\nKembalian: Rp ${formatNumber(bayar - total)}\n\nLanjutkan transaksi?`)) {
            return;
        }
        
        $.ajax({
            url: '/transaction/store',
            method: 'POST',
            data: {
                bayar: bayar,
                customer_id: currentCustomer?.id,
                diskon_persen: diskonPersen,
                diskon_nominal: diskonNominal,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', 'Transaksi berhasil!');
                    loadReceipt(response.transaction_id);
                    resetAfterCheckout();
                } else {
                    showToast('error', response.message);
                }
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Transaksi gagal');
            }
        });
    }

    // ========== RESET AFTER CHECKOUT ==========
    function resetAfterCheckout() {
        cart = [];
        currentCustomer = null;
        diskonPersen = 0;
        diskonNominal = 0;
        $('#memberCode').val('');
        $('#memberInfo').html('');
        $('#jenisDiskon').val('persen');
        $('#diskonNilai').val(0);
        $('#bayarInput').val('');
        toggleDiskonUI();
        renderCart();
    }

    // ========== LOAD RECEIPT MODAL ==========
    function loadReceipt(transactionId) {
        $('#receiptContent').html('Loading...');
        $('#receiptModal').modal('show');
        
        $.ajax({
            url: `/transaction/data/${transactionId}`,
            method: 'GET',
            success: function(data) {
                let html = `
                    <div style="font-family: monospace; font-size: 12px;">
                        <div class="text-center">
                            <strong>TOKO POS SYSTEM</strong><br>
                            ${new Date().toLocaleString()}<br>
                            Kasir: ${data.user.name}<br>
                            Transaksi: ${data.kode_transaksi}<br>
                            ${data.customer ? `Member: ${data.customer.nama}<br>` : ''}
                            <hr>
                        </div>
                        <table style="width: 100%;">
                            <thead>
                                <tr><th>Item</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
                            </thead>
                            <tbody>
                `;
                data.items.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.product.nama_produk}</td>
                            <td>${item.qty}</td>
                            <td>${formatNumber(item.harga_saat_transaksi)}</td>
                            <td>${formatNumber(item.subtotal)}</td>
                        </tr>
                    `;
                });
                html += `
                            </tbody>
                        </table>
                        <hr>
                        <table style="width: 100%;">
                            <tr><td>Subtotal</td><td align="right">${formatNumber(data.subtotal)}</td></tr>
                            <tr><td>Diskon</td><td align="right">${formatNumber((data.subtotal * data.diskon_persen / 100) + data.diskon_nominal)}</td></tr>
                            <tr><td><strong>TOTAL</strong></td><td align="right"><strong>${formatNumber(data.total)}</strong></td></tr>
                            <tr><td>Bayar</td><td align="right">${formatNumber(data.bayar)}</td></tr>
                            <tr><td>Kembalian</td><td align="right">${formatNumber(data.kembalian)}</td></tr>
                        </table>
                        <hr>
                        <div class="text-center">
                            Terima kasih telah berbelanja!<br>
                            ${data.customer && data.customer.email ? `Struk dikirim ke ${data.customer.email}` : ''}
                        </div>
                    </div>
                `;
                $('#receiptContent').html(html);
                window.currentReceiptId = transactionId;
            }
        });
    }

    // ========== PRINT RECEIPT ==========
    function printReceipt() {
        if (window.currentReceiptId) {
            window.open(`/transaction/receipt/${window.currentReceiptId}`, '_blank');
        }
    }

    // ========== SEARCH PRODUCTS ==========
    function searchProducts() {
        const keyword = $('#searchProduct').val().toLowerCase();
        $('.product-item').each(function() {
            const nama = $(this).data('nama').toLowerCase();
            const barcode = ($(this).data('barcode') || '').toLowerCase();
            if (nama.includes(keyword) || barcode.includes(keyword)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // ========== EVENT LISTENERS ==========
    // Product click
    $(document).on('click', '.product-card', function() {
        const productId = $(this).closest('.product-item').data('id');
        addToCart(productId, 1);
    });
    
    // Member check
    $('#checkMemberBtn').on('click', checkMember);
    $('#memberCode').on('keypress', function(e) {
        if (e.which === 13) checkMember();
    });
    
    // Diskon
    $('#jenisDiskon').on('change', function() {
        toggleDiskonUI();
        $('#diskonNilai').val(0);
        diskonPersen = 0;
        diskonNominal = 0;
        renderCart();
    });
    
    $('#applyDiscountBtn').on('click', applyDiscount);
    
    // Cart actions
    $('#clearCartBtn').on('click', clearCart);
    $('#checkoutBtn').on('click', checkout);
    
    // Receipt
    $('#printReceiptBtn').on('click', printReceipt);
    
    // Search
    $('#searchProduct').on('keyup', searchProducts);
    
    // Bayar input
    $('#bayarInput').on('input', function() {
        renderCart();
    });
    
    // Initialize
    loadCart();
    toggleDiskonUI();
});