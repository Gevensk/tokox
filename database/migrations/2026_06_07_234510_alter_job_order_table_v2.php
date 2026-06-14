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
        Schema::table('job_order', function (Blueprint $table) {
            // 1. Hapus foreign key-nya terlebih dahulu
            $table->dropForeign(['customer_id']); 
            // ATAU gunakan nama constraint-nya langsung jika di atas gagal:
            // $table->dropForeign('job_order_customer_id_foreign');

            // 2. Baru setelah itu kolomnya aman untuk dihapus
            $table->dropColumn('customer_id');
        });
    }
    /**
     * Reverse the migrations.
     */
public function down()
{
    Schema::table('job_order', function (Blueprint $table) {
        // 1. Buat kembali kolomnya
        $table->unsignedBigInteger('customer_id')->nullable(); 

        // 2. Pasang kembali foreign key relasinya
        $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
    });
}
};
