<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_quote' => \App\Models\Quote::factory(),
            'created_by' => \App\Models\User::factory(),
            'status' => fake()->randomElement(['Pending', 'Approved', 'Processing', 'Completed', 'Cancelled']),
            'planned_delivery_date' => fake()->dateTimeBetween('+1 week', '+4 weeks'),
            'actual_delivery_date' => null,
            'remarks' => fake()->optional()->paragraph(),
            'pdf_path' => null,
        ];
    }
}
