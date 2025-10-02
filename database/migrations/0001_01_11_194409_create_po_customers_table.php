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
        Schema::create('po_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_po');
            $table->unsignedBigInteger('id_customer');
            $table->string('company_name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('performance_flag')->nullable();
            $table->timestamps();

            $table->foreign('id_po')
                ->references('id_po')
                ->on('purchase_orders')
                ->onDelete('cascade');

            $table->foreign('id_customer')
                ->references('id_customer')
                ->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_customers');
    }
};
