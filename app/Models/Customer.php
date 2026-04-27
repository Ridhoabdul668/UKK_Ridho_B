<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'kode_member', 'nama', 'email', 'no_hp', 'alamat',
        'poin', 'jenis_diskon', 'nilai_diskon',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Hitung diskon berdasarkan setting member
    public function hitungDiskon(int $subtotal): int
    {
        if ($this->jenis_diskon === 'persen') {
            return (int) ($subtotal * $this->nilai_diskon / 100);
        } else {
            return min($this->nilai_diskon, $subtotal);
        }
    }
}
