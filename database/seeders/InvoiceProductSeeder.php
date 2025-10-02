<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InvoiceProductSeeder extends Seeder
{
    /**
     * Seed the invoice_products table with snapshot data.
     * Creates independent product snapshots for each Invoice based on Delivery Note products.
     */
    public function run(): void
    {
        $invoices = \App\Models\Invoice::with('deliveryNote.products')->get();

        foreach ($invoices as $invoice) {
            if ($invoice->deliveryNote && $invoice->deliveryNote->products) {
                foreach ($invoice->deliveryNote->products as $dnProduct) {
                    // Create snapshot from Delivery Note product data
                    \App\Models\InvoiceProduct::create([
                        'id_invoice' => $invoice->id_invoice,
                        'product_code' => $dnProduct->product_code,
                        'name' => $dnProduct->name,
                        'description' => $dnProduct->description,
                        'technical_specs' => $dnProduct->technical_specs,
                        'quantity_invoiced' => $dnProduct->quantity_shipped,
                        'unit_price' => $dnProduct->unit_price,
                        'total_price' => $dnProduct->quantity_shipped * $dnProduct->unit_price,
                        'serial_numbers' => $dnProduct->serial_numbers,
                    ]);
                }
            }
        }
    }
}
