<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issue_date = fake()->dateTimeBetween('-1 month', 'now');
        $due_date = fake()->dateTimeBetween($issue_date, '+1 month');
        $total = fake()->numberBetween(1000, 100000);
        $vat = $total * 0.20;
        $discount = fake()->optional(0.3)->numberBetween(0, $total * 0.1);

        return [
            'id_quote' => \App\Models\Quote::factory(),
            'status' => fake()->randomElement(['Draft', 'Unpaid', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled']),
            'issue_date' => $issue_date,
            'due_date' => $due_date,
            'date_invoice' => $issue_date,
            'payment_status' => fake()->randomElement(['Pending', 'Paid', 'Overdue']),
            'date_payment' => null,
            'currency' => fake()->randomElement(['MAD', 'USD', 'EUR']),
            'payment_terms' => fake()->sentence(3),
            'sub_total' => $total,
            'tax_total' => $vat,
            'discount_total' => $discount ?? 0,
            'grand_total' => $total + $vat - ($discount ?? 0),
            'total_excl_vat' => $total,
            'vat_amount' => $vat,
            'total_incl_vat' => $total + $vat,
            'supplier_vat_number' => fake()->numerify('VAT###########'),
            'supplier_iso_certification' => fake()->optional()->numerify('ISO####:####'),
            'created_by' => \App\Models\User::factory(),
            'remarks' => fake()->optional()->paragraph,
            'notes' => fake()->optional()->paragraph,
        ];
    }
}
