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
        Schema::create('quote_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_quote');
            $table->unsignedBigInteger('id_product');
            
            // Product snapshot data (frozen at quote creation)
            $table->string('product_code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('technical_specs')->nullable();
            $table->text('commercial_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->integer('min_delivery_day');
            $table->integer('max_delivery_day');
            $table->integer('availability_yrs');
            
            // Quote-specific data
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2); // Price at quote time
            $table->decimal('total_line_price', 15, 2)->storedAs('quantity * unit_price');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_quote')->references('id_quote')->on('quotes');
            $table->foreign('id_product')->references('id_product')->on('products')
                  ->onDelete('restrict'); // Prevent product deletion if used in quotes

            // Unique constraint to prevent duplicate products in same quote
            $table->unique(['id_quote', 'id_product']);
            
            // Indexes for performance
            $table->index(['id_quote', 'id_product']);
            $table->index('product_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_products');
    }
};
