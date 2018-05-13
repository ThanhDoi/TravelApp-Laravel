<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Hotel;
use Illuminate\Support\Facades\Auth;

class RecommenderController extends Controller
{
	public function getCBData(Request $request) {
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

	public function getCFData(Request $request) {
		$users = implode("|", array_column(DB::table('hotel_user')->where('predict', 0)->get()->all(), 'user_id'));
		$hotels = implode("|", array_column(DB::table('hotel_user')->where('predict', 0)->get()->all(), 'hotel_id'));
		$ratings = implode("|", array_column(DB::table('hotel_user')->where('predict', 0)->get()->all(), 'rating'));
		
		return response()->json([
			'users' => $users,
			'hotels' => $hotels,
			'ratings' => $ratings
		], 200);
	}

	public function predictCB(Request $request) {
		$user_id = $request['user_id'];

		$user = User::where('id', $user_id)->firstOrFail();

		$result = exec("python3 " . storage_path() . "/app/python/content-based.py " . escapeshellarg($user_id));

		$count = 1;

		foreach (preg_split('/],\s*\[/', trim($result, '[]')) as $row) {
			$data = preg_split("/',\s*'/", trim($row, "'"));
			$rating = $data[0];
			$hotel = $user->hotels->where('id', $count)->first();
			if ($hotel == null) {
				$hotel = Hotel::where('id', $count)->firstOrFail();
				$user->hotels()->attach($hotel->id, [
					'rating' => $rating,
					'predict' => 1,
				]);
			} 
			$count++;
		}

		$predictItems = $user->hotels()->where('predict', 1)->orderBy('rating', 'desc')->take(20)->get()->pluck('pivot');
		DB::table('hotel_user')->where('user_id', $user_id)->where('predict', 1)->delete();

		return response()->json([
			'data' => $predictItems,
		], 200);
	}

	public function predictCF(Request $request) {
		$user_id = $request['user_id'];
		$city_id = $request['city_id'];

		$results = exec("python3 " . storage_path() . "/app/python/collaborative.py " . escapeshellarg($user_id));
		$results = json_decode($results, true);
		arsort($results);
		$predictItems = array();

		foreach ($results as $key => $value) {
			if (Hotel::where('id', $key)->first()->city_id == $city_id) {
				array_push($predictItems, array($key => $value));
			}
		}
		$predictItems = array_slice($predictItems, 0, 20);
		return response()->json([
			'data' => $predictItems,
		], 200);
	}

	public function calculateAvgRating($city_id) {
		$results = DB::table('visited_hotels')
		->join('hotels', 'visited_hotels.hotel_id', '=', 'hotels.id')
		->select(DB::raw('avg(rating) as avgRating, hotel_id, count(user_id)'))
		->where('hotels.city_id', '=', $city_id)
		->groupBy('hotel_id')
		->orderBy('count(user_id)', 'DESC')
		->take(10)->get()->pluck('hotel_id');
		return $results;
	}

	public function getRecommend(Request $request) {
		$user = Auth::guard('api')->user();
		$city_id = $request['city_id'];
		
		if (count($user->hotels->all()) == 0) {
			return response()->json([
				'new_user' => 1,
				'data' => $this->calculateAvgRating($city_id)
			], 200);
		}

		$result = exec("python3 " . storage_path() . "/app/python/content-based.py " . escapeshellarg($user->id));

		$count = 1;

		foreach (preg_split('/],\s*\[/', trim($result, '[]')) as $row) {
			$data = preg_split("/',\s*'/", trim($row, "'"));
			$rating = $data[0];
			$hotel = $user->hotels->where('id', $count)->first();
			if ($hotel == null) {
				$hotel = Hotel::where('id', $count)->firstOrFail();
				$user->hotels()->attach($hotel->id, [
					'rating' => $rating,
					'predict' => 1,
				]);
			} 
			$count++;
		}

		$predictCB = $user->hotels()->where('predict', 1)->where('city_id', $city_id)->orderBy('rating', 'desc')->take(20)->get()->pluck('pivot');
		DB::table('hotel_user')->where('user_id', $user->id)->where('predict', 1)->delete();

		$results = exec("python3 " . storage_path() . "/app/python/collaborative.py " . escapeshellarg($user->id));
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
}
