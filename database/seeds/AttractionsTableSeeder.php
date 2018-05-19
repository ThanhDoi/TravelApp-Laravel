<?php

use Illuminate\Database\Seeder;
use App\Attraction;

class AttractionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvFilePath = storage_path() . '/app/python/attractions.csv';
    	$csvFile = file($csvFilePath);
    	$i = 0;
    	foreach ($csvFile as $line) {
    		$data = str_getcsv($line);
    		if ($i == 0) {
    			$i++;
    			continue;
    		}
    		$feature_vectors = implode("|", array_slice($data, 1, 19));
    		Attraction::create([
                'city_id' => $data[0],
    			'name' => $data[23],
    			'location' => $data[22],
    			'features' => $data[20],
    			'feature_vectors' => $feature_vectors,
    			'img_url' => $data[21],
    		]);
    	}
    }
}
