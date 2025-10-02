<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the Products table with initial data.
     * These products will be used as master data for the snapshot-based system.
     * When documents are created, they will snapshot the product data at that point in time.
     */
    public function run(): void
    {
        \App\Models\Product::create([
            'product_code' => 'PROD-001',
            'name' => 'Advanced Server Module',
            'description' => 'High-performance server module for enterprise applications',
            'technical_specs' => 'CPU: Intel Xeon, RAM: 64GB DDR4, Storage: 2TB SSD',
            'commercial_terms' => 'Standard warranty included, extended warranty available',
            'payment_terms' => 'Net 30 days',
            'image_url' => '/images/server-module.jpg',
            'min_delivery_day' => 5,
            'max_delivery_day' => 15,
            'availability_yrs' => 5,
            'status' => 'Active',
            'unit_price' => 2500.00, // Add unit price
        ]);

        \App\Models\Product::create([
            'product_code' => 'PROD-002',
            'name' => 'Network Security Gateway',
            'description' => 'Enterprise-grade network security solution',
            'technical_specs' => 'Firewall: Advanced, VPN: Supported, Monitoring: 24/7',
            'commercial_terms' => 'Annual maintenance contract required',
            'payment_terms' => '50% upfront, 50% on delivery',
            'image_url' => '/images/security-gateway.jpg',
            'min_delivery_day' => 10,
            'max_delivery_day' => 25,
            'availability_yrs' => 3,
            'status' => 'Active',
            'unit_price' => 1800.00, // Add unit price
        ]);

        \App\Models\Product::create([
            'product_code' => 'PROD-003',
            'name' => 'Data Storage Array',
            'description' => 'High-capacity storage solution for data centers',
            'technical_specs' => 'Capacity: 100TB, RAID: 6, Interface: SAS/SATA',
            'commercial_terms' => '3-year warranty, onsite support available',
            'payment_terms' => 'Net 45 days',
            'image_url' => '/images/storage-array.jpg',
            'min_delivery_day' => 15,
            'max_delivery_day' => 30,
            'availability_yrs' => 4,
            'status' => 'Active',
            'unit_price' => 5200.00, // Add unit price
        ]);
    }
}
