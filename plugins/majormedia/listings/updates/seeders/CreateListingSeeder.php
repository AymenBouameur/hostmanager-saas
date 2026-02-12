<?php
namespace Majormedia\Listings\Updates\Seeders;

use Seeder;
use DB;

/**
 * CreateListingSeeder
 */
class CreateListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('majormedia_listings_listings')->insert([
            [
                'title' => 'Appartement moderne à Marrakech',
                'address' => 'Avenue Mohammed VI, Marrakech, Maroc',
                'description' => 'Appartement de luxe avec une vue panoramique, situé dans le quartier le plus prisé de Marrakech.',
                'user_id' => 1, 
                'external_id' => 1001,
                'is_active' => 1,
                'is_pinned' => 0,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Villa de luxe à Casablanca',
                'address' => 'Boulevard de la Corniche, Casablanca, Maroc',
                'description' => 'Magnifique villa avec piscine privée et jardin, située en bord de mer à Casablanca.',
                'user_id' => 2, 
                'external_id' => 1002,
                'is_active' => 1,
                'is_pinned' => 0,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Appartement à vendre à Rabat',
                'address' => 'Rue Mohammed V, Rabat, Maroc',
                'description' => 'Appartement spacieux et bien éclairé, proche des commerces et des transports publics.',
                'user_id' => 3, 
                'external_id' => 1003,
                'is_active' => 1,
                'is_pinned' => 1, 
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Appartement meublé à Tanger',
                'address' => 'Quartier Hassan II, Tanger, Maroc',
                'description' => 'Appartement entièrement meublé avec des équipements modernes, à quelques pas de la plage.',
                'user_id' => 4,
                'external_id' => 1004,
                'is_active' => 1,
                'is_pinned' => 0,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Studio à louer à Agadir',
                'address' => 'Avenue Hassan II, Agadir, Maroc',
                'description' => 'Studio lumineux avec vue sur la mer, idéal pour une location saisonnière à Agadir.',
                'user_id' => 1, 
                'external_id' => 1005,
                'is_active' => 1,
                'is_pinned' => 0,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
