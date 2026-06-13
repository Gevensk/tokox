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
        Schema::create('nota_produksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjahit_id')->constrained('penjahit');

            $table->string('no_surat_jalan', 50)->unique();
            $table->dateTime('tgl_order');
            $table->dateTime('tgl_kirim')->nullable();

            $table->string('status_nota', 30)->default('Draft'); // Draft, Aktif, Selesai
            $table->enum('status_kirim', ['Belum Dikirim', 'Dikirim', 'Diterima'])->default('Belum Dikirim');
            
            $table->decimal('ongkir_kirim_bahan', 15, 2)->default(0);
            $table->string('penerima_bahan', 100)->nullable();
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_produksi');
    }
};
