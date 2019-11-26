<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollectionResource;

class UserController extends Controller
{
	/**
	 * Display one user.
	 * @param User $user
	 * @return UserResource
	 */
	public function show(User $user): UserResource
	{
		return new UserResource($user);
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
	public function destroy(User $user){
		$user->delete();

		return response()->json();
	}
}