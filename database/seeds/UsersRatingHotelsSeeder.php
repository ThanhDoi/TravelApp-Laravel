<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Hotel;

class UsersRatingHotelsSeeder extends Seeder
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

    	foreach ($csvFile as $line) {
    		$data = str_getcsv($line);
    		list($user_id, $hotel_id, $rating) = $data;
    		if ($user_id <= count(User::all()) && $hotel_id <= $hotelCount) {
    			$user = User::where('id', ($user_id + 1))->firstOrFail();
    			$hotel = Hotel::where('id', $hotel_id)->firstOrFail();

    			$user->hotels()->attach($hotel->id, [
    				'rating' => $rating,
    				'predict' => false,
    			]);
    		}
    	}
    }
}
