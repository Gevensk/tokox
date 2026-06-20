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
        Schema::create('nota_pembelian_has_bahan_baku', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('nota_pembelian_id');
            $table->foreign('nota_pembelian_id')->references('id')->on('nota_pembelian')->onDelete('cascade');
            $table->unsignedInteger('bahan_baku_id');
            $table->foreign('bahan_baku_id')->references('id')->on('bahan_baku')->onDelete('cascade');
            $table->double('jumlah');
            $table->double('harga_satuan');
            $table->double('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_pembelian_has_bahan_baku');
    }
};
