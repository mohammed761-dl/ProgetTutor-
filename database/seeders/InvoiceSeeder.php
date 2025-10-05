<?php

// filepath: database/seeders/InvoiceSeeder.php

namespace Database\Seeders;

use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Seed the invoices table.
     * Creates Invoices from delivered Delivery Notes, maintaining data independence.
     */
    public function run(): void
    {
        // Get delivered delivery notes
        $deliveryNotes = DeliveryNote::with(['products', 'aro.purchaseOrder.quote.customer'])
            ->where('status', 'Delivered')
            ->get();

        $users = User::where('role', 'Finance')->get();

        if (! $users->count()) {
            $users = User::all(); // Fallback to all users if no finance users
        }

        foreach ($deliveryNotes as $index => $deliveryNote) {
            // Calculate totals based on actual delivered products
            $baseAmount = $deliveryNote->products->sum(function ($product) {
                return $product->quantity_shipped * $product->unit_price;
            });
            $vatAmount = $baseAmount * 0.21; // 21% VAT
            $totalAmount = $baseAmount + $vatAmount;

            $customer = $deliveryNote->aro->purchaseOrder->quote->customer;

            Invoice::create([
                'invoice_number' => 'INV-'.date('Y').str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'id_dnp' => $deliveryNote->id_dnp,
                'customer_po_no' => $deliveryNote->aro->purchaseOrder->customer_po_number,
                'date_invoice' => $deliveryNote->actual_delivery_date,
                'payment_status' => 'Pending',
                'date_payment' => null,
                'id_user' => $users->random()->id_user,
                'currency' => $deliveryNote->aro->purchaseOrder->currency,
                'payment_terms' => $deliveryNote->aro->purchaseOrder->payment_terms,
                'total_excl_vat' => $baseAmount,
                'vat_amount' => $vatAmount,
                'total_incl_vat' => $totalAmount,
                'supplier_vat_number' => 'BE0123456789',
                'supplier_contact_person' => 'John Doe',
                'supplier_email' => 'supplier@senselix.com',
                'supplier_phone' => '+32 123 456 789',
                'customer_contact_person' => $customer->contact_name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
            ]);
        }
    }
}
