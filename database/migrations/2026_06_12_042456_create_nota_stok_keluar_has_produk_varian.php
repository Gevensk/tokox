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
        Schema::create('nota_stok_keluar_has_produk_varian', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel induk nota_stok_keluar
            $table->foreignId('nota_stok_keluar_id')
                  ->constrained('nota_stok_keluar')
                  ->cascadeOnDelete(); // Jika nota induk dihapus, detailnya otomatis ikut terhapus
            
            // Relasi ke tabel produk_varian untuk tahu warna/ukuran apa yang keluar
            $table->foreignId('produk_varian_id')->constrained('produk_varian');
            
            // Kuantitas dan pencatatan harga riil saat barang keluar
            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_stok_keluar_has_produk_varian');
    }
};
