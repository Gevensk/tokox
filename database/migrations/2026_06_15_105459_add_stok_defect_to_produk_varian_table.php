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
        Schema::table('produk_varian', function (Blueprint $table) {
        // Menambahkan kolom untuk menampung stok reject/cacat mikro (4 byte)
            $table->integer('stok_defect')->default(0)->after('stok_tersedia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_varian', function (Blueprint $table) {
            //
        });
    }
};
