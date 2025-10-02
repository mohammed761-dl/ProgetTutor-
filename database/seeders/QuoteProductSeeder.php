<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class QuoteProductSeeder extends Seeder
{
    /**
     * Seed the quote_products table with snapshot data.
     * This seeder demonstrates the snapshot-based architecture by creating
     * independent product data for each quote.
     */
    public function run(): void
    {
        $quotes = \App\Models\Quote::all();
        $products = \App\Models\Product::all();

        foreach ($quotes as $quote) {
            // Add 2-3 random products to each quote
            $randomProducts = $products->random(rand(2, 3));
            
            foreach ($randomProducts as $product) {
                // Create product snapshot with slight price variations
                $priceVariation = rand(-5, 5) / 100; // -5% to +5%
                $adjustedPrice = $product->unit_price * (1 + $priceVariation);
                
                \App\Models\QuoteProduct::create([
                    'id_quote' => $quote->id_quote,
                    'id_product' => $product->id_product,
                    'product_code' => $product->product_code,
                    'name' => $product->name,
                    'description' => $product->description,
                    'technical_specs' => $product->technical_specs,
                    'quantity' => rand(1, 5),
                    'unit_price' => round($adjustedPrice, 2),
                    'commercial_terms' => $product->commercial_terms,
                    'payment_terms' => $product->payment_terms,
                    'min_delivery_day' => $product->min_delivery_day,
                    'max_delivery_day' => $product->max_delivery_day,
                    'availability_yrs' => rand(1, 5),
                ]);
            }
        }
    }
}
