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
    		$feature_vectors = implode("|", array_slice($data, 0, 12));
    		Hotel::create([
    			'name' => $data[17],
    			'location' => $data[14],
    			'price' => $data[15],
    			'star' => $data[16],
    			'features' => $data[12],
    			'feature_vectors' => $feature_vectors,
    			'img_url' => $data[13],
    		]);
    	}
    }
}
