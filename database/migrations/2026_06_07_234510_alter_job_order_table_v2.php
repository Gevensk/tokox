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
        Schema::disableForeignKeyConstraints();

        Schema::table('job_order', function (Blueprint $table) {
            if (Schema::hasColumn('job_order', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
   
            if (Schema::hasColumn('job_order', 'ongkir_terima_barang')) {
                $table->dropColumn('ongkir_terima_barang');
            }

            if (!Schema::hasColumn('job_order', 'nota_order_id')) {
                $table->foreignId('nota_order_id')->after('id')->constrained('nota_order')->onDelete('cascade');
            }

            $table->foreignId('nota_produksi_id')->nullable()->change();
            $table->enum('status_pesanan', ['Antrean', 'Diproduksi', 'Selesai Parsial', 'Selesai'])->default('Antrean')->change();
        });

        Schema::enableForeignKeyConstraints();
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_order', function (Blueprint $table) {
            // Logika untuk mengembalikan struktur jika kamu melakukan rollback (opsional)
            $table->dropForeign(['nota_order_id']);
            $table->dropColumn('nota_order_id');
            $table->foreignId('customer_id')->constrained('customer');
            $table->decimal('ongkir_terima_barang', 15, 2)->default(0);
            $table->foreignId('nota_produksi_id')->nullable(false)->change();
            $table->string('status_pesanan')->default('Pending')->change();
        });
    }
};
