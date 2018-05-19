<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Hotel;
use Illuminate\Support\Facades\Auth;
use App\Attraction;

class RecommenderController extends Controller
{
	public function getHotelCBData(Request $request) {
		$user_id = $request->get('user_id');

		$feature_vectors = implode("|", array_column(DB::table('hotels')->get()->all(), 'feature_vectors'));
		$hotel_ids = implode("|", array_column(DB::table('hotel_user')->where('user_id', $user_id)->where('predict', 0)->get()->all(), 'hotel_id'));
		$ratings = implode("|", array_column(DB::table('hotel_user')->where('user_id', $user_id)->where('predict', 0)->get()->all(), "rating"));

		return response()->json([
			'feature_vectors' => $feature_vectors,
			'hotel_ids' => $hotel_ids,
			'ratings' => $ratings
		], 200);
	}

	public function getHotelCFData(Request $request) {
		$users = implode("|", array_column(DB::table('hotel_user')->where('predict', 0)->get()->all(), 'user_id'));
		$hotels = implode("|", array_column(DB::table('hotel_user')->where('predict', 0)->get()->all(), 'hotel_id'));
		$ratings = implode("|", array_column(DB::table('hotel_user')->where('predict', 0)->get()->all(), 'rating'));
		
		return response()->json([
			'users' => $users,
			'hotels' => $hotels,
			'ratings' => $ratings
		], 200);
	}

	public function getAttractionCFData(Request $request) {
		$users = implode("|", array_column(DB::table('attraction_user')->where('predict', 0)->get()->all(), 'user_id'));
		$attractions = implode("|", array_column(DB::table('attraction_user')->where('predict', 0)->get()->all(), 'attraction_id'));
		$ratings = implode("|", array_column(DB::table('attraction_user')->where('predict', 0)->get()->all(), 'rating'));
		
		return response()->json([
			'users' => $users,
			'attractions' => $attractions,
			'ratings' => $ratings
		], 200);
	}

	public function getAttractionCBData(Request $request) {
		$user_id = $request->get('user_id');

		$feature_vectors = implode("|", array_column(DB::table('attractions')->get()->all(), 'feature_vectors'));
		$attraction_ids = implode("|", array_column(DB::table('attraction_user')->where('user_id', $user_id)->where('predict', 0)->get()->all(), 'attraction_id'));
		$ratings = implode("|", array_column(DB::table('attraction_user')->where('user_id', $user_id)->where('predict', 0)->get()->all(), "rating"));

		return response()->json([
			'feature_vectors' => $feature_vectors,
			'attraction_ids' => $attraction_ids,
			'ratings' => $ratings
		], 200);
	}

	public function calculateHotelAvgRating($city_id) {
		$results = DB::table('visited_hotels')
		->join('hotels', 'visited_hotels.hotel_id', '=', 'hotels.id')
		->select(DB::raw('avg(rating) as avgRating, hotel_id, count(user_id)'))
		->where('hotels.city_id', '=', $city_id)
		->groupBy('hotel_id')
		->orderBy('count(user_id)', 'DESC')
		->take(10)->get()->pluck('hotel_id');
		return $results;
	}

	public function getHotelRecommend(Request $request) {
		$user = Auth::guard('api')->user();
		$city_id = $request['city_id'];
		
		if (count(DB::table('hotel_user')->where('user_id', $user->id)->get()->all()) == 0) {
			return response()->json([
				'new_user' => 1,
				'data' => $this->calculateHotelAvgRating($city_id)
			], 200);
		}

		$result = exec("python3 " . storage_path() . "/app/python/hotel_cb.py " . escapeshellarg($user->id));

		$count = 1;

		foreach (preg_split('/],\s*\[/', trim($result, '[]')) as $row) {
			$data = preg_split("/',\s*'/", trim($row, "'"));
			$rating = $data[0];
			$fetch = DB::table('hotel_user')
			->where('user_id', $user->id)
			->where('hotel_id', $count)
			->get()
			->first();

			if ($fetch == null) {
				DB::table('hotel_user')
				->insert([
					'user_id' => $user->id,
					'hotel_id' => $count,
					'rating' => $rating,
					'predict' => 1,
				]);
			} 
			$count++;
		}
		$predictCB = DB::table('hotel_user')->join('hotels', 'hotel_user.hotel_id', '=', 'hotels.id')->select('hotel_id', 'rating')->where('user_id', $user->id)->where('predict', 1)->where('city_id', $city_id)->orderBy('rating', 'desc')->take(20)->get()->all();
		DB::table('hotel_user')->where('user_id', $user->id)->where('predict', 1)->delete();

		$results = exec("python3 " . storage_path() . "/app/python/hotel_cf.py " . escapeshellarg($user->id));
		$results = json_decode($results, true);
		arsort($results);
		$predictItems = array();

		foreach ($results as $key => $value) {
			if (Hotel::where('id', $key)->first()->city_id == $city_id) {
				array_push($predictItems, array($key => $value));
			}
		}
		$predictCF = array_slice($predictItems, 0, 20);
		return response()->json([
			'CB' => $predictCB,
			'CF' => $predictCF
		], 200);
	}

	public function calculateAttractionAvgRating($city_id) {
		$results = DB::table('visited_attractions')
		->join('attractions', 'visited_attractions.attraction_id', '=', 'attractions.id')
		->select(DB::raw('avg(rating) as avgRating, attraction_id, count(user_id)'))
		->where('attractions.city_id', '=', $city_id)
		->groupBy('attraction_id')
		->orderBy('count(user_id)', 'DESC')
		->take(10)->get()->pluck('attraction_id');
		return $results;
	}

	public function getAttractionRecommend(Request $request) {
		$user = Auth::guard('api')->user();
		$city_id = $request['city_id'];
		
		if (count(DB::table('attraction_user')->where('user_id', $user->id)->get()->all()) == 0) {
			return response()->json([
				'new_user' => 1,
				'data' => $this->calculateAttractionAvgRating($city_id)
			], 200);
		}

		$result = exec("python3 " . storage_path() . "/app/python/attraction_cb.py " . escapeshellarg($user->id));

		$count = 1;

		foreach (preg_split('/],\s*\[/', trim($result, '[]')) as $row) {
			$data = preg_split("/',\s*'/", trim($row, "'"));
			$rating = $data[0];
			$result = DB::table('attraction_user')
			->where('user_id', $user->id)
			->where('attraction_id', $count)
			->get()
			->first();

			if ($result == null) {
				DB::table('attraction_user')
				->insert([
					'user_id' => $user->id,
					'attraction_id' => $count,
					'rating' => $rating,
					'predict' => 1,
				]);
			} 
			$count++;
		}

		$predictCB = DB::table('attraction_user')->join('attractions', 'attraction_user.attraction_id', '=', 'attractions.id')->select('attraction_id', 'rating')->where('user_id', $user->id)->where('predict', 1)->where('city_id', $city_id)->orderBy('rating', 'desc')->take(20)->get()->all();
		DB::table('attraction_user')->where('user_id', $user->id)->where('predict', 1)->delete();

		$results = exec("python3 " . storage_path() . "/app/python/attraction_cf.py " . escapeshellarg($user->id));
		$results = json_decode($results, true);
		arsort($results);
		$predictItems = array();

		foreach ($results as $key => $value) {
			if (Attraction::where('id', $key)->first()->city_id == $city_id) {
				array_push($predictItems, array($key => $value));
			}
		}
		$predictCF = array_slice($predictItems, 0, 20);
		return response()->json([
			'CB' => $predictCB,
			'CF' => $predictCF
		], 200);
	}
}
