<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'barcode', 'nama_produk', 'deskripsi', 'harga',
        'stok', 'satuan', 'status', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // Kurangi stok
    public function kurangiStok(int $qty): void
    {
        $this->stok -= $qty;
        $this->save();
    }

    // Tambah stok
    public function tambahStok(int $qty): void
    {
        $this->stok += $qty;
        $this->save();
    }
}
