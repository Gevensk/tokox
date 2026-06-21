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
            $table->string('tipe', 20)->default('bom')->after('harga_rata_saat_ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }
};
