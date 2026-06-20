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
        // 1. Memodifikasi Tabel Produk (Menghapus ikatan customer_id)
        Schema::table('produk', function (Blueprint $table) {
            // Kita buat kolom customer_id menjadi NULLABLE dulu agar data lama tidak error,
            // atau jika Anda yakin ingin menghapusnya langsung, gunakan dropColumn.
            $table->integer('customer_id')->nullable()->change(); 
        });

        // 2. Memodifikasi Tabel Hasil Produksi (Menambahkan kolom status_alokasi)
        Schema::table('hasil_produksi', function (Blueprint $table) {
            $table->enum('status_alokasi', ['Stok Toko', 'Pesanan Customer'])
                  ->default('Stok Toko')
                  ->after('qty_accepted'); // Meletakkannya setelah kolom qty_accepted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->integer('customer_id')->nullable(false)->change();
        });

        Schema::table('hasil_produksi', function (Blueprint $table) {
            $table->dropColumn('status_alokasi');
        });
    }
};