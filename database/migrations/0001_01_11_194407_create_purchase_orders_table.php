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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id('id_po');
            $table->string('po_number', 100)->unique();
            $table->unsignedBigInteger('id_customer');
            $table->unsignedBigInteger('id_quote')->nullable(); // Optional - PO can be created without a quote
            $table->unsignedBigInteger('created_by');
            $table->enum('status', ['Pending', 'Approved', 'Delivered', 'Cancelled'])->default('Pending');
            $table->date('planned_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_customer')->references('id_customer')->on('customers');
            $table->foreign('id_quote')->references('id_quote')->on('quotes');
            $table->foreign('created_by')->references('id_user')->on('users');

            // Indexes
            $table->index('id_quote');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
