<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DeliveryNoteSeeder extends Seeder
{
    /**
     * Seed the delivery_notes table.
     * Creates Delivery Notes from AROs, maintaining data independence.
     */
    public function run(): void
    {
        $purchaseOrders = \App\Models\PurchaseOrder::with('quote.customer')->get();

        foreach ($purchaseOrders as $po) {
            $deliveryNote = \App\Models\DeliveryNote::create([
                'dnp_number' => 'DN-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'id_po' => $po->id_po,
                'id_quote' => $po->id_quote,
                'date_delivery' => $po->planned_delivery_date ?? now(),
                'planned_delivery_date' => $po->planned_delivery_date,
                'actual_delivery_date' => $po->planned_delivery_date ? $po->planned_delivery_date->addDays(rand(-2, 2)) : null,
                'status' => 'Pending',
                'incoterms' => 'CIF',
                'delivery_address' => $po->quote->customer->address,
                'packaging_details' => 'Standard packaging',
                'created_by' => 1, // Assuming admin user id = 1
                'remarks' => 'Auto-generated Delivery Note for PO ' . $po->po_number,
                'client_approved' => false,
            ]);
        }
    }
}
