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
        Schema::create('hasil_produksi', function (Blueprint $table) {
            $table->id();
            // Relasi foreign key ke tabel job_orders
            $table->foreignId('job_order_id')->constrained('job_order')->onDelete('cascade');
            // Relasi foreign key ke tabel produk_varian
            $table->foreignId('produk_varian_id')->constrained('produk_varian')->onDelete('cascade');
            
            $table->dateTime('tgl_terima');
            $table->decimal('ongkir', 15, 2)->default(0);
            $table->integer('qty_accepted')->default(0);
            $table->integer('qty_rejected_defect')->default(0);
            $table->integer('qty_rejected')->default(0);
            $table->string('catatan_qc', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_produksi');
    }
};
