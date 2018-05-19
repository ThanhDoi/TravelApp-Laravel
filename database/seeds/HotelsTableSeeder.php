<?php

use Illuminate\Database\Seeder;
use App\Hotel;

class HotelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$csvFilePath = storage_path() . '/app/python/hotels.csv';
    	$csvFile = file($csvFilePath);
    	$i = 0;
    	foreach ($csvFile as $line) {
    		$data = str_getcsv($line);
    		if ($i == 0) {
    			$i++;
    			continue;
    		}
    		$feature_vectors = implode("|", array_slice($data, 1, 12));
    		Hotel::create([
                'city_id' => $data[0],
                'name' => $data[18],
                'location' => $data[15],
                'price' => $data[16],
                'star' => $data[17],
                'features' => $data[13],
                'feature_vectors' => $feature_vectors,
                'img_url' => $data[14],
            ]);
    	}
    }
}
