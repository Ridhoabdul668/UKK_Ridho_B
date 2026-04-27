<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'kode_member' => 'M001',
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_hp' => '08123456789',
            'alamat' => 'Jl. Merdeka No 1',
            'poin' => 100,
            'jenis_diskon' => 'persen',
            'nilai_diskon' => 10,
        ]);

        Customer::create([
            'kode_member' => 'M002',
            'nama' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'no_hp' => '08198765432',
            'alamat' => 'Jl. Sudirman No 5',
            'poin' => 250,
            'jenis_diskon' => 'nominal',
            'nilai_diskon' => 5000,
        ]);
    }
}
