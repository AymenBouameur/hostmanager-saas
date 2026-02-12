<?php
namespace Majormedia\Listings\Updates\Seeders;

use Seeder;
use DB;
use Carbon\Carbon;

/**
 * CreateExpensesSeeder
 */
class CreateExpensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Insert expenses for listings
        DB::table('majormedia_listings_expenses')->insert([
            [
                'listing_id' => 1, // Make sure this listing ID exists in the listings table
                'label' => 'Electricity Bill',
                'amount' => 150.00,
                'expenses_type' => 1, // You can define different types, here 1 could be "utilities"
                'processed_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'listing_id' => 2, // Listing ID from the listings table
                'label' => 'Water Bill',
                'amount' => 75.00,
                'expenses_type' => 2, // Expenses type can be different, 2 could be "maintenance"
                'processed_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'listing_id' => 3,
                'label' => 'Property Taxes',
                'amount' => 500.00,
                'expenses_type' => 3, // You can define other types for expenses
                'processed_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'listing_id' => 4,
                'label' => 'Cleaning Fees',
                'amount' => 50.00,
                'expenses_type' => 4, // Example expense type
                'processed_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'listing_id' => 5,
                'label' => 'Security Services',
                'amount' => 120.00,
                'expenses_type' => 5, // Example expense type
                'processed_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
