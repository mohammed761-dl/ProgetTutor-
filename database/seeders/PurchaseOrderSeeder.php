<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get quotes that have POs
        $acceptedQuotes = Quote::where('has_po', true)->get();
        $commercialUser = User::where('role', 'Commercial')->first();

        foreach ($acceptedQuotes as $quote) {
            \App\Models\PurchaseOrder::create([
                'id_customer' => $quote->id_customer,
                'id_quote' => $quote->id_quote,
                'created_by' => $commercialUser?->id_user ?? User::factory(),
                'status' => 'Approved',
                'planned_delivery_date' => now()->addDays(30),
                'remarks' => 'Auto-generated from quote '.$quote->quote_number,
            ]);
        }
    }
}
