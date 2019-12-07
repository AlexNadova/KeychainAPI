<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserResource;
use App\Helpers\HttpStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
	/** 
	 * login api 
	 * 
	 * @return \Illuminate\Http\JsonResponse 
	 */
	public function login(): \Illuminate\Http\JsonResponse
	{
		if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
			$user = Auth::user();
			$success['token'] =  $user->createToken('eddie')->accessToken;
			return response()->json(['success' => $success], HttpStatus::STATUS_OK);
		} else {
			return response()->json(['error' => 'Unauthorized'], HttpStatus::STATUS_UNAUTHORIZED);
		}
	}

	public function logout(): \Illuminate\Http\JsonResponse{
		$userToken = Auth::user()->token();
		$userToken->revoke();
		DB::table('oauth_access_tokens')->where([
			['user_id', Auth::user()['id']],
			['revoked',1],
		])->delete();
		return response()->json(['success' => 'User has been logged out.'], HttpStatus::STATUS_OK);
	}

	/** 
	 * Register api 
	 * 
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function register(Request $request): \Illuminate\Http\JsonResponse
	{
		$request->validate([
			'name' => 'required|regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'surname' => 'required|regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'email' => 'required|string|unique:users,email|email',
			'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})(?!.*[^a-zA-Z0-9]).{8,}/|string',
			'c_password' => 'required|same:password',
		]);
		$input = $request->all();
		//hash password with L Hash facade (authController takes care of veryfying Bcrypt 
		//password against the un-hashed version provided by user)
		$input['password'] = bcrypt($input['password']);
		//create user
		$user = User::create($input);
		//return user name and token
		return response()->json(['success' => 'User was created.'], HttpStatus::STATUS_CREATED);
	}

	/**
	 * Display one user.
	 * @param
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show(): \Illuminate\Http\JsonResponse
	{
		$authenticatedUser = Auth::user();
		if($authenticatedUser){
			$user = User::where('id', '=', $authenticatedUser['id'])->first();
			return response()->json(['data' => new UserResource($authenticatedUser)], HttpStatus::STATUS_OK);
		}else{
			return response()->json(['error' => 'Unauthorized'], HttpStatus::STATUS_UNAUTHORIZED);
		}
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(Request $request): \Illuminate\Http\JsonResponse
	{
		$request->validate([
			'name' => 'regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'surname' => 'regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'email' => 'string|email',
			'password' => 'regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})(?!.*[^a-zA-Z0-9]).{8,}/|string',
			'c_password' => 'same:password',
		]);
		$authenticatedUser = Auth::user();
		if($authenticatedUser){
			if (isset($request['email'])) {
				//get user from db that is not current user and has given email - if doesn't exist = null
				$user = User::where([
					['email', '=', $request["email"]],
					['id', '<>', $authenticatedUser['id']]
				])->first();
				//if user with given email exist, error
				if ($user !== null) {
					//if user doesn't exist, error
					return response()->json(['error' => 'This email is already in use.'], HttpStatus::STATUS_BAD_REQUEST);
				}
			}

			if (isset($request['password'])) {
				$request['password'] = bcrypt($request['password']);
			}

			$authenticatedUser->update($request->all());
			return response()->json([
				'message' => 'User was updated.',
				'data' => new UserResource($authenticatedUser)
			], HttpStatus::STATUS_OK);
		}else{
			return response()->json(['error' => 'Unauthorized'], HttpStatus::STATUS_UNAUTHORIZED);
		}
	}

	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy(): \Illuminate\Http\JsonResponse
	{
		$authenticatedUser = Auth::user();
		if($authenticatedUser){
			$authenticatedUser->delete();
			DB::table('oauth_access_tokens')->where('user_id', $authenticatedUser['id'])->delete();
			return response()->json(['success' => 'User was deleted successfully.'], HttpStatus::STATUS_OK);
		}else{
			return response()->json(['error' => 'Unauthorized'], HttpStatus::STATUS_UNAUTHORIZED);
		}
	}
}
