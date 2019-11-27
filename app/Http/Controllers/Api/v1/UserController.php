<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
	public $successStatus = 200;

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
			return response()->json(['success' => $success], $this->successStatus);
		} else {
			return response()->json(['error' => 'Unauthorised'], 401);
		}
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
			return response()->json(['error' => $validator->errors()], 401);
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
		return response()->json(['success' => $success], $this->successStatus);
	}


	/**
	 * Display one user.
	 * @param User $user
	 * @return \Illuminate\Http\Response
	 */
	public function show(User $user)
	{
		$user = Auth::user();
		return response()->json(['data' => new UserResource($user)], $this->successStatus);
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
			return response()->json(['error' => $validator->errors()], 401);
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
				return response()->json(['error' => 'This email is already in use.'], 400);
			}
		}

		if (User::where('id', '=', $id)->first() === null) {
			return response()->json(['error' => 'User with this ID does not exist.'], 400);
		} elseif ($authenticatedUser['id'] === $id) {
			if (isset($request['password'])) {
				$request['password'] = bcrypt($request['password']);
			}
			$authenticatedUser->update($request->all());
			return response()->json(['data' => new UserResource($authenticatedUser)], $this->successStatus);
		} else {
			return response()->json(['error' => 'Given ID does not match with logged user.'], 400);
		}
	}

	/**
	 * @param User $user
	 * @return \Illuminate\Http\JsonResource
	 * @throws \Exception
	 */
	public function destroy(User $user)
	{
		$user->delete();

		return response()->json();
	}
}
