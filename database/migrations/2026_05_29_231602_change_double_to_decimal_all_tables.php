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
        Schema::table('bill_of_materials', function (Blueprint $table) {
            $table->decimal('jumlah', 15, 4)->change();
        });

        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->decimal('stok_total', 15, 4)->change();
            $table->decimal('harga_rata_rata', 15, 2)->change();
        });

        Schema::table('nota_pembelian', function (Blueprint $table) {
            $table->decimal('total_harga_bahan', 15, 2)->change();
            $table->decimal('ongkir', 15, 2)->change();
            $table->decimal('total_bayar', 15, 2)->change();
        });

        Schema::table('nota_pembelian_has_bahan_baku',function(Blueprint $table){
            $table->decimal('jumlah', 15, 4)->change(); //ini 
            $table->decimal('harga_satuan', 15, 2)->change();
            $table->decimal('subtotal', 15, 2)->change();
        });

        Schema::table('produk_varian',function(Blueprint $table){
            $table->decimal('harga_avg_produk', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('bill_of_materials', function (Blueprint $table) {
            $table->double('jumlah')->change();
        });

        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->double('stok_total')->change();
            $table->double('harga_rata_rata')->change();
        });

        Schema::table('nota_pembelian', function (Blueprint $table) {
            $table->double('total_harga_bahan')->change();
            $table->double('ongkir')->change();
            $table->double('total_bayar')->change();
        });

        Schema::table('nota_pembelian_has_bahan_baku', function (Blueprint $table) {
            $table->double('jumlah')->change();
            $table->double('harga_satuan')->change();
            $table->double('subtotal')->change();
        });

        Schema::table('produk_varian', function (Blueprint $table) {
            $table->double('harga_avg_produk')->change();
        });
    }
};
