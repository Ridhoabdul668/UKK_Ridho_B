<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $produk = [
            ['barcode' => 'PRD001', 'nama_produk' => 'Kopi Hitam', 'harga' => 15000, 'stok' => 50, 'satuan' => 'pcs'],
            ['barcode' => 'PRD002', 'nama_produk' => 'Teh Manis', 'harga' => 10000, 'stok' => 40, 'satuan' => 'pcs'],
            ['barcode' => 'PRD003', 'nama_produk' => 'Roti Tawar', 'harga' => 12000, 'stok' => 30, 'satuan' => 'pcs'],
            ['barcode' => 'PRD004', 'nama_produk' => 'Nasi Goreng', 'harga' => 25000, 'stok' => 25, 'satuan' => 'porsi'],
            ['barcode' => 'PRD005', 'nama_produk' => 'Mie Ayam', 'harga' => 18000, 'stok' => 20, 'satuan' => 'porsi'],
            ['barcode' => 'PRD006', 'nama_produk' => 'Es Jeruk', 'harga' => 8000, 'stok' => 60, 'satuan' => 'gelas'],
        ];

        foreach ($produk as $p) {
            Product::create($p);
        }
    }
}
