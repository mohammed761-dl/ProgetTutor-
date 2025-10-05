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
        Schema::create('invoice_quote_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('quote_product_id'); // Reference to original quote
            $table->unsignedBigInteger('dnp_product_id')->nullable(); // Reference to delivered products

            // Copy of quote product data (original terms)
            $table->string('product_code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('technical_specs')->nullable();
            $table->text('commercial_terms')->nullable();
            $table->text('payment_terms')->nullable();

            // Invoice-specific quantities and calculations
            $table->integer('quantity_invoiced'); // Based on delivery note quantity
            $table->decimal('unit_price', 15, 2); // From original quote
            $table->decimal('total_ht', 15, 2); // unit_price × quantity
            $table->decimal('vat_amount', 15, 2); // VAT per line
            $table->decimal('reduction', 15, 2)->default(0); // discount per line
            $table->decimal('line_total', 15, 2); // total_ht + vat_amount - reduction

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('invoice_id')->references('id_invoice')->on('invoices')
                ->onDelete('restrict');
            $table->foreign('quote_product_id')->references('id')->on('quote_products')
                ->onDelete('restrict');
            $table->foreign('dnp_product_id')->references('id')->on('dnp_products')
                ->onDelete('restrict');

            // Unique constraints
            $table->unique(['invoice_id', 'quote_product_id']);
            $table->unique(['invoice_id', 'dnp_product_id']);

            // Indexes for performance
            $table->index(['invoice_id', 'quote_product_id']);
            $table->index(['invoice_id', 'dnp_product_id']);
            $table->index('product_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_quote_product');
    }
};
