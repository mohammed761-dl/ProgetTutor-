<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CompanyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\CompanyInfo::create([
            'id_company_info' => 1,
            'name' => 'Senselix ERP Solutions',
            'address' => '123 Business Park, Technology District, TD 12345',
            'phone' => '+1-555-0001',
            'email' => 'info@senselix.com',
            'website' => 'https://www.senselix.com',
            'warranty_duration' => 12,
            'support_info' => '24/7 technical support available via phone and email',
            'bank_name' => 'First National Bank',
            'swift_bic' => 'FNBNUS33',
            'account_number' => '1234567890',
            'iban' => 'US12FNBN1234567890123456',
            'authorized_name' => 'John Smith',
            'authorized_title' => 'Chief Executive Officer',
            'signature_email' => 'john.smith@senselix.com',
            'signature_phone' => '+1-555-0002',
            'general_conditions_url' => 'https://www.senselix.com/terms',
            'vat_number' => 'US123456789',
        ]);
    }
}
