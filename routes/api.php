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
Route::prefix('v1')->group(function(){
	//all routes for REST API
	Route::apiResource('/user','Api\v1\UserController');
});

Route::prefix('v2')->group(function(){
	//all routes for REST API
	Route::apiResource('/user','Api\v2\UserController')->only('show');
});