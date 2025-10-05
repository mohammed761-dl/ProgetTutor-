<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateQuote = now();
        $validUntil = $dateQuote->copy()->addMonth();

        return [
            'quote_number' => 'QT-'.$dateQuote->format('Ym').'-'.fake()->unique()->numberBetween(1, 9999),
            'date_quote' => $dateQuote,
            'valid_until' => $validUntil,
            'status' => fake()->randomElement(['Sent same day', 'Sent within 2-3 days', 'Sent after 4+ days']),
            'has_po' => false,
            'currency' => fake()->randomElement(['EUR', 'USD', 'MAD']),
            'payment_terms' => fake()->sentence(),
            'delivery_terms' => fake()->sentence(),
            'discount_notes' => fake()->optional()->sentence(),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'total_ht' => fake()->randomFloat(2, 100, 10000),
            'reduction' => fake()->randomFloat(2, 0, 1000),
            'vat' => fake()->randomFloat(2, 0, 2000),
            'total_ttc' => fake()->randomFloat(2, 100, 12000),
            'signature_name' => fake()->optional()->name(),
            'signature_title' => fake()->optional()->jobTitle(),
        ];
    }
}
