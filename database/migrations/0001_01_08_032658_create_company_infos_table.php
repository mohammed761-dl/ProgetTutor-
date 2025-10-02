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
        Schema::create('company_info', function (Blueprint $table) {
            $table->integer('id_company_info')->primary();
            $table->string('name');
            $table->text('address');
            $table->string('phone', 50);
            $table->string('email');
            $table->string('website')->nullable();
            $table->integer('warranty_duration');
            $table->text('support_info');
            $table->string('bank_name');
            $table->string('swift_bic', 50);
            $table->string('account_number', 50);
            $table->string('iban', 50);
            $table->string('authorized_name');
            $table->string('authorized_title');
            $table->string('signature_email');
            $table->string('signature_phone', 50);
            $table->string('general_conditions_url')->nullable();
            $table->string('vat_number', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_info');
    }
};
