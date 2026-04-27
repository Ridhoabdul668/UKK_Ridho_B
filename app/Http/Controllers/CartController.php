<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->status !== 'baik') {
            return response()->json([
                'success' => false,
                'message' => "Produk {$product->nama_produk} sedang {$product->status} dan tidak bisa dijual!",
            ], 400);
        }

        if ($product->stok < $request->qty) {
            return response()->json([
                'success' => false,
                'message' => "Stok {$product->nama_produk} tersisa {$product->stok}",
            ], 400);
        }

        if (isset($cart[$product->id])) {
            // Update qty jika sudah ada
            $newQty = $cart[$product->id]['qty'] + $request->qty;
            if ($product->stok < $newQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak cukup. Maksimal {$product->stok}",
                ], 400);
            }
            $cart[$product->id]['qty'] = $newQty;
            $cart[$product->id]['subtotal'] = $cart[$product->id]['harga'] * $newQty;
        } else {
            // Tambah baru
            $cart[$product->id] = [
                'id' => $product->id,
                'nama_produk' => $product->nama_produk,
                'harga' => $product->harga,
                'qty' => $request->qty,
                'subtotal' => $product->harga * $request->qty,
                'stok_max' => $product->stok,
            ];
        }

        Session::put('cart', $cart);

        // Hitung total baru
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['subtotal'];
        }

        return response()->json([
            'success' => true,
            'cart' => array_values($cart),
            'total' => $total,
            'count' => count($cart),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'qty' => 'required|integer|min:0',
        ]);

        $cart = Session::get('cart', []);

        if (! isset($cart[$request->product_id])) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ada di keranjang'], 404);
        }

        $product = Product::find($request->product_id);

        if ($request->qty <= 0) {
            // Hapus item
            unset($cart[$request->product_id]);
        } else {
            // Cek stok
            if ($product && $product->stok < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak cukup. Maksimal {$product->stok}",
                ], 400);
            }
            $cart[$request->product_id]['qty'] = $request->qty;
            $cart[$request->product_id]['subtotal'] = $cart[$request->product_id]['harga'] * $request->qty;
        }

        Session::put('cart', $cart);

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['subtotal'];
        }

        return response()->json([
            'success' => true,
            'cart' => array_values($cart),
            'total' => $total,
            'count' => count($cart),
        ]);
    }

    public function remove(Request $request)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$request->product_id])) {
            unset($cart[$request->product_id]);
            Session::put('cart', $cart);
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['subtotal'];
        }

        return response()->json([
            'success' => true,
            'cart' => array_values($cart),
            'total' => $total,
            'count' => count($cart),
        ]);
    }

    public function clear()
    {
        Session::forget('cart');

        return response()->json([
            'success' => true,
            'cart' => [],
            'total' => 0,
            'count' => 0,
        ]);
    }
}
