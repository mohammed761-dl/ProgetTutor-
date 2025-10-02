<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DnpProduct>
 */
class DnpProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_dnp' => \App\Models\DeliveryNote::factory(),
            'aro_product_id' => \App\Models\AroProduct::factory(),
            'quantity_shipped' => fake()->numberBetween(1, 100),
            'serial_numbers' => fake()->optional()->numerify('SN###-###-###,SN###-###-###'),
            'tracking_code' => fake()->optional()->bothify('TRK-????####'),
        ];
    }
}
