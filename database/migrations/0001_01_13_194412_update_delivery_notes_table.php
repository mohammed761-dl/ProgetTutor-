<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
           
            if (Schema::hasColumn('delivery_notes', 'delivery_status')) {
                $table->dropColumn('delivery_status');
            }
            if (Schema::hasColumn('delivery_notes', 'customer_po_number')) {
                $table->dropColumn('customer_po_number');
            }

            // Add new columns if they don't exist
            if (!Schema::hasColumn('delivery_notes', 'id_po')) {
                $table->unsignedBigInteger('id_po')->after('dnp_number');
                $table->foreign('id_po')->references('id_po')->on('purchase_orders');
            }
            if (!Schema::hasColumn('delivery_notes', 'id_quote')) {
                $table->unsignedBigInteger('id_quote')->after('id_po');
                $table->foreign('id_quote')->references('id_quote')->on('quotes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            // Drop new foreign keys
            $table->dropForeign(['id_po']);
            $table->dropForeign(['id_quote']);

            // Drop new columns
            $table->dropColumn(['id_po', 'id_quote']);

            // Add back old columns
            $table->unsignedBigInteger('id_aro')->after('dnp_number');
            $table->enum('delivery_status', ['Delivered', 'Problem', 'Blocked'])->after('id_aro');
            $table->string('customer_po_number', 100)->nullable()->after('client_approved');

            // Add back old foreign key
            $table->foreign('id_aro')->references('id_aro')->on('aros');
        });
    }
};
