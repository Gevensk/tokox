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
        // Mengubah kolom menjadi nullable
        $table->integer('nota_produksi_id')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('job_order', function (Blueprint $table) {
        $table->integer('nota_produksi_id')->unsigned()->nullable(false)->change();
    });
}
};
