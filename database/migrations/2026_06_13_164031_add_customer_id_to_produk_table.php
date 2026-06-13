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
        Schema::table('produk', function (Blueprint $table) {
            // Menambahkan kolom customer_id bersifat nullable setelah kolom satuan
            $table->unsignedBigInteger('customer_id')->nullable()->after('satuan');

            // Membuat relasi foreign key ke tabel customer
            // Menambahkan onDelete('set null') agar jika data customer dihapus, produk custom-nya tidak ikut terhapus melainkan kolom customer_id berubah jadi NULL (berubah status jadi produk umum).
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customer')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu sebelum menghapus kolomnya
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};