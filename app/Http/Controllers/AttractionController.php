<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attraction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttractionController extends Controller
{
    public function index() {
    	return Attraction::all();
    }

    public function avgRating(Request $request, $id) {
        $user = Auth::guard('api')->user();
    	$ratings = DB::table('visited_attractions')->where('attraction_id', $id)->get()->pluck('rating')->all();
        $user_rating = DB::table('visited_attractions')->where('user_id', $user->id)->where('attraction_id', $id)->get()->pluck('rating');
        $user_recommend_rating = DB::table('attraction_user')->where('user_id', $user->id)->where('attraction_id', $id)->get()->pluck('rating');

    	if (count($ratings) == 0) {
    		return response()->json([
    			'nodata' => 1
    		], 200);
    	}

        if (count($user_rating) == 0) {
            $user_rating[0] = -1;
        }

        if (count($user_recommend_rating) == 0) {
            $user_recommend_rating[0] = -1;
        }

    	$average = array_sum($ratings) / count($ratings);
    	return response()->json([
    		'nodata' => 0,
    		'average' => $average,
    		'count' => count($ratings),
            'user_rating' => $user_rating[0],
            'user_recommend_rating' => $user_recommend_rating[0]
    	], 200);
    }

    public function updateRate(Request $request, $id) {
    	$user = Auth::guard('api')->user();
    	$rate = $request["rate"];

        $row = DB::table('visited_attractions')
        ->where('user_id', $user->id)
        ->where('attraction_id', $id)
        ->first();

        if ($row == null) {
            DB::table('visited_attractions')
            ->insert([
                'user_id' => $user->id,
                'attraction_id' => $id,
                'rating' => $rate,
                'created_at' => Carbon::now()
            ]);
        } else {
            DB::table('visited_attractions')
            ->where('user_id', $user->id)
            ->where('attraction_id', $id)
            ->update(['rating' => $rate]);
        }

    	return response()->json([
    		'data' => 'Saved'
    	], 200);
    }

    public function updateRecommendRate(Request $request, $id) {
        $user = Auth::guard('api')->user();
        $rate = $request["rate"];

        $row = DB::table('attraction_user')
        ->where('user_id', $user->id)
        ->where('attraction_id', $id)
        ->first();

        if ($row == null) {
            DB::table('attraction_user')
            ->insert([
                'user_id' => $user->id,
                'attraction_id' => $id,
                'rating' => $rate,
                'predict' => 0
            ]);
        } else {
            DB::table('attraction_user')
            ->where('user_id', $user->id)
            ->where('attraction_id', $id)
            ->update(['rating' => $rate]);
        }

        return response()->json([
            'data' => 'Saved'
        ], 200);
    }
}
