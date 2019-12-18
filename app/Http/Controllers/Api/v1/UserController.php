<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\User;
use App\EmailVerification;
use App\Http\Resources\UserResource;
use App\Helpers\HttpStatus;
use App\Notifications\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
	/** 
	 * login api 
	 * @return \Illuminate\Http\JsonResponse 
	 */
	public function login(): \Illuminate\Http\JsonResponse
	{
		$user = User::where([
			['email', '=', request('email')]
		])->first();
		if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
			if (is_null($user['email_verified_at'])){
				return response()->json(['error' => 'You first need to verify your email.'], HttpStatus::STATUS_UNAUTHORIZED);
			}
			$user = Auth::user();
			$success['token'] =  $user->createToken($user->email)->accessToken;
			return response()->json(['success' => $success], HttpStatus::STATUS_OK);
		} else {
			return response()->json(['error' => 'User could not be authenticated.'], HttpStatus::STATUS_UNAUTHORIZED);
		}
	}

	/** 
	 * logout api 
	 * @return \Illuminate\Http\JsonResponse 
	 */
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
	 * @param Request $request (string: name, surname, email, password, c_password)
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function register(Request $request): \Illuminate\Http\JsonResponse
	{
		$request->validate([
			'name' => 'required|regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'surname' => 'required|regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'email' => 'required|string|unique:users,email|email',
			'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})/|string',
			'c_password' => 'required|same:password',
		]);
		$input = $request->only('name', 'surname', 'email', 'password');
		//hash password with L Hash facade (authController takes care of veryfying Bcrypt 
		//password against the un-hashed version provided by user)
		$input['password'] = bcrypt($input['password']);
		//create user
		$user = User::create($input);
		$emailVerify = EmailVerification::updateOrCreate([
			'user_id' => $user['id'],
			'token' => Str::random(60),
			'email_update' => $user['email']
		]);
		if ($user && $emailVerify){
			$user->notify(new EmailVerificationRequest($emailVerify->token));
			//return user name and token
			return response()->json(['success' => 'User was created.'], HttpStatus::STATUS_CREATED);
		}else{
			return response()->json(['error' => 'User could not be registered.'], HttpStatus::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * Display one user.
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show(): \Illuminate\Http\JsonResponse
	{
		$authenticatedUser = Auth::user();
		if($authenticatedUser){
			return response()->json(['data' => new UserResource($authenticatedUser),], HttpStatus::STATUS_OK);
		}else{
			return response()->json(['error' => 'You cannot access this resource.'], HttpStatus::STATUS_UNAUTHORIZED);
		}
	}

	/**
	 * @param Request $request (string: name|surname|password|c_password)
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(Request $request): \Illuminate\Http\JsonResponse
	{
		$request->validate([
			'name' => 'regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'surname' => 'regex:/^[a-zA-Zá-žÁ-Ž]{2,17}$/|string',
			'password' => 'regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})/|string',
			'c_password' => 'same:password|required_with:password',
		]);
		$authenticatedUser = Auth::user();
		if($authenticatedUser){
			//if password is given, hash it
			if (isset($request['password'])) {
				$request['password'] = bcrypt($request['password']);
			}
			//update user
			$authenticatedUser->update($request->only('name', 'surname', 'password'));
			return response()->json([
				'message' => 'User was updated.',
				'data' => new UserResource($authenticatedUser)
			], HttpStatus::STATUS_OK);
		}else{
			return response()->json(['error' => 'You cannot access this resource.'], HttpStatus::STATUS_FORBIDDEN);
		}
	}

	/**
	 * delete user
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
			return response()->json(['error' => 'You cannot access this resource.'], HttpStatus::STATUS_UNAUTHORIZED);
		}
	}
}
