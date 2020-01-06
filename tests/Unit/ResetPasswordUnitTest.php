<?php

namespace Tests\Unit;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Testcases\ResetPasswordTestCase;
use App\Helpers\HttpStatus;
use App\PasswordReset;
use App\User;

class ResetPasswordUnitTests extends ResetPasswordTestCase
{
	public $route = 'http://127.0.0.1:8000/api/v1';

	public function createUser() {
		$userCheck = User::where([
			'email' => 'cauliflower@website.com'
		])->first();
		if(!$userCheck){
			$newUser = [
				'name' => 'Beneficent',
				'surname' => 'Cauliflower',
				'email' => 'cauliflower@website.com',
				'password' => bcrypt('SafePassword1'),
			];
			$user = User::create($newUser);
			$user->email_verified_at = now();
			$user->update();
			return $user;
		}
		return $userCheck;
	}

	public function createToken() {
		$passwordReset = PasswordReset::where([
			['email', 'cauliflower@website.com']])->first();
		if(!$passwordReset){
			$passwordReset = PasswordReset::updateOrCreate([
				'email' => 'cauliflower@website.com',
				'token' => Str::random(60)
			]);
		}
		return $passwordReset;
	}

//--------------------------------------CREATE TOKEN--------------------------------------
	
	/**
	 *  TRP1: test password reset; correct
	 * 	@return void
	 */
	public function testPasswordResetCreate(): void {
		$user = $this->createUser();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/create', [
			'email' => $user->email,
			'url' => 'https://website.com/reset-password?token=vyhsv156s4vbsz'
		]);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'message' => 'We have e-mailed you your password reset link!',
		]);
		$this->assertDatabaseHas('password_resets',[
			'email' => $user->email,
		]);
	}

	/**
	 *  TRP2: test password reset; wrong - email doesn't exist in DB
	 * 	@return void
	 */
	public function testPasswordResetCreateEmailNotFound(): void {
		$user = $this->createUser();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/create', [
			'email' => 'wrong.mail@website.com',
			'url' => 'https://website.com/reset-password?token=vyhsv156s4vbsz'
		]);
		$response->assertStatus(HttpStatus::STATUS_BAD_REQUEST);
		$response->assertJson([
			'error' => 'We cannot find a user with that e-mail address.',
		]);
		$this->assertDatabaseMissing('password_resets',[
			'email' => 'wrong.mail@website.com',
		]);
	}
	
	/**
	 *  TRP3: test password reset; wrong - values have wrong type
	 * 	@return void
	 */
	public function testPasswordResetCreateValuesWrongType(): void {
		$user = $this->createUser();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/create', [
			'email' => 1,
			'url' => false
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors' => [
				'email' => [
					'The email must be a string.',
       				'The email must be a valid email address.'
				],
				'url' => [
					'The url must be a string.',
					'The url format is invalid.'
			  	]
			]		
		]);
		$this->assertDatabaseMissing('password_resets',[
			'email' => 1
		]);
	}
	
	/**
	 *TRP4: test password reset; wrong - values not given
	 * 	@return void
	 */
	public function testPasswordResetCreateValuesNotGiven(): void {
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/create');
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors' => [
				'email' => [
					'The email field is required.'
				],
				'url' => [
					'The url field is required.'
			  	]
			]
		]);
	}

//--------------------------------------RESET PASSWORD--------------------------------------

	/**
	 *  TRP5: test password reset; correct
	 * 	@return void
	 */
	public function testPasswordReset(): void {
		$user = $this->createUser();
		$passwordReset = $this->createToken();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => 'NewPassword1',
			'c_password' => 'NewPassword1',
			'token' => $passwordReset->token]);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'message' => 'Your password has been reset.',
		]);
	}

	/**
	 * TRP6: test password reset; wrong - field not given
	 * 	@return void
	 */
	public function testPasswordResetFieldsNotGiven(): void {
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset');
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message'=> 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password field is required.'
				],
				'c_password' => [
					'The c password field is required.'
				],
				'token' => [
					'The token field is required.'
				]
			]
		]);
	}
		
	/**
	 *  TRP7: test password reset; wrong - fields wrong type
	 * 	@return void
	 */
	public function testPasswordResetFieldsWrongType(): void {
		$user = $this->createUser();
		$passwordReset = $this->createToken();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => ['a'],
			'c_password' => ['a'],
			'token' => false]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message'=> 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password must be a string.',
					'The password format is invalid.'
				],
				'token' => [
					'The token must be a string.'
				]
			]
		]);
	}
		
	/**
	 *  TRP8: test password reset; wrong - password and c_password don't match
	 * 	@return void
	 */
	public function testPasswordResetPasswordsDontMatch(): void {
		$user = $this->createUser();
		$passwordReset = $this->createToken();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => 'NewPassword1',
			'c_password' => 'NewPassword',
			'token' => $passwordReset->token]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message'=> 'The given data was invalid.',
			'errors'=> [
				'c_password' => [
					'The c password and password must match.',
				]
			]
		]);
	}
	
	/**
	 *  TRP9: test password reset; wrong - password wrong format (all lowercase)
	 * 	@return void
	 */
	public function testPasswordResetPasswordAllLowercase(): void {
		$user = $this->createUser();
		$passwordReset = $this->createToken();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => 'onlylowercase',
			'c_password' => 'onlylowercase',
			'token' => $passwordReset->token]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password format is invalid.',
				]
			]
		]);
	}
	
	/**
	 *  TRP10: test password reset; wrong - password wrong format (all uppercase)
	 * 	@return void
	 */
	public function testPasswordResetPasswordAllUppercase(): void {
		$user = $this->createUser();
		$passwordReset = $this->createToken();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => 'ONLYUPPERCASE',
			'c_password' => 'ONLYUPPERCASE',
			'token' => $passwordReset->token]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password format is invalid.',
				]
			]
		]);
	}

	/** 
	 *  TRP11: test password reset; wrong - password wrong format (all numbers - still string)
	 * 	@return void
	 */ 
	public function testPasswordResetPasswordAllNumbers(): void {
		$user = $this->createUser();
		$passwordReset = $this->createToken();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => '0123456789',
			'c_password' => '0123456789',
			'token' => $passwordReset->token]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password format is invalid.',
				]
			]
		]);
	}

	// TRP12: test password reset; wrong - token not found in DB
	public function testPasswordResetTokenNotFound(): void {
		$user = $this->createUser();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
			'token' => Str::random(60)]);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'error' => 'This password reset token is invalid.',
		]);
	}

	// TRP13: test password reset; wrong - token older than 12h
	public function testPasswordResetTokenTooOld(): void {
		$user = $this->createUser();
		$passwordReset = $this->createToken();
		$passwordReset->updated_at = '2019-12-17 21:04:00';
		$passwordReset->update();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
			'token' => $passwordReset->token]);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'error' => 'This password reset token is invalid.',
		]);
	}

	// TRP14: test password reset; wrong - user not found by email
	public function testPasswordResetUserNotFound(): void {
		$user = $this->createUser();
		$passwordReset = $this->createToken();
		$user->delete();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/password/reset', [
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
			'token' => $passwordReset->token]);
		$response->assertStatus(HttpStatus::STATUS_NOT_FOUND);
		$response->assertJson([
			'error' => 'We cannnot find a user with that e-mail address.',
		]);
	}
}