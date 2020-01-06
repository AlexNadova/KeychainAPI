<?php

namespace Tests\Unit;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Testcases\VerifyEmailTestCase;
use App\Helpers\HttpStatus;
use App\EmailVerification;
use App\User;

class VerifyEmailUnitTests extends VerifyEmailTestCase
{
	public $route = 'http://127.0.0.1:8000/api/v1';

	public function createUser($user = null) {
		if(is_null($user)){
			$user = [	
			'name' => 'Baseballbat',
			'surname' => 'Cottagecheese',
			'email' => 'cottagecheese@website.com'
			];
		}
		$userCheck = User::where([
			'email' => $user['email']
		])->first();
		if(!$userCheck){
			$newUser = [
				'name' => $user['name'],
				'surname' => $user['surname'],
				'email' => $user['email'],
				'password' => bcrypt('SafePassword1'),
			];
			$user = User::create($newUser);
			// $user->email_verified_at = now();
			// $user->update();
			return $user;
		}
		return $userCheck;
	}

	public function createToken($id, $email) {
		$emailVerification = EmailVerification::where([
			['email_update', $email]])->first();
		if(!$emailVerification){
			$emailVerification = EmailVerification::updateOrCreate([
				'user_id' => $id,
				'email_update' => $email,
				'token' => Str::random(60)
			]);
		}
		return $emailVerification;
	}

//--------------------------------------UPDATE EMAIL--------------------------------------

	/**
	 *  TVE1: test email verification; correct
	 *  @return void
	 */
	public function testEmailUpdate(): void {
		$user = $this->createUser();
		$newEmail = 'b.cottagecheese@website.sk';
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/email/update', [
			'email' => $user->email,
			'email_update' => $newEmail]);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'message' => 'We have e-mailed you your e-mail verification link!',
		]);
		$this->assertDatabaseHas('email_verification',[
			'user_id' => $user->id,
			'email_update' => $newEmail
		]);
	}
	/**
	 *  TVE2: test email verification; wrong - field empty
	 *  @return void
	 */
	public function testEmailUpdateFieldsEmpty(): void {
		$user = $this->createUser();
		$newEmail = 'cottagecheese@example.com';
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/email/update');
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors' => [
				'email' => [
					'The email field is required.'
				],
				'email_update' => [
					'The email update field is required.'
				]
			]
		]);
		$this->assertDatabaseMissing('email_verification',[
			'user_id' => $user->id,
			'email_update' => $newEmail
		]);
	}

	/**
	 *  TVE3: test email verification; wrong - fields wrong type (all should be string)
	 *  @return void
	 */
	public function testEmailUpdateFieldsWrongType(): void {
		$user = $this->createUser();
		$newEmail = true;
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/email/update', [
			'email' => 1,
			'email_update' => $newEmail]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors' => [
				'email' => [
					'The email must be a string.',
					'The email must be a valid email address.'
				],
				'email_update' => [
					'The email update must be a string.',
					'The email update must be a valid email address.'
				]
			]
		]);
		$this->assertDatabaseMissing('email_verification',[
			'user_id' => $user->id,
			'email_update' => $newEmail
		]);
	}

	/**
	 *  TVE4: test email verification; wrong - new email already in use
	 *  @return void
	 */
	public function testEmailUpdateNewEmailAlreadyInUse(): void {
		$user = $this->createUser();
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/email/update', [
			'email' => $user->email,
			'email_update' => $user->email]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors' => [
				'email_update' => [
					'The email update has already been taken.'
				]
			]
		]);
		$this->assertDatabaseMissing('email_verification',[
			'user_id' => $user->id,
			'email_update' => $user->email
		]);
	}

	/**
	 *  TVE5: test email verification; wrong - user with given email doesn't exist
	 *  @return void
	 */
	public function testEmailUpdateUserNotFound(): void {
		$user = $this->createUser();
		$email = 'incorrectmail@weird.website';
		$newEmail = 'newmail@otherwebsite.com';
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/email/update', [
			'email' => $email,
			'email_update' => $newEmail]);
		$response->assertStatus(HttpStatus::STATUS_BAD_REQUEST);
		$response->assertJson([
			'error' => 'We cannot find a user with that e-mail address.'
		]);
		$this->assertDatabaseMissing('users', [
			'email' => $email
		]);
		$this->assertDatabaseMissing('email_verification',[
			'user_id' => $user->id,
			'email_update' => $user->email
		]);
	}

	/**
	 *  TVE6: test email verification; wrong - access token not used
	 *  @return void
	 */
	public function testEmailUpdateAnauthenticated(): void {
		$user = $this->createUser();
		$newEmail = 'someothermail@website.net';
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/email/update', [
			'email' => $user->email,
			'email_update' => $newEmail]);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'message' => 'Unauthenticated.'
		]);
		$this->assertDatabaseMissing('email_verification',[
			'user_id' => $user->id,
			'email_update' => $newEmail
		]);
	}

//--------------------------------------VERIFY EMAIL--------------------------------------

	/**
	 *  TVE7: test email verification; correct 
	 *  @return void
	 */
	public function testEmailVerification(): void {
		$user = $this->createUser();
		$newEmail = 'cottagecheese.b@website.net';
		$token = $this->createToken($user->id, $newEmail);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/email/verify/'.$token->token);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'message' => 'Your e-mail has been verified. Proceed to login.'
		]);
		$this->assertDatabaseHas('users',[
			'id' => $user->id,
			'email' => $newEmail
		]);
	}

	/**
	 *  TVE8: test email verification; wrong - email verification token not given
	 *  @return void
	 */
	public function testEmailVerificationTokenNotGiven(): void {
		$user = $this->createUser();
		$newEmail = 'cottagecheese@somewebsite.net';
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/email/verify/');
		$response->assertStatus(HttpStatus::STATUS_NOT_FOUND);
		$this->assertDatabaseMissing('users',[
			'id' => $user->id,
			'email' => $newEmail
		]);
	}

	/**
	 *  TVE10: test email verification; wrong - token not found in DB
	 *  @return void
	 */
	public function testEmailVerificationTokenNotFound(): void {
		$user = $this->createUser();
		$newEmail = 'xXxcottagecheesexXx@website.net';
		$token = Str::random(60);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/email/verify/'.$token);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'error' => 'This e-mail verification token is invalid.'
		]);
		$this->assertDatabaseMissing('users', [
			'id' => $user->id,
			'email' => $newEmail
		]);
	}

	/**
	 *  TVE11: test email verification; wrong - token older than 12h
	 *  @return void
	 */
	public function testEmailVerificationTokenTooOld(): void {
		$user = $this->createUser();
		$newEmail = 'too.tired.to@make.up.names';
		$token = $this->createToken($user->id, $newEmail);
		$token->created_at = '2019-12-18 17:14:00';
		$token->update();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/email/verify/'.$token->token);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'error' => 'This e-mail verification token is invalid.'
		]);
		$this->assertDatabaseMissing('users', [
			'id' => $user->id,
			'email' => $newEmail
		]);
	}

	/**
	 *  TVE12: test email verification; wrong - user doesn't exist anymore (was deleted)
	 *  @return void
	 */
	public function testEmailVerificationUserNotFound(): void {
		$user = $this->createUser();
		$newEmail = 'omg@website.net';
		$token = $this->createToken($user->id, $newEmail);
		$user->delete();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/email/verify/'.$token->token);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'error' => 'This e-mail verification token is invalid.'
		]);
		$this->assertDatabaseMissing('users', [
			'id' => $user->id,
			'email' => $newEmail
		]);
	}

	/**
	 *  TVE13: test email verification; wrong - new email already in use
	 *  @return void
	 */
	public function testEmailVerificationNewEmailAlreadyInUse(): void {
		$existingUser = $this->createUser([
			'name' => 'Cogglesnatch',
			'surname' => 'Cottagecheese',
			'email' => 'email@website.com']);
		$user = $this->createUser();
		$newEmail = 'last.one@website.com';
		$token = $this->createToken($user->id, $newEmail);
		$existingUser->email = 'last.one@website.com';
		$existingUser->update();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/email/verify/'.$token->token);
		$response->assertStatus(HttpStatus::STATUS_CONFLICT);
		$response->assertJson([
			'error' => 'This email is already in use.'
		]);
		$this->assertDatabaseHas('users', [
			'id' => $existingUser->id,
			'email' => $existingUser->email,
			'name' => $existingUser->name
		]);
		$this->assertDatabaseMissing('users', [
			'id' => $user->id,
			'email' => $newEmail
		]);
	}
}