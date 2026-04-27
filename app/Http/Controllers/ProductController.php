<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(20);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'nullable|unique:products',
            'nama_produk' => 'required',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required',
        ]);

        $product = Product::create($request->all());

        Log::catat(Auth::id(), 'create', 'products', $product->id, 'Menambah produk baru: '.$product->nama_produk);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'barcode' => 'nullable|unique:products,barcode,'.$product->id,
            'nama_produk' => 'required',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required',
        ]);

        $oldData = $product->toArray();
        $product->update($request->all());

        Log::catat(Auth::id(), 'update', 'products', $product->id, 'Mengupdate produk', $oldData, $product->toArray());

        return redirect()->route('products.index')->with('success', 'Produk berhasil diupdate');
    }

    public function destroy(Product $product)
    {
        if ($product->transactionItems()->exists()) {
            return back()->with('error', 'Produk sudah pernah bertransaksi, tidak bisa dihapus. Tandai tidak aktif saja.');
        }

        $namaProduk = $product->nama_produk;
        $product->delete();

        Log::catat(Auth::id(), 'delete', 'products', $product->id, 'Menghapus produk: '.$namaProduk);

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }

    public function toggleStatus(Product $product)
    {
        $product->is_active = ! $product->is_active;
        $product->save();

        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        Log::catat(Auth::id(), 'toggle_status', 'products', $product->id, "Produk {$status}");

        return back()->with('success', "Produk berhasil {$status}");
    }

    public function markStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|in:baik,jelek,kadaluarsa',
        ]);

        $oldStatus = $product->status;
        $product->status = $request->status;
        $product->save();

        Log::catat(Auth::id(), 'mark_status', 'products', $product->id, "Ubah status dari {$oldStatus} ke {$request->status}");

        return back()->with('success', 'Status produk berhasil diubah');
    }
}
