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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_member', 50)->unique();
            $table->string('nama');
            $table->string('email');
            $table->string('no_hp', 15);
            $table->text('alamat')->nullable();
            $table->integer('poin')->default(0);
            $table->enum('jenis_diskon', ['persen', 'nominal'])->default('persen');
            $table->integer('nilai_diskon')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
