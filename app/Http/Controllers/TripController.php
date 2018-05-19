<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Hotel;
use App\Trip;
use App\Attraction;

class TripController extends Controller
{
    public function createTripByHotel(Request $request) {
    	$user = Auth::guard('api')->user();
    	$hotel_id = $request['hotel_id'];

    	$trip_name = $request['trip_name'];
    	$start_date = $request['start_date'];
    	$end_date = $request['end_date'];

    	$trip = Trip::create([
    		'name' => $trip_name,
    		'start_date' => $start_date,
    		'end_date' => $end_date,
    		'user_id' => 2
    	]);

    	$hotel = Hotel::where('id', $hotel_id)->first();
    	$trip->hotels()->save($hotel);

    	return response()->json([
    		'data' => 'Saved'
    	], 200);
    }

    public function createTripByAttraction(Request $request) {
    	$user = Auth::guard('api')->user();
    	$attraction_id = $request['attraction_id'];

    	$trip_name = $request['trip_name'];
    	$start_date = $request['start_date'];
    	$end_date = $request['end_date'];

    	$trip = Trip::create([
    		'name' => $trip_name,
    		'start_date' => $start_date,
    		'end_date' => $end_date,
    		'user_id' => 2
    	]);

    	$attraction = Attraction::where('id', $attraction_id)->first();
    	$trip->attractions()->save($attraction);

    	return response()->json([
    		'data' => 'Saved'
    	], 200);
    }

    public function getItemsInTrip(Request $request, $id) {
    	$trip = Trip::where('id', $id)->first();
    	$hotels = $trip->hotels()->get()->pluck('id')->all();
    	$attractions = $trip->attractions()->get()->pluck('id')->all();

    	return response()->json([
    		'hotels' => $hotels,
    		'attractions' => $attractions
    	], 200);
    }

    public function addHotel(Request $request, $id) {
    	$trip = Trip::where('id', $id)->first();
    	$hotel_id = $request["hotel_id"];
    	$hotel = Hotel::where('id', $hotel_id)->first();
    	$trip->hotels()->save($hotel);

    	return response()->json([
    		'data' => 'Saved'
    	], 200);
    }

    public function addAttraction(Request $request, $id) {
    	$trip = Trip::where('id', $id)->first();
    	$attraction_id = $request["attraction_id"];
    	$attraction = Attraction::where('id', $attraction_id)->first();
    	$trip->attractions()->save($attraction);

    	return response()->json([
    		'data' => 'Saved'
    	], 200);
    }

    public function removeHotel(Request $request, $id) {
    	$trip = Trip::where('id', $id)->first();
    	$hotel_id = $request["hotel_id"];
    	$hotel = Hotel::where('id', $hotel_id)->first();
    	$trip->hotels()->detach($hotel);

    	return response()->json([
    		'data' => 'Removed'
    	], 200);
    }

    public function removeAttraction(Request $request, $id) {
    	$trip = Trip::where('id', $id)->first();
    	$attraction_id = $request["attraction_id"];
    	$attraction = Attraction::where('id', $attraction_id)->first();
    	$trip->attractions()->detach($attraction);

    	return response()->json([
    		'data' => 'Removed'
    	], 200);
    }

    public function delete($id) {
    	$trip = Trip::findOrFail($id);
    	$trip->delete();

    	return response()->json([
    		'data' => 'Deleted',
    	], 204);
    }
}
