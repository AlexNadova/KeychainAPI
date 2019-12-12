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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

//all routes start wit v1 now (versioning)
Route::prefix('v1')->group(function () {
	//we don't add login & register to group (auth) because here we're just getting the token, not using it
	Route::post('login', 'Api\v1\UserController@login');
	Route::post('register', 'Api\v1\UserController@register');
	//password reset:
	Route::group([
		'namespace' => 'Auth',
		'middleware' => 'api',
		'prefix' => 'password'
	], function(){
		Route::post('create','PasswordResetController@create');
		// Route::get('find/{token}','PasswordResetController@find');
		Route::post('reset','PasswordResetController@reset');
	});
	//!!! email verification: probably will need remake after frontend is implemented
	Route::post('email/verify','Auth\VerificationController@verify');

	//use authentication middleware to protect these routes
	Route::group(['middleware' => 'auth:api'], function () {
		// Route::post('details', 'Api\v1\UserController@details');
		//all routes for REST API, exclude create because we have register for that
		Route::get('user','Api\v1\UserController@show');
		Route::put('user','Api\v1\UserController@update');
		Route::delete('user','Api\v1\UserController@destroy');
		Route::delete('logout','Api\v1\UserController@logout');
		Route::apiResource('logins', 'Api\v1\LoginController');
		Route::post('email/update','Auth\VerificationController@update');
	});
});

Route::prefix('v2')->group(function () {
	//all routes for REST API
	Route::apiResource('/user', 'Api\v2\UserController')->only('show');
});