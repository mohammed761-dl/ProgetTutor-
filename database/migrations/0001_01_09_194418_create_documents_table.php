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
        Schema::create('documents', function (Blueprint $table) {
            $table->id('id_document');
            $table->string('file_name');
            $table->text('file_path');
            $table->enum('type', ['Quote', 'PO', 'ARO', 'DNP', 'Invoice', 'Product']);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('documentable_id');
            $table->string('documentable_type', 50);
            $table->timestamps();

            // Index for polymorphic relationship
            $table->index(['documentable_type', 'documentable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
