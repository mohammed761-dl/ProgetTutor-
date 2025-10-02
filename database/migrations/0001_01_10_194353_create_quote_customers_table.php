<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_quote');
            $table->unsignedBigInteger('id_customer');
            
            // Customer snapshot data
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 50);
            $table->json('address');
            $table->string('vat_number', 50);
            $table->enum('performance_flag', [
                'Always on time',
                'Small delays',
                'Frequent big delays',
                'No payment'
            ]);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_quote')->references('id_quote')->on('quotes')
                  ->onDelete('cascade');
            $table->foreign('id_customer')->references('id_customer')->on('customers')
                  ->onDelete('restrict');

            // Each quote has exactly one customer snapshot
            $table->unique('id_quote');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_customers');
    }
};
