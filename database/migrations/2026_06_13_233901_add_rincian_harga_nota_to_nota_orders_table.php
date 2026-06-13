<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nota_order', function (Blueprint $table) {
            // Kolom ini nullable agar orderan lama yang belum punya harga tidak error
            $table->json('rincian_harga_nota')->nullable()->after('status_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('nota_order', function (Blueprint $table) {
            $table->dropColumn('rincian_harga_nota');
        });
    }
};
