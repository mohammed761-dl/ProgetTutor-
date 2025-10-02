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
        Schema::create('customers', function (Blueprint $table) {
            $table->id('id_customer');
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone', 50);
            $table->text('address');
            $table->enum('performance_flag', ['Always on time', 'Small delays', 'Frequent big delays', 'No payment']);
            $table->string('vat_number', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
