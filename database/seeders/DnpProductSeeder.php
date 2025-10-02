<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DnpProductSeeder extends Seeder
{
    /**
     * Seed the dnp_product table with snapshot data.
     * Creates independent product snapshots for each Delivery Note based on ARO products.
     */
    public function run(): void
    {
        $deliveryNotes = \App\Models\DeliveryNote::with('purchaseOrder.poProducts')->get();

        foreach ($deliveryNotes as $dn) {
            if ($dn->purchaseOrder && $dn->purchaseOrder->poProducts) {
                foreach ($dn->purchaseOrder->poProducts as $poProduct) {
                    // Create snapshot from PO product data
                    \App\Models\DnpProduct::create([
                        'id_dnp' => $dn->id_dnp,
                        'po_product_id' => $poProduct->id,
                        'product_code' => $poProduct->product_code,
                        'name' => $poProduct->name,
                        'description' => $poProduct->description,
                        'technical_specs' => $poProduct->technical_specs,
                        'quantity_shipped' => $poProduct->quantity, // Using full PO quantity
                        'unit_price' => $poProduct->unit_price,
                        'serial_numbers' => 'SN' . rand(10000, 99999),
                        'tracking_code' => 'TRK' . rand(100000, 999999),
                    ]);
                }
            }
        }
    }
}
