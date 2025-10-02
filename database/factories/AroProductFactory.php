<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AroProduct>
 */
class AroProductFactory extends Factory
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
        $total = $quantity * $unit_price;

        return [
            'id_aro' => \App\Models\ARO::factory(),
            'po_product_id' => \App\Models\PoProduct::factory(),
            'product_code' => fake()->bothify('PRD-####??'),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph,
            'technical_specs' => fake()->optional()->paragraph,
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'total_line_price' => $total,
        ];
    }
}
