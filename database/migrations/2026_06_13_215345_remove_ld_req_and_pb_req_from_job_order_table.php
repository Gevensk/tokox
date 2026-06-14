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
    Schema::table('job_order', function (Blueprint $table) {
        // Cek dulu apakah kolom 'ld_req' ada, kalau ada baru hapus
        if (Schema::hasColumn('job_order', 'ld_req')) {
            $table->dropColumn('ld_req');
        }
        
        // Cek dulu apakah kolom 'pb_req' ada, kalau ada baru hapus
        if (Schema::hasColumn('job_order', 'pb_req')) {
            $table->dropColumn('pb_req');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_order', function (Blueprint $table) {
            // Antisipasi jika suatu saat kamu melakukan rollback, kolom akan dikembalikan
            $table->string('ld_req', 45)->nullable()->after('is_custom');
            $table->string('pb_req', 45)->nullable()->after('ld_req');
        });
    }
};