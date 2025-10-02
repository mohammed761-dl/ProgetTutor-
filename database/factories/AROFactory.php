<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ARO>
 */
class AROFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'id_po' => PurchaseOrder::factory(),
            'date_aro' => $date,
            'status' => fake()->randomElement(['Pending', 'Approved', 'Rejected', 'Cancelled']),
            'created_by' => User::factory(),
            'remarks' => fake()->optional()->paragraph(),
        ];
    }
}
