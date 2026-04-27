<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

class PosController extends Controller
{
    public function index()
    {
        // HANYA TAMPILKAN PRODUK DENGAN STATUS "baik" DAN AKTIF SERTA STOK > 0
        $products = Product::where('is_active', true)
            ->where('status', 'baik')
            ->where('stok', '>', 0)
            ->orderBy('nama_produk', 'asc')
            ->get();

        $customers = Customer::all();
        $cart = Session::get('cart', []);

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['subtotal'];
        }

        return view('pos.index', compact('products', 'customers', 'cart', 'total'));
    }

    public function getCartData()
    {
        $cart = Session::get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['subtotal'];
        }

        return response()->json([
            'cart' => array_values($cart),
            'total' => $total,
            'count' => count($cart),
        ]);
    }
}
