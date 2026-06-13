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
        Schema::create('job_order', function (Blueprint $table) {
            $table->id();
            // Relasi FK (Pastikan nama tabel induknya sudah sesuai dengan di DB kamu)
            $table->foreignId('nota_produksi_id')->constrained('nota_produksi')->onDelete('cascade');
            $table->foreignId('produk_varian_id')->constrained('produk_varian');
            $table->foreignId('customer_id')->constrained('customer');
            
            $table->integer('jumlah');
            
            // Perubahan dari DOUBLE ke DECIMAL untuk akuntansi keuangan yang akurat
            $table->decimal('biaya_jasa_satuan', 15, 2);
            $table->decimal('total_biaya_bahan', 15, 2)->default(0);
            $table->decimal('total_hpp_job', 15, 2);
            $table->decimal('ongkir_terima_barang', 15, 2)->default(0);
            $table->decimal('biaya_rework_tambahan', 15, 2)->default(0);
            
            // Fitur Custom Specs
            $table->tinyInteger('is_custom')->default(0); // 0 = standar, 1 = kustom
            $table->string('id_req', 45)->nullable();
            $table->string('pb_req', 45)->nullable();
            $table->string('catatan_custom', 255)->nullable();
            $table->string('foto_desain', 255)->nullable(); // Diperpanjang ke 255 agar muat nama file panjang
            
            // Status Produksi & Waktu
            $table->string('status_pesanan')->default('Pending'); // Pending, In Progress, Partial, Completed
            $table->dateTime('estimasi_selesai')->nullable();
            $table->dateTime('tgl_selesai')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order');
    }
};
