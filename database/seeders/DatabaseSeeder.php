<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // System tables first
            AdminSeeder::class,
            UserSeeder::class,
            CompanyInfoSeeder::class,

            // Base tables
            CustomerSeeder::class,
            ProductSeeder::class,

        ]);
    }
}
