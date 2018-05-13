<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Hotel;
use App\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HotelController extends Controller
{
    public function index() {
    	return Hotel::all();
    }

    public function avgRating(Request $request, $id) {
        $user = Auth::guard('api')->user();
    	$ratings = DB::table('visited_hotels')->where('hotel_id', $id)->get()->pluck('rating')->all();
        $user_rating = DB::table('visited_hotels')->where('user_id', $user->id)->where('hotel_id', $id)->get()->pluck('rating');

    	if (count($ratings) == 0) {
    		return response()->json([
    			'nodata' => 1
    		], 200);
    	}

        if (count($user_rating) == 0) {
            $user_rating[0] = -1;
        }

    	$average = array_sum($ratings) / count($ratings);
    	return response()->json([
    		'nodata' => 0,
    		'average' => $average,
    		'count' => count($ratings),
            'user_rating' => $user_rating[0]
    	], 200);
    }

    public function ratedScore(Request $request, $id) {
        $user = Auth::guard('api')->user();
    	$ratedScore = DB::table('visited_hotels')->where('hotel_id', $id)->where('user_id', $user->id)->get()->pluck('rating')->first();

    	if ($ratedScore == null) {
    		return response()->json([
    			'rated' => 0
    		], 200);
    	}

    	return response()->json([
    		'rated' => $ratedScore
    	], 200);
    }

    public function updateRate(Request $request, $id) {
    	$user = Auth::guard('api')->user();
    	$rate = $request["rate"];

        $row = DB::table('visited_hotels')
        ->where('user_id', $user->id)
        ->where('hotel_id', $id)
        ->first();

        if ($row == null) {
            DB::table('visited_hotels')
            ->insert([
                'user_id' => $user->id,
                'hotel_id' => $id,
                'rating' => $rate,
                'created_at' => Carbon::now()
            ]);
        } else {
            DB::table('visited_hotels')
            ->where('user_id', $user->id)
            ->where('hotel_id', $id)
            ->update(['rating' => $rate]);
        }

        $hotel = $user->hotels->where('hotel_id', $id)->first();

        if ($hotel == null) {
            $hotel = Hotel::where('id', $id)->first();
            $user->hotels()->attach($hotel->id, [
                'rating' => $rate,
                'predict' => 0
            ]);
        } else {
            $hotel->pivot->rating = $rate;
            $hotel->pivot->save();
        }

    	return response()->json([
    		'data' => 'Saved'
    	], 200);
    }

    public function updateRecommendRate(Request $request, $id) {
        $user = Auth::guard('api')->user();
        $rate = $request["rate"];

        $hotel = $user->hotels->where('id', $id)->first();

        if ($hotel == null) {
            $hotel = Hotel::where('id', $id)->first();
            $user->hotels()->attach($hotel->id, [
                'rating' => $rate,
                'predict' => 0
            ]);
        } else {
            $hotel->pivot->rating = $rate;
            $hotel->pivot->save();
        }

        return response()->json([
            'data' => 'Saved'
        ], 200);
    }
}
