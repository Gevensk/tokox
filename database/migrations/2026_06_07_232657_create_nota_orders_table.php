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
        Schema::create('nota_order', function (Blueprint $table) {
            $table->increments('id'); 
            $table->string('no_order', 45);
            $table->enum('tipe_order', ['Internal', 'Customer']);
            $table->unsignedInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customer');
            $table->dateTime('tgl_order');
            $table->dateTime('tgl_deadline');
            $table->decimal('harga_deal', 15, 2);
            $table->decimal('nominal_dp_masuk', 15, 2);
            $table->enum('status_bayar', ['Belum Lunas', 'Lunas']);
            $table->enum('status_order', ['Draft', 'Valid', 'Progress', 'Selesai']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_order');
    }
};
