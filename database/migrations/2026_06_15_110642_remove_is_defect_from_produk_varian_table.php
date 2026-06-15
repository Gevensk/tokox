<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIsDefectFromProdukVarianTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produk_varian', function (Blueprint $table) {
            // Menghapus kolom is_defect secara permanen
            $table->dropColumn('is_defect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_varian', function (Blueprint $table) {
            // Antisipasi jika migration di-rollback, kolom dikembalikan dengan nilai default 0
            $table->boolean('is_defect')->default(0);
        });
    }
}