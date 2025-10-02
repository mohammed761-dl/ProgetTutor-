<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryNote>
 */
class DeliveryNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_aro' => \App\Models\ARO::factory(),
            'status' => fake()->randomElement(['Pending', 'Delivered', 'Partially Delivered', 'Returned', 'Cancelled']),
            'client_approved' => fake()->boolean,
            'date_delivery' => fake()->dateTimeBetween('now', '+2 weeks'),
            'planned_delivery_date' => fake()->dateTimeBetween('now', '+1 month'),
            'actual_delivery_date' => null,
            'incoterms' => fake()->randomElement(['EXW', 'FOB', 'CIF', 'DDP']),
            'delivery_address' => fake()->address,
            'packaging_details' => fake()->optional()->sentence,
            'created_by' => \App\Models\User::factory(),
            'remarks' => fake()->optional()->paragraph,
        ];
    }
}
