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
        Schema::create('pemakaian_bahan', function (Blueprint $table) {
            $table->id();
            // Sesuaikan foreign key dengan tipe id tabel bahan_baku & job_order (biasanya bigInteger bawaan laravel)
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->onDelete('cascade');
            $table->foreignId('job_order_id')->constrained('job_order')->onDelete('cascade');
            $table->dateTime('tgl_keluar');
            $table->decimal('jumlah', 15, 4);
            $table->decimal('harga_rata_saat_ini', 15, 4);
            $table->timestamps(); // otomatis membuat created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemakaian_bahan');
    }
};
