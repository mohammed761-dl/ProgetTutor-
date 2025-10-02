<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Customer::create([
            'company_name' => 'Tech Solutions Inc.',
            'contact_name' => 'John Smith',
            'email' => 'john.smith@techsolutions.com',
            'phone' => '+1-555-0123',
            'address' => '123 Business Ave, Tech City, TC 12345',
            'performance_flag' => 'Always on time',
            'vat_number' => 'US123456789',
        ]);

        \App\Models\Customer::create([
            'company_name' => 'Global Industries Ltd.',
            'contact_name' => 'Sarah Johnson',
            'email' => 'sarah.johnson@globalindustries.com',
            'phone' => '+1-555-0456',
            'address' => '456 Corporate Blvd, Business District, BD 67890',
            'performance_flag' => 'Small delays',
            'vat_number' => 'US987654321',
        ]);

        \App\Models\Customer::create([
            'company_name' => 'Innovation Corp.',
            'contact_name' => 'Michael Brown',
            'email' => 'michael.brown@innovationcorp.com',
            'phone' => '+1-555-0789',
            'address' => '789 Innovation St, Startup City, SC 11111',
            'performance_flag' => 'Frequent big delays',
            'vat_number' => 'US111222333',
        ]);
    }
}
