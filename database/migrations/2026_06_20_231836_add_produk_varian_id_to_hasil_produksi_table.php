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
        if (!Schema::hasColumn('hasil_produksi', 'produk_varian_id')) {
            Schema::table('hasil_produksi', function (Blueprint $table) {
                $table->unsignedInteger('produk_varian_id')->nullable()->after('job_order_id');
                $table->foreign('produk_varian_id')->references('id')->on('produk_varian')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('hasil_produksi', 'produk_varian_id')) {
            Schema::table('hasil_produksi', function (Blueprint $table) {
                $table->dropForeign(['produk_varian_id']);
                $table->dropColumn('produk_varian_id');
            });
        }
    }
};
