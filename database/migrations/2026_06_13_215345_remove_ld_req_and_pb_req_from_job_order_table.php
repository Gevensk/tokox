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
            // Menghapus kolom ld_req dan pb_req dari tabel job_order
            $table->dropColumn(['ld_req', 'pb_req']);
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