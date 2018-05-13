<?php

use Illuminate\Database\Seeder;
use App\Hotel;
use App\User;

class VisitedHotelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$csvFilePath = storage_path() . '/app/python/ratings.csv';
    	$csvFile = file($csvFilePath);
    	$hotelCount = count(Hotel::all());
    	$faker = Faker\Factory::create();

    	foreach ($csvFile as $line) {
    		$data = str_getcsv($line);
    		list($user_id, $hotel_id, $rating) = $data;
    		if ($user_id <= count(User::all()) && $hotel_id <= $hotelCount) {
    			DB::table('visited_hotels')->insert([
    				'user_id' => $user_id + 1,
    				'hotel_id' => $hotel_id,
                    'rating' => $rating,
    				'created_at' => $faker->dateTimeThisYear($max = 'now'),
    			]);
    		}
    	}
    }
}
