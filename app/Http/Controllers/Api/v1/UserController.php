<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
	const STATUS_OK = 200;
	const STATUS_CREATED = 201;
	const STATUS_BAD_REQUEST = 400;
	const STATUS_UNAUTHORIZED = 401;

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
			return response()->json(['success' => $success], self::STATUS_OK);
		} else {
			return response()->json(['error' => 'Unauthorised'], self::STATUS_UNAUTHORIZED);
		}
	}

	public function logout(): \Illuminate\Http\JsonResponse{
		$userToken = Auth::user()->token();
		$userToken->revoke();
		DB::table('oauth_access_tokens')->where([
			['user_id', Auth::user()['id']],
			['revoked',1],
		])->delete();
		return response()->json(['success' => 'User has been logged out.'], self::STATUS_OK);
	}

	/** 
	 * Register api 
	 * 
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function register(Request $request): \Illuminate\Http\JsonResponse
	{
		//validation: these fields must be given
		$validator = Validator::make($request->all(), [
			'name' => 'required|regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'surname' => 'required|regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'email' => 'required|string|unique:users,email|email',
			'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})/|string',
			'c_password' => 'required|same:password',
		]);
		//if validation fails, send error response
		if ($validator->fails()) {
			return response()->json(['error' => $validator->errors()], self::STATUS_BAD_REQUEST);
		}
		$input = $request->all();
		//hash password with L Hash facade (authController takes care of veryfying Bcrypt 
		//password against the un-hashed version provided by user)
		$input['password'] = bcrypt($input['password']);
		//create user
		$user = User::create($input);
		//create token named eddie for this user
		$success['token'] =  $user->createToken('eddie')->accessToken;
		$success['name'] =  $user->name;
		//return user name and token
		return response()->json(['success' => $success], self::STATUS_CREATED);
	}

	/**
	 * Display one user.
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show(int $id): \Illuminate\Http\JsonResponse
	{
		$authenticatedUser = Auth::user();
		//get user by id taken from route (../api/v1/user/2)
		$user = User::where('id', '=', $id)->first();
		//if user doesn't exist, error
		if ($user === null) {
			return response()->json(['error' => 'User with this ID does not exist.'], self::STATUS_BAD_REQUEST);
		} elseif ($authenticatedUser['id'] === $user['id']) {
			return response()->json(['data' => new UserResource($authenticatedUser)], self::STATUS_OK);
			//if id of current authenticated user and id taken from route (../api/v1/user/2) don't match, error
		} else {
			return response()->json(['error' => 'Given ID does not match with logged user.'], self::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @param int $id, Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(int $id, Request $request): \Illuminate\Http\JsonResponse
	{
		//validation: these fields must be given
		$validator = Validator::make($request->all(), [
			'name' => 'regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'surname' => 'regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'email' => 'string|email',
			'password' => 'regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})/|string',
			'c_password' => 'same:password',
		]);
		//if validation fails, send error response
		if ($validator->fails()) {
			return response()->json(['error' => $validator->errors()], self::STATUS_BAD_REQUEST);
		}
		$authenticatedUser = Auth::user();

		if (isset($request['email'])) {
			//get user from db that is not current user and has given email - if doesn't exist = null
			$checkEmail = User::where([
				['email', '=', $request["email"]],
				['id', '<>', $authenticatedUser['id']]
			])->first();
			//if user with given email exist, error
			if ($checkEmail !== null) {
				//if user doesn't exist, error
				return response()->json(['error' => 'This email is already in use.'], self::STATUS_BAD_REQUEST);
			}
		}

		if (User::where('id', '=', $id)->first() === null) {
			return response()->json(['error' => 'User with this ID does not exist.'], self::STATUS_BAD_REQUEST);
		} elseif ($authenticatedUser['id'] === $id) {
			if (isset($request['password'])) {
				$request['password'] = bcrypt($request['password']);
			}
			$authenticatedUser->update($request->all());
			return response()->json(['data' => new UserResource($authenticatedUser)], self::STATUS_OK);
		} else {
			return response()->json(['error' => 'Given ID does not match with logged user.'], self::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy(int $id): \Illuminate\Http\JsonResponse
	{
		$authenticatedUser = Auth::user();

		//if user doesn't exist, error
		if (User::where('id', '=', $id)->first() === null) {
			return response()->json(['error' => 'User with this ID does not exist.'], self::STATUS_BAD_REQUEST);
		} elseif ($authenticatedUser['id'] === $id) {
			$authenticatedUser->delete();
			DB::table('oath_access_tokens')->where('user_id', $id)->delete();
			return response()->json(['success' => 'User was deleted successfully.'], self::STATUS_OK);
		} else {
			return response()->json(['error' => 'Given ID does not match with logged user.'], self::STATUS_BAD_REQUEST);
		}
	}
}
