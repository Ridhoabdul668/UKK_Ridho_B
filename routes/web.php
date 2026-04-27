<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeleteRequestController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return redirect('/pos');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ========== POS ONE PAGE ==========
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/cart-data', [PosController::class, 'getCartData'])->name('pos.cart-data');

    // ========== CART API ==========
    Route::prefix('cart')->group(function () {
        Route::post('/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/update', [CartController::class, 'update'])->name('cart.update');
        Route::post('/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/clear', [CartController::class, 'clear'])->name('cart.clear');
    });

    // ========== TRANSACTIONS ==========
    Route::post('/transaction/store', [TransactionController::class, 'store'])->name('transaction.store');
    Route::get('/transaction/receipt/{id}', [TransactionController::class, 'printReceipt'])->name('transaction.receipt');
    Route::get('/transaction/data/{id}', [TransactionController::class, 'getReceipt'])->name('transaction.data');
    Route::get('/transaction/history', [TransactionController::class, 'history'])->name('transaction.history'); // HANYA SEKALI

    // ========== PRODUCTS ==========
    // Semua user bisa lihat produk
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    // Kasir dan Admin bisa create & edit (tapi kasir tidak bisa delete langsung)
    Route::middleware('auth')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    });

    // Hanya Admin yang bisa hapus langsung
    Route::middleware('role:admin')->group(function () {
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::post('/products/{product}/mark-status', [ProductController::class, 'markStatus'])->name('products.mark-status');
    });

    // ========== CUSTOMERS (MEMBERS) ==========
    Route::middleware('role:admin')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // ========== REPORTS ==========
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/best-sellers', [ReportController::class, 'bestSellers'])->name('reports.best-sellers');
    Route::get('/reports/slow-movers', [ReportController::class, 'slowMovers'])->name('reports.slow-movers');
    Route::get('/reports/damaged-products', [ReportController::class, 'damagedProducts'])->name('reports.damaged-products');
    Route::get('/reports/products-by-status/{status}', [ReportController::class, 'productsByStatus'])->name('reports.products-by-status');
    Route::get('/reports/sales-by-cashier', [ReportController::class, 'salesByCashier'])->name('reports.sales-by-cashier');
    Route::get('/reports/sales-by-date', [ReportController::class, 'salesByDate'])->name('reports.sales-by-date');
    Route::get('/reports/dashboard-stats', [ReportController::class, 'dashboardStats'])->name('reports.dashboard-stats');

    // ========== DELETE REQUEST (HAPUS PRODUK & TRANSAKSI) ==========
    Route::post('/delete-request-product', [DeleteRequestController::class, 'storeProduct'])->name('delete-request.product.store');
    Route::post('/delete-request-transaction', [DeleteRequestController::class, 'storeTransactionRequest'])->name('delete-request.transaction.store');

    // ========== ADMIN: MANAJEMEN REQUEST ==========
    Route::middleware('role:admin')->group(function () {
        Route::get('/delete-requests', [DeleteRequestController::class, 'index'])->name('delete-requests.index');
        Route::post('/delete-requests/{id}/approve', [DeleteRequestController::class, 'approveProduct'])->name('delete-requests.approve');
        Route::post('/delete-requests/{id}/reject', [DeleteRequestController::class, 'rejectProduct'])->name('delete-requests.reject');
    });

    // ========== USER MANAGEMENT ==========
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ========== LOG ACTIVITY ==========
    Route::middleware('role:admin')->group(function () {
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/{id}', [LogController::class, 'show'])->name('logs.show');
    });
});

// ========== API for AJAX (Customer lookup) ==========
Route::get('/api/customer/{kode}', function ($kode) {
    $customer = Customer::where('kode_member', $kode)->first();
    if ($customer) {
        return response()->json([
            'id' => $customer->id,
            'nama' => $customer->nama,
            'email' => $customer->email,
            'poin' => $customer->poin,
            'jenis_diskon' => $customer->jenis_diskon,
            'nilai_diskon' => $customer->nilai_diskon,
        ]);
    }

    return response()->json(null, 404);
})->name('api.customer')->middleware('auth');

require __DIR__ . '/auth.php';
