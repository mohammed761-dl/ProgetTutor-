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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id('id_quote');
            $table->string('quote_number', 100)->unique();
            $table->unsignedBigInteger('id_customer');
            $table->unsignedBigInteger('id_user');
            $table->date('date_quote');
            $table->date('valid_until');
            $table->enum('status', ['Sent same day', 'Sent within 2-3 days', 'Sent after 4+ days']);
            $table->boolean('has_po')->default(false);
            $table->enum('currency', ['EUR', 'USD', 'MAD'])->default('EUR');
            $table->text('payment_terms')->nullable();
            $table->text('delivery_terms')->nullable();
            $table->text('discount_notes')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->decimal('total_ht', 15, 2)->default(0)->comment('Total Hors Taxe (before tax)');
            $table->decimal('reduction', 15, 2)->default(0)->comment('Total discount amount');
            $table->decimal('vat', 15, 2)->default(0)->comment('VAT amount');
            $table->decimal('total_ttc', 15, 2)->default(0)->comment('Total Toutes Taxes Comprises (with tax)');
            $table->string('signature_name')->nullable();
            $table->string('signature_title')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_customer')->references('id_customer')->on('customers');
            $table->foreign('id_user')->references('id_user')->on('users');

            // Indexes
            $table->index('id_customer');
            $table->index('id_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
