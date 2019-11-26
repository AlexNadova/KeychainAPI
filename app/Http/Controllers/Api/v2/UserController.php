<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Http\Resources\v2\UserResource;
use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
	public function show(User $user): UserResource
	{
		return new UserResource($user);
	 }
}
