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
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id('id_dnp');
            $table->string('dnp_number', 100)->unique();
            $table->unsignedBigInteger('id_po');
            $table->unsignedBigInteger('id_quote');
            $table->unsignedBigInteger('id_aro')->nullable();
            // Status fields
            $table->enum('status', ['Pending', 'Delivered', 'Partially Delivered', 'Returned', 'Cancelled'])->default('Pending');

            // User tracking
            $table->unsignedBigInteger('created_by')->nullable();

            // Date fields
            $table->date('date_delivery');
            $table->date('planned_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            // Client and order info
            $table->boolean('client_approved')->default(false);

            // Delivery details
            $table->string('incoterms', 20)->nullable();
            $table->text('delivery_address');
            $table->text('packaging_details')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_po')->references('id_po')->on('purchase_orders');
            $table->foreign('id_quote')->references('id_quote')->on('quotes');
            $table->foreign('created_by')->references('id_user')->on('users');
            $table->foreign('id_aro')->references('id_aro')->on('aros');
            // Indexes
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};
