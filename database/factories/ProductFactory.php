<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_code' => strtoupper($this->faker->unique()->bothify('PRD-##??')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'technical_specs' => json_encode(['spec1' => $this->faker->word(), 'spec2' => $this->faker->word()]),
            'commercial_terms' => json_encode(['term1' => $this->faker->sentence(), 'term2' => $this->faker->sentence()]),
            'payment_terms' => json_encode(['term1' => $this->faker->sentence(), 'term2' => $this->faker->sentence()]),
            'image_url' => $this->faker->imageUrl(),
            'min_delivery_day' => $minDelivery = $this->faker->numberBetween(1, 10),
            'max_delivery_day' => $this->faker->numberBetween($minDelivery + 1, $minDelivery + 20),
            'availability_yrs' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['Active', 'EOL', 'Archived']),
            'unit_price' => $this->faker->randomFloat(2, 10, 1000)
        ];
    }
}
