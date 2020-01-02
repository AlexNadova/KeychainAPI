<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\EmailVerification;
use App\Helpers\HttpStatus;
use App\Notifications\EmailVerificationRequest;
use App\Notifications\EmailVerificationSuccess;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    /**
     * Where to redirect users after verification.
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    { }
	
	/**
     * update email
     * @param  Request $request (string : email, email_update)
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
		$request->validate([
			'email' => 'required|string|email',
			'email_update' => 'required|string|email|unique:users,email',
		]);
		//get user by email
		$user = User::where('email', $request->email)->first();
		//if user doean't exist, return error
		if (!$user) {
			return response()->json(['error' => 'We cannot find a user with that e-mail address.'], HttpStatus::STATUS_BAD_REQUEST);
		}
		$emailVerifyCheck = EmailVerification::where('user_id',$user['id'])->first();
		if($emailVerifyCheck){
			$emailVerifyCheck->delete();
		}
		//create emailVerification with random token
		$emailVerify = EmailVerification::create([
			'user_id' => $user['id'],
			'token' => Str::random(60),
			'email_update' => $request->email_update
			]);
		//if user and passwordReset exits, send email (notify) and return message
		if ($user && $emailVerify){
			$user['email'] = $request->email_update;
			$user->notify(new EmailVerificationRequest($emailVerify->token));
			return response()->json(['message' => 'We have e-mailed you your e-mail verification link!'], HttpStatus::STATUS_OK);
		}
	}

	/**
     * Verify email
     * @param  
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(string $token): \Illuminate\Http\JsonResponse
    {
		$emailVerify = EmailVerification::where([
			['token', $token]
		])->first();
		if (!$emailVerify) {
			return response()->json(['error' => 'This e-mail verification token is invalid.'], HttpStatus::STATUS_UNAUTHORIZED);
		}
		//find out if token is older than 12h -> invalid
		if (Carbon::parse($emailVerify->created_at)->addMinutes(720)->isPast()) {
            $emailVerify->delete();
            return response()->json(['error' => 'This e-mail verification token is invalid.'], HttpStatus::STATUS_UNAUTHORIZED);
		}
		//find user by id
		$user = User::where('id', $emailVerify->user_id)->first();
		if (!$user) {
			return response()->json(['error' => 'We cannnot find a user with that ID.'], HttpStatus::STATUS_BAD_REQUEST);
		}
		//if user with given new email exist, error
		$userCheck = User::where([
			['email', '=', $emailVerify->email_update],
			['id', '<>', $user->id]
		])->first();
		if ($userCheck) {
			return response()->json(['error' => 'This email is already in use.'], HttpStatus::STATUS_CONFLICT);
		}
		$user->email = $emailVerify->email_update;
		$user->email_verified_at = new DateTime();
		$user->save();
		$emailVerify->delete();
		DB::table('oauth_access_tokens')->where('user_id', $user['id'])->delete();
		$user->notify(new EmailVerificationSuccess($emailVerify));
		return response()->json(['message' => 'Your e-mail has been verified. Proceed to login.'], HttpStatus::STATUS_OK);
	}
}
