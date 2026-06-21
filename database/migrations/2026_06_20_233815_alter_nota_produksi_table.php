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
        Schema::table('nota_produksi', function (Blueprint $table) {
            if (!Schema::hasColumn('nota_produksi', 'no_surat_jalan')) {
                $table->string('no_surat_jalan', 50)->unique()->nullable()->after('penjahit_id');
            }
            if (!Schema::hasColumn('nota_produksi', 'tgl_kirim')) {
                $table->dateTime('tgl_kirim')->nullable()->after('tgl_order');
            }
            if (!Schema::hasColumn('nota_produksi', 'penerima_bahan')) {
                $table->string('penerima_bahan', 100)->nullable()->after('ongkir_kirim_bahan');
            }
            if (!Schema::hasColumn('nota_produksi', 'catatan')) {
                $table->text('catatan')->nullable()->after('penerima_bahan');
            }
        });

        Illuminate\Support\Facades\DB::statement("ALTER TABLE `nota_produksi` MODIFY COLUMN `status_kirim` ENUM('Belum Dikirim', 'Dikirim', 'Diterima') DEFAULT 'Belum Dikirim'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_produksi', function (Blueprint $table) {
            if (Schema::hasColumn('nota_produksi', 'no_surat_jalan')) {
                $table->dropColumn('no_surat_jalan');
            }
            if (Schema::hasColumn('nota_produksi', 'tgl_kirim')) {
                $table->dropColumn('tgl_kirim');
            }
            if (Schema::hasColumn('nota_produksi', 'penerima_bahan')) {
                $table->dropColumn('penerima_bahan');
            }
            if (Schema::hasColumn('nota_produksi', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });

        Illuminate\Support\Facades\DB::statement("ALTER TABLE `nota_produksi` MODIFY COLUMN `status_kirim` ENUM('Dikirim', 'Diterima') DEFAULT NULL");
    }
};
