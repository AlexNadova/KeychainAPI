<?php

namespace App\Http\Controllers\Auth;use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\Helpers\HttpStatus;
use App\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Create token password reset
     * @param  Request $request (string : $email)
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
		//validate given email
        $request->validate([
            'email' => 'required|string|email',
		]);
		//get user by that email
		$user = User::where('email', $request->email)->first();
		//if user doean't exist, return error
		if (!$user) {
			return response()->json(['error' => 'We cannot find a user with that e-mail address.'], HttpStatus::STATUS_BAD_REQUEST);
		}
		//create passwordreset with random token
		$passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email,
            'token' => Str::random(60)]
		);
		//if user and passwordReset exits, send email (notify) and return message
		if ($user && $passwordReset){
			$user->notify(new PasswordResetRequest($passwordReset->token));
			return response()->json(['message' => 'We have e-mailed you your password reset link!'], HttpStatus::STATUS_OK);
		}
	}
	
	/**
     * Reset password
     * @param  Request $request (string: email, password, c_password, token)
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
			'password' => 'required|string',
			'c_password' => 'required|same:password',
            'token' => 'required|string'
		]);
		$passwordReset = PasswordReset::where([
            ['token', $request->token],
			['email', $request->email]])->first();
		if (!$passwordReset) {
			return response()->json(['error' => 'This password reset token is invalid.'], HttpStatus::STATUS_UNAUTHORIZED);
		}
		//find out if token is older than 12h -> invalid
		if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json(['error' => 'This password reset token is invalid.'], HttpStatus::STATUS_UNAUTHORIZED);
		}
		//find user by email
		$user = User::where('email', $passwordReset->email)->first();        
		if (!$user) {
			return response()->json(['error' => 'We cannnot find a user with that e-mail address.'], HttpStatus::STATUS_BAD_REQUEST);
		}
		$user->password = bcrypt($request->password);
		$user->save();
		$passwordReset->delete();
		$user->notify(new PasswordResetSuccess($passwordReset));
		return response()->json(['message' => 'Your password has been reset.'], HttpStatus::STATUS_OK);
	}
		
	// /**
    //  * Find token password reset
    //  *
    //  * @param  [string] $token
    //  * @return [string] message
    //  * @return [json] passwordReset object
    //  */
    // public function find($token)
    // {
	// 	$passwordReset = PasswordReset::where('token', $token)->first();
	// 	if (!$passwordReset) return response()->json(['message' => 'This password reset token is invalid.'], 404);
	// 	if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
    //         $passwordReset->delete();
    //         return response()->json(['message' => 'This password reset token is invalid.'], 404);
	// 	}
	// 	return response()->json($passwordReset);
	// }
}
