<?php

use Illuminate\Database\Seeder;

class VisitedAttractionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvFilePath = storage_path() . '/app/python/attraction_ratings.csv';
    	$csvFile = file($csvFilePath);
        $faker = Faker\Factory::create();

    	foreach ($csvFile as $line) {
    		$data = str_getcsv($line);
    		list($user_id, $attraction_id, $rating) = $data;
    		DB::table('visited_attractions')
    		->insert([
    			'user_id' => $user_id + 1,
    			'attraction_id' => $attraction_id,
    			'rating' => $rating,
                'created_at' => $faker->dateTimeThisYear($max = 'now'),
    		]);
    	}
    }
}
