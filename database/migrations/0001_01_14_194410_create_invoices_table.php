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
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id_invoice');
            $table->string('invoice_number', 100)->unique(); // SNX-INV-YYYY-XXXXX format
            $table->unsignedBigInteger('id_quote'); // Reference to original quote

            // Status and dates
            $table->enum('status', ['Draft', 'Unpaid', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'])->default('Draft');
            $table->dateTime('issue_date');
            $table->dateTime('due_date');
            $table->date('date_invoice')->nullable();
            $table->string('payment_status')->nullable();
            $table->date('date_payment')->nullable();

            // Currency and totals
            $table->string('currency', 3)->default('MAD'); // MAD, USD, EUR
            $table->string('payment_terms')->nullable();
            $table->decimal('sub_total', 15, 2)->default(0); // sum before tax
            $table->decimal('tax_total', 15, 2)->default(0); // sum of VAT
            $table->decimal('discount_total', 15, 2)->default(0); // sum of reductions
            $table->decimal('grand_total', 15, 2)->default(0); // final total
            $table->decimal('total_excl_vat', 15, 2)->nullable();
            $table->decimal('vat_amount', 15, 2)->nullable();
            $table->decimal('total_incl_vat', 15, 2)->nullable();

            // Company info
            $table->string('supplier_vat_number')->nullable();
            $table->string('supplier_iso_certification')->nullable();

            // User tracking
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('id_user')->nullable();

            // Additional info
            $table->text('remarks')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_quote')->references('id_quote')->on('quotes');
            $table->foreign('created_by')->references('id_user')->on('users');
            $table->foreign('id_user')->references('id_user')->on('users');

            // Indexes
            $table->index('id_quote');
            $table->index('created_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
