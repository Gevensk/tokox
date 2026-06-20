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
        // Alter tipe enum to 'in', 'out' using raw SQL to bypass doctrine/dbal limitations with enums
        Illuminate\Support\Facades\DB::statement("ALTER TABLE `penyesuaian_stok_bahan` MODIFY COLUMN `tipe` ENUM('in', 'out') NULL");

        // Add updated_at if not exists
        if (!Schema::hasColumn('penyesuaian_stok_bahan', 'updated_at')) {
            Schema::table('penyesuaian_stok_bahan', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert tipe enum to 'masuk', 'keluar'
        Illuminate\Support\Facades\DB::statement("ALTER TABLE `penyesuaian_stok_bahan` MODIFY COLUMN `tipe` ENUM('masuk', 'keluar') NULL");

        // Remove updated_at if exists
        if (Schema::hasColumn('penyesuaian_stok_bahan', 'updated_at')) {
            Schema::table('penyesuaian_stok_bahan', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }
};
