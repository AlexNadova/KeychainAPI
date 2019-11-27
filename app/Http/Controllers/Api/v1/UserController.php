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
	 * @return \Illuminate\Http\Response 
	 */
	public function login()
	{
		if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
			$user = Auth::user();
			$success['token'] =  $user->createToken('MyApp')->accessToken;
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
	 * Display all users
	 *
	 * @return UserCollectionResource
	 */
	public function index(): UserCollectionResource
	{
		// $users = User::paginate(5); //how many rows per page
		// return UserResource::collection($users); //take created resource and return it as collection
		return new UserCollectionResource(User::paginate(10));
	}

	/**
	 * create new user
	 * 
	 * @param Request $request
	 * @return UserResource
	 */
	public function store(Request $request): UserResource
	{
		//validaation: these fields must be given
		$request->validate([
			'name' => 'required',
			'surname' => 'required',
			'email' => 'required',
			'password' => 'required', // password
		]);
		//create new user
		$user = user::create($request->all());
		//return that user
		return new UserResource($user);
	}

	/**
	 * @param User $user, Request $request
	 * @return UserResource
	 */
	public function update(User $user, Request $request): UserResource
	{
		//update user
		$user->update($request->all());
		return new UserResource($user);
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
