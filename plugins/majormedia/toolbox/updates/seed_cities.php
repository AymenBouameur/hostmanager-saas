<?php
namespace MajorMedia\ToolBox\Updates;

use October\Rain\Database\Updates\Seeder;

use DB;
use File;
use Illuminate\Support\Facades\Storage;
class CitiesTableSeeder extends Seeder
{
    public function run()
    {
        /* @Deprecated ==> managed with \MajorMedia\ToolBox\Traits\Countries */
        $jsonFilePath = base_path('plugins/majormedia/toolbox/seeders/cities_morocco.json');


        // Load the JSON file and decode it into an array
        $json = File::get($jsonFilePath);
        $cities = json_decode($json, true);
        foreach ($cities as $city) {
            DB::table('majormedia_toolbox_cities')->insert([
                'name' => $city['city'],
                'lat' => $city['lat'],
                'lng' => $city['lng'],
                'state_id' => null,
                'slug' => \Str::slug($city['city']),
                'is_active' => true,
                'is_pinned' => false,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
