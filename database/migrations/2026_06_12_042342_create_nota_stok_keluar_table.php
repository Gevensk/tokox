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
        Schema::create('nota_stok_keluar', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel customer (Wajib ada)
            $table->foreignId('customer_id')->constrained('customer')->onDelete('cascade');
            
            // Relasi ke tabel nota_order (NULLABLE: Kosong jika pelanggan beli langsung produk ready)
            $table->foreignId('nota_order_id')->nullable()->constrained('nota_order')->onDelete('set null');
            
            // Menggunakan ENUM sesuai dengan kamus mutasi barang yang kita sepakati
            $table->enum('tipe_keluar', ['penjualan_order', 'penjualan_langsung', 'sampel_promosi', 'barang_hilang_rusak']);
            
            // Menggunakan Decimal(15,2) sesuai standar finansial di ERD kamu
            $table->decimal('total_bayar', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_stok_keluar');
    }
};
