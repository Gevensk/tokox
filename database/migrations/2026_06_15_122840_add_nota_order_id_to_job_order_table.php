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
    Schema::table('job_order', function (Blueprint $table) {
        $table->unsignedInteger('nota_order_id')->nullable()->after('id');
        $table->foreign('nota_order_id')->references('id')->on('nota_order')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('job_order', function (Blueprint $table) {
        $table->dropForeign(['nota_order_id']);
        $table->dropColumn('nota_order_id');
    });
}
};
