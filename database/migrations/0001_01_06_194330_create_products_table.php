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
        Schema::create('products', function (Blueprint $table) {
            $table->id('id_product');
            $table->string('product_code', 100)->unique();
            $table->string('name');
            $table->text('description');
            $table->text('technical_specs');
            $table->text('commercial_terms');
            $table->text('payment_terms');
            $table->string('image_url')->nullable();
            $table->integer('min_delivery_day');
            $table->integer('max_delivery_day');
            $table->integer('availability_yrs');
            $table->enum('status', ['Active', 'EOL', 'Archived']);
            $table->decimal('unit_price', 15, 2)->default(0.00)->comment('Current unit price for product');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
