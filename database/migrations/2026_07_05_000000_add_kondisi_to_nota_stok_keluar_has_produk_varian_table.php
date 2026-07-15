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
        Schema::table('nota_stok_keluar_has_produk_varian', function (Blueprint $table) {
            $table->enum('kondisi', ['normal', 'defect'])->default('normal')->after('produk_varian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_stok_keluar_has_produk_varian', function (Blueprint $table) {
            $table->dropColumn('kondisi');
        });
    }
};
