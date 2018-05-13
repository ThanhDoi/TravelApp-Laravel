<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'Auth\RegisterController@register');
Route::post('/login', 'Auth\LoginController@login');

Route::middleware('auth:api')->group(function() {
	Route::put('/users/', 'UserController@update');
	Route::get('/users/ratedHotels', 'UserController@ratedHotels');
	Route::get('/users/visitedHotels', 'UserController@getVisitedHotels');

	Route::get('/getRecommend', 'RecommenderController@getRecommend');

	Route::get('hotels/{id}/ratedScore', 'HotelController@ratedScore');
	Route::get('/hotels/{id}/avgRating', 'HotelController@avgRating');
	Route::post('/hotels/{id}/rate', 'HotelController@updateRate');
	Route::post('hotels/{id}/rateRecommend', 'HotelController@updateRecommendRate');

	Route::post('/logout', 'Auth\LoginController@logout');
});

Route::post('getCFData', 'RecommenderController@getCFData');
Route::post('getCBData', 'RecommenderController@getCBData');
// Route::get('predictCB', 'RecommenderController@predictCB');
// Route::get('predictCF', 'RecommenderController@predictCF');


Route::get('/hotels', 'HotelController@index');
Route::get('/checkConnect', function() {
	return response()->json([
		'checkOK' => 1,
	], 200);
});