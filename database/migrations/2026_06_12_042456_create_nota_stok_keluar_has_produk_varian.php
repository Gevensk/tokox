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
            $table->increments('id');
            
            // Relasi ke tabel induk nota_stok_keluar
            $table->unsignedInteger('nota_stok_keluar_id');
            $table->foreign('nota_stok_keluar_id')->references('id')->on('nota_stok_keluar')->onDelete('cascade'); // Jika nota induk dihapus, detailnya otomatis ikut terhapus
            
            // Relasi ke tabel produk_varian untuk tahu warna/ukuran apa yang keluar
            $table->unsignedInteger('produk_varian_id');
            $table->foreign('produk_varian_id')->references('id')->on('produk_varian');
            
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
