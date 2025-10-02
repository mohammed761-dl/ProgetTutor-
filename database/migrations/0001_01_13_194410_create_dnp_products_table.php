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
        Schema::create('dnp_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_dnp');
            $table->unsignedBigInteger('po_product_id'); // Reference to po_product entry
            
            // Copy of PO product data at delivery note creation time
            $table->string('product_code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('technical_specs')->nullable();
            
            // Delivery-specific fields
            $table->integer('quantity_shipped'); // Cannot exceed PO quantity
            $table->decimal('unit_price', 15, 2); // From PO
            $table->decimal('total_line_price', 15, 2)->storedAs('quantity_shipped * unit_price');
            
            // Additional tracking fields
            $table->text('serial_numbers')->nullable();
            $table->string('tracking_code', 100)->nullable();
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_dnp')->references('id_dnp')->on('delivery_notes')
                  ->onDelete('restrict');
            $table->foreign('po_product_id')->references('id')->on('po_products')
                  ->onDelete('restrict');

            // Unique constraint
            $table->unique(['id_dnp', 'po_product_id']);
            
            // Indexes for performance
            $table->index(['id_dnp', 'po_product_id']);
            $table->index('product_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dnp_products');
    }
};
