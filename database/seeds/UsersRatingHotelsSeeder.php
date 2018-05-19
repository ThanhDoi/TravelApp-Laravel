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
    	$csvFilePath = storage_path() . '/app/python/hotel_ratings.csv';
    	$csvFile = file($csvFilePath);
    	$hotelCount = count(Hotel::all());

    	foreach ($csvFile as $line) {
    		$data = str_getcsv($line);
    		list($user_id, $hotel_id, $rating) = $data;
    		DB::table('hotel_user')
            ->insert([
                'user_id' => $user_id + 1,
                'hotel_id' => $hotel_id,
                'rating' => $rating,
                'predict' => 0
            ]);
    	}
    }
}
