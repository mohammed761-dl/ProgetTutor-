<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing customers and users
        $customer1 = \App\Models\Customer::first();
        $customer2 = \App\Models\Customer::skip(1)->first();
        $user1 = \App\Models\User::where('role', 'Commercial')->first();
        $user2 = \App\Models\User::where('role', 'CEO')->first();

        if ($customer1 && $user1) {
            \App\Models\Quote::create([
                'quote_number' => 'Q-2024-021',
                'id_customer' => $customer1->id_customer,
                'id_user' => $user1->id_user,
                'date_quote' => now()->subDays(30),
                'valid_until' => now()->addDays(30),
                'status' => 'Sent same day',
                'has_po' => false, // No PO = Pending
                'currency' => 'EUR',
                'payment_terms' => 'Net 30 days',
                'delivery_terms' => 'FOB Origin',
                'total_amount' => 15000.00,
                'signature_name' => 'John Smith',
                'signature_title' => 'Sales Manager',
            ]);

            \App\Models\Quote::create([
                'quote_number' => 'Q-2024-022',
                'id_customer' => $customer1->id_customer,
                'id_user' => $user1->id_user,
                'date_quote' => now()->subDays(20),
                'valid_until' => now()->addDays(40),
                'status' => 'Sent within 2-3 days',
                'has_po' => false, // No PO = Pending
                'currency' => 'EUR',
                'payment_terms' => 'Net 30 days',
                'delivery_terms' => 'FOB Origin',
                'total_amount' => 8500.00,
                'signature_name' => 'John Smith',
                'signature_title' => 'Sales Manager',
            ]);
        }

        if ($customer2 && $user2) {
            \App\Models\Quote::create([
                'quote_number' => 'Q-2024-023',
                'id_customer' => $customer2->id_customer,
                'id_user' => $user2->id_user,
                'date_quote' => now()->subDays(15),
                'valid_until' => now()->addDays(45),
                'status' => 'Sent within 2-3 days',
                'has_po' => true, // Has PO = Accepted
                'currency' => 'USD',
                'payment_terms' => '50% upfront, 50% on delivery',
                'delivery_terms' => 'CIF Destination',
                'total_amount' => 25000.00,
                'signature_name' => 'Jane Doe',
                'signature_title' => 'CEO',
            ]);

            \App\Models\Quote::create([
                'quote_number' => 'Q-2024-024',
                'id_customer' => $customer2->id_customer,
                'id_user' => $user2->id_user,
                'date_quote' => now()->subDays(10),
                'valid_until' => now()->addDays(50),
                'status' => 'Sent same day',
                'has_po' => true, // Has PO = Accepted
                'currency' => 'EUR',
                'payment_terms' => 'Net 30 days',
                'delivery_terms' => 'FOB Origin',
                'total_amount' => 12000.00,
                'signature_name' => 'Jane Doe',
                'signature_title' => 'CEO',
            ]);
        }
    }
}
