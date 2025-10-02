<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateExistingQuotesVatRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if vat_rate column exists
        if (!Schema::hasColumn('quotes', 'vat_rate')) {
            $this->command->error('vat_rate column does not exist. Please run the migration first.');
            return;
        }

        $this->command->info('Updating existing quotes with NULL vat_rate...');

        // Get quotes with NULL vat_rate
        $quotes = DB::table('quotes')
            ->whereNull('vat_rate')
            ->whereNotNull('vat')
            ->whereNotNull('total_amount')
            ->whereNotNull('reduction')
            ->get();

        $this->command->info("Found {$quotes->count()} quotes to update.");

        $updatedCount = 0;
        foreach ($quotes as $quote) {
            try {
                // Calculate VAT rate from existing data
                $totalAfterReduction = $quote->total_amount - $quote->reduction;
                
                if ($totalAfterReduction > 0 && $quote->vat > 0) {
                    $calculatedVatRate = $quote->vat / $totalAfterReduction;
                    
                    // Update the quote with calculated VAT rate
                    DB::table('quotes')
                        ->where('id_quote', $quote->id_quote)
                        ->update(['vat_rate' => $calculatedVatRate]);
                    
                    $updatedCount++;
                    $this->command->info("Updated quote {$quote->quote_number}: VAT rate = " . round($calculatedVatRate * 100, 1) . "%");
                }
            } catch (\Exception $e) {
                $this->command->error("Error updating quote {$quote->quote_number}: " . $e->getMessage());
            }
        }

        $this->command->info("Successfully updated {$updatedCount} quotes.");
        
        // Also update quotes that have 0 VAT to have 0 VAT rate
        $zeroVatQuotes = DB::table('quotes')
            ->whereNull('vat_rate')
            ->where('vat', 0)
            ->update(['vat_rate' => 0]);
            
        if ($zeroVatQuotes > 0) {
            $this->command->info("Updated {$zeroVatQuotes} quotes with 0 VAT to have 0 VAT rate.");
        }
    }
}
