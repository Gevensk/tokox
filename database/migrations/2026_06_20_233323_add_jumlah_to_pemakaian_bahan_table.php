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
        if (!Schema::hasColumn('pemakaian_bahan', 'jumlah')) {
            Schema::table('pemakaian_bahan', function (Blueprint $table) {
                $table->decimal('jumlah', 15, 4)->after('tgl_keluar');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pemakaian_bahan', 'jumlah')) {
            Schema::table('pemakaian_bahan', function (Blueprint $table) {
                $table->dropColumn('jumlah');
            });
        }
    }
};
