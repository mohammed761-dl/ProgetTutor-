<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PoProductSeeder extends Seeder
{
    /**
     * Seed the po_products table with snapshot data.
     * Creates independent product snapshots for each PO, based on quote products.
     */
    public function run(): void
    {
        $purchaseOrders = \App\Models\PurchaseOrder::with('quote')->get();

        foreach ($purchaseOrders as $po) {
            if ($po->quote) {
                $quoteProducts = \App\Models\QuoteProduct::where('id_quote', $po->quote->id_quote)->get();
                foreach ($quoteProducts as $quoteProduct) {
                    // Check if this combination already exists
                    $exists = \App\Models\PoProduct::where('id_po', $po->id_po)
                        ->where('quote_product_id', $quoteProduct->id)
                        ->exists();

                    if (!$exists) {
                        \App\Models\PoProduct::createWithSnapshot($po->id_po, $quoteProduct);
                    }
                }
            }
        }
    }
}
