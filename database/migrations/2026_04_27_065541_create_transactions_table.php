<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 20)->unique();
            $table->date('tanggal');
            $table->bigInteger('subtotal');
            $table->integer('diskon_persen')->default(0);
            $table->integer('diskon_nominal')->default(0);
            $table->bigInteger('total');
            $table->bigInteger('bayar');
            $table->bigInteger('kembalian');
            $table->enum('status', ['selesai', 'batal'])->default('selesai');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
