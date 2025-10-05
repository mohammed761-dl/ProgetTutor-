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
        Schema::create('po_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_po');
            $table->unsignedBigInteger('quote_product_id'); // Reference to quote_product entry

            // Copy of quote product data at PO creation time
            $table->string('product_code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('technical_specs')->nullable();
            $table->text('commercial_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->integer('min_delivery_day');
            $table->integer('max_delivery_day');

            // PO-specific fields
            $table->integer('quantity'); // Cannot exceed quote quantity
            $table->decimal('unit_price', 15, 2); // From quote
            $table->decimal('total_line_price', 15, 2)->storedAs('quantity * unit_price');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_po')->references('id_po')->on('purchase_orders')
                ->onDelete('restrict');
            $table->foreign('quote_product_id')->references('id')->on('quote_products')
                ->onDelete('restrict');

            // Unique constraint
            $table->unique(['id_po', 'quote_product_id']);

            // Indexes for performance
            $table->index(['id_po', 'quote_product_id']);
            $table->index('product_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_products');
    }
};
