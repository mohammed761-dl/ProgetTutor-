<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceProduct>
 */
class InvoiceProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 100);
        $unit_price = fake()->randomFloat(2, 10, 1000);
        $total_ht = $quantity * $unit_price;
        $vat_rate = 0.20;
        $vat_amount = $total_ht * $vat_rate;
        $reduction = fake()->optional(0.3)->randomFloat(2, 0, $total_ht * 0.1);

        return [
            'invoice_id' => \App\Models\Invoice::factory(),
            'quote_product_id' => \App\Models\QuoteProduct::factory(),
            'product_name' => fake()->words(3, true),
            'unit_price' => $unit_price,
            'quantity_invoiced' => $quantity,
            'total_ht' => $total_ht,
            'vat_amount' => $vat_amount,
            'reduction' => $reduction ?? 0,
            'line_total' => $total_ht + $vat_amount - ($reduction ?? 0),
        ];
    }
}
