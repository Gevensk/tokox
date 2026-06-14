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
        Schema::table('nota_order', function (Blueprint $table) {
            // 1. Mengubah nama kolom dari harga_deal menjadi total_harga
            if (Schema::hasColumn('nota_order', 'harga_deal')) {
                $table->renameColumn('harga_deal', 'total_harga');
            }

            // 2. Menambahkan kolom baru harga_satuan_items jika belum ada
            if (!Schema::hasColumn('nota_order', 'harga_satuan_items')) {
                // Menggunakan tipe TEXT agar bisa menampung rincian panjang, dibuat nullable agar tidak error di data lama
                $table->decimal('harga_satuan_items',15,2)->nullable()->after('nominal_dp_masuk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_order', function (Blueprint $table) {
            // Rollback: Kembalikan total_harga menjadi harga_deal
            if (Schema::hasColumn('nota_order', 'total_harga')) {
                $table->renameColumn('total_harga', 'harga_deal');
            }

            // Rollback: Hapus kolom harga_satuan_items yang tadi dibuat baru
            if (Schema::hasColumn('nota_order', 'harga_satuan_items')) {
                $table->dropColumn('harga_satuan_items');
            }
        });
    }
};