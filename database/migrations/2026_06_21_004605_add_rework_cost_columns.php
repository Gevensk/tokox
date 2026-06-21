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
        Schema::table('job_order', function (Blueprint $table) {
            $table->decimal('biaya_rework_tambahan', 15, 2)->default(0)->after('ongkir_terima_barang');
        });

        Schema::table('hasil_produksi', function (Blueprint $table) {
            $table->decimal('biaya_rework_tambahan', 15, 2)->default(0)->after('ongkir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_order', function (Blueprint $table) {
            $table->dropColumn('biaya_rework_tambahan');
        });

        Schema::table('hasil_produksi', function (Blueprint $table) {
            $table->dropColumn('biaya_rework_tambahan');
        });
    }
};
