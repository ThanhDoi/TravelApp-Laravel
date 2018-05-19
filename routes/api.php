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
	Route::get('/users/visitedItems', 'UserController@getVisitedItems');

	Route::get('/getHotelRecommend', 'RecommenderController@getHotelRecommend');
	Route::get('/getAttractionRecommend', 'RecommenderController@getAttractionRecommend');

	Route::get('/hotels/{id}/avgRating', 'HotelController@avgRating');
	Route::post('/hotels/{id}/rate', 'HotelController@updateRate');
	Route::post('/hotels/{id}/rateRecommend', 'HotelController@updateRecommendRate');

	Route::get('/attractions/{id}/avgRating', 'AttractionController@avgRating');
	Route::post('/attractions/{id}/rate', 'AttractionController@updateRate');
	Route::post('/attractions/{id}/rateRecommend', 'AttractionController@updateRecommendRate');

	Route::post('/trips/createTripByHotel', 'TripController@createTripByHotel');
	Route::post('/trips/createTripByAttraction', 'TripController@createTripByAttraction');
	Route::get('/trips/{id}/getItemsInTrip', 'TripController@getItemsInTrip');
	Route::post('/trips/{id}/addHotel', 'TripController@addHotel');
	Route::post('/trips/{id}/addAttraction', 'TripController@addAttraction');
	Route::post('/trips/{id}/removeHotel', 'TripController@removeHotel');
	Route::post('/trips/{id}/removeAttraction', 'TripController@removeAttraction');
	Route::delete('/trips/{id}', 'TripController@delete');

	Route::post('/logout', 'Auth\LoginController@logout');
});

Route::post('getHotelCFData', 'RecommenderController@getHotelCFData');
Route::post('getHotelCBData', 'RecommenderController@getHotelCBData');
Route::post('getAttractionCBData', 'RecommenderController@getAttractionCBData');
Route::post('getAttractionCFData', 'RecommenderController@getAttractionCFData');

Route::get('/hotels', 'HotelController@index');
Route::get('/attractions', 'AttractionController@index');
Route::get('/checkConnect', function() {
	return response()->json([
		'checkOK' => 1,
	], 200);
});