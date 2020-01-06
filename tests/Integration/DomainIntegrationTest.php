<?php

namespace Tests\Integration;

use Laravel\Passport\Passport;
use Tests\Testcases\DomainTestCase;
use App\Helpers\HttpStatus;
use App\Login;
use App\User;

class LoginsUnitTests extends DomainTestCase
{
	public $route = 'http://127.0.0.1:8000/api/v1/logins';
	
	public function createUser($user = null) {
		if(is_null($user)){
			$user = [
				'name' => 'Butawhite',
				'surname' => 'Cantbekhan',
				'email' => 'cantbekhan@website.com',
				'password' => bcrypt('SafePassword1')
			];
		}
		$userCheck = User::where([
			'email' => $user['email']
		])->first();
		if(!$userCheck){
			$user = User::create($user);
			$user->email_verified_at = now();
			$user->update();
			return $user;
		}
		return $userCheck;
	}

	/**
	 *  TID1: test logins - store; correct: domain added automatically from website_address
	 *  @return void
	 */
	public function testLoginsStoreDomain(): void {
		$user = $this->createUser();
		$login = [
			'website_name' => 'InterestingWebsite',
			'website_address' => 'https://interestingWebsite.com/',
			'username' => 'Buckminster',
			'password' => 'password'
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route, $login);
		$response->assertStatus(HttpStatus::STATUS_CREATED);
		$response->assertJson(['success' => 'Login was created.']);
		$this->assertDatabaseHas('logins',[
			'website_address' => 'https://interestingWebsite.com/',
			'domain' => 'interestingWebsite.com'
		]);
	}

	/**
	 *  TID2: test logins - update; correct: if address is updated, domain is updated
	 *  @return void
	 */
	public function testLoginsUpdateDomain(): void {
		$user = $this->createUser();
		$login = [
			'user_id' => $user->id,
			'website_name' => 'Gmail',
			'website_address' => 'https://gmail.com',
			'domain' => 'gmail.com',
			'username' => 'cantbekhan@gmail.com',
			'password' => 'password'
		];
		$login = Login::create($login);
		$updatedLogin = [
			'website_address' => 'https://madeUpWebsite.com/',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/'.$login->id, $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$this->assertDatabaseHas('logins', [
			'website_address' => 'https://madeUpWebsite.com/',
			'domain' => 'madeUpWebsite.com'
		]);
	}
}