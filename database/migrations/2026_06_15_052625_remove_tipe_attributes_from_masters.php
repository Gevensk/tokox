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
        if (Schema::hasColumn('customer', 'tipe')) {
            Schema::table('customer', function (Blueprint $table) {
                $table->dropColumn('tipe');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        if (!Schema::hasColumn('customer', 'tipe')) {
            Schema::table('customer', function (Blueprint $table) {
                // Menggunakan string biasa atau varchar pendek untuk menghemat memori
                $table->string('tipe', 30)->nullable()->after('nama');
            });
        }


    }
};