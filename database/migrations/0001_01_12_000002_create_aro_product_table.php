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
        Schema::create('aro_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_aro');
            $table->unsignedBigInteger('quote_product_id');
            $table->integer('quantity_received');
            $table->text('remarks')->nullable();
            $table->decimal('unit_price_agreed', 10, 2)->nullable();
            $table->decimal('total_line_price', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_aro')
                  ->references('id_aro')
                  ->on('aros')
                  ->onDelete('cascade');

            $table->foreign('quote_product_id')
                  ->references('id')
                  ->on('quote_products')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aro_product');
    }
};
