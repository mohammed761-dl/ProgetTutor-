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
        Schema::create('aros', function (Blueprint $table) {
            $table->id('id_aro');
            $table->string('aro_number')->unique();
            $table->unsignedBigInteger('id_po');
            $table->unsignedBigInteger('created_by');
            $table->date('date_aro');
            $table->enum('status', ['Pending', 'Delivered', 'Partially Delivered', 'Cancelled'])->default('Pending');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('id_po')
                ->references('id_po')
                ->on('purchase_orders')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id_user')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aros');
    }
};
