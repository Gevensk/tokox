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
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            // Mengubah struktur tabel agar tgl_keluar diizinkan bernilai NULL
            $table->datetime('tgl_keluar')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            // Mengembalikan ke pengaturan awal jika dilakukan rollback
            $table->datetime('tgl_keluar')->nullable(false)->change();
        });
    }
};
