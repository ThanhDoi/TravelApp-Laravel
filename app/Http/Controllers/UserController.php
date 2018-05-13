<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
	public function update(Request $request) {
		$user = Auth::guard('api')->user();

		$password = $request['password'];
		if (Hash::check($password, $user->password)) {
			$user->name = $request['name'];
			$user->generateToken();
			$user->save();
			return response()->json([
				'data' => $user->toArray()
			], 200);
		} else {
			return response()->json([
				'error' => 'Wrong password'
			], 422);
		}
	}

	public function getVisitedHotels(Request $request) {
		$user = Auth::guard('api')->user();
		$results = DB::table('visited_hotels')->where('user_id', $user->id)->get()->all();
		return response()->json([
			'data' => $results
		], 200);
	}
}
