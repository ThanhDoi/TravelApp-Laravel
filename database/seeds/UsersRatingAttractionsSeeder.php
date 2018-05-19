<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersRatingAttractionsSeeder extends Seeder
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

    	foreach ($csvFile as $line) {
    		$data = str_getcsv($line);
    		list($user_id, $attraction_id, $rating) = $data;
    		DB::table('attraction_user')
    		->insert([
    			'user_id' => $user_id + 1,
    			'attraction_id' => $attraction_id,
    			'rating' => $rating,
    			'predict' => 0
    		]);
    	}
    }
}
