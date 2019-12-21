<?php

namespace Tests\Unit;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Tests\Testcases\LoginsTestCase;
use App\Helpers\HttpStatus;
use App\Login;
use App\User;

class LoginsUnitTests extends LoginsTestCase{
	public $route = 'http://127.0.0.1:8000/api/v1/logins';

	public function createUser($user = null) {
		if(is_null($user)){
			$user = [
				'name' => 'Buckminster',
				'surname' => 'Wafflesmack',
				'email' => 'wafflesmack@website.com',
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

	public function createLogin($id, $login = null) {
		if(is_null($login)){
			$login = [
				'user_id' => $id,
				'website_name' => 'Gmail',
				'website_address' => 'https://gmail.com',
				'username' => 'buckminster@gmail.com',
				'password' => 'password'
			];
		}
		$loginCheck = Login::where([
			'user_id' => $id,
			'website_name' => $login['website_name'],
			'website_address' => $login['website_address'],
			'username' => $login['username'],
			'password' => $login['password']
		])->first();
		if(!$loginCheck){
			$login = Login::create($login);
			return $login;
		}
		return $loginCheck;
	}

	public function checkIfLoginExists($id, $login, $testName, $expectedStatus){
		$loginCheck = Login::where([
			'user_id' => $id,
			'website_name' => $login['website_name'],
			'website_address' => $login['website_address']
		])->first();
		if($loginCheck){
			if(($loginCheck->user_id === $id) 
				&& ($loginCheck->website_name === $login['website_name']) 
				&& ($loginCheck->website_address === $login['website_address'])
				&& ($loginCheck->username === $login['username'])){
					$realStatus = 1; //login exists
			}else{
				$realStatus = 2; //some fields are wrong
			}
		}else{
			$realStatus = 3; //login doesn't exist
		}
		if($expectedStatus === $realStatus){
			dump($testName.' - OK');
		}else{
			dump($testName.' - NOT OK');
		}
	}

//-----------------------------------------------CREATE LOGIN-----------------------------------------------

	/**
	 *  TLS1: test logins - store; correct
	 *  @return void
	 */
	public function testLoginsStore(): void {
		$user = $this->createUser();
		$login = [
			'website_name' => 'facebook',
			'website_address' => 'https://facebook.com',
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
		$this->checkIfLoginExists($user->id, $login, 'testLoginsStore', 1);
	}

	
	/**
	 *  TLS2: test logins - store; correct but extra fields given which should not be accepted 
	 *  @return void
	 */
	public function testLoginsStoreExtraFields(): void {
		$user = $this->createUser();
		$login = [
			'user_id' => '5',
			'website_name' => 'Twitter',
			'website_address' => 'https://twitter.com',
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
		$this->checkIfLoginExists($user->id, $login, 'testLoginsStoreExtraFields', 1);
	}

	/**
	 *  TLS3: test logins - store; wrong - fields not given
	 *  @return void
	 */
	public function testLoginsStoreFieldsNotGiven(): void {
		$user = $this->createUser();
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'website_name' => [
					'The website name field is required.'
				],
				'website_address' => [
					'The website address field is required.'
				],
				'username' => [
					'The username field is required.'
				],
				'password' => [
					'The password field is required.'
				]
			]
		]);
	}

	/**
	 *  TLS4: test logins - store; wrong - fields are of wrong type (all should be string)
	 *  @return void
	 */
	public function testLoginsStoreFieldsWrongType(): void {
		$user = $this->createUser();
		$login = [
			'website_name' => false,
			'website_address' => '',
			'username' => [5,8],
			'password' => 0
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route, $login);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'website_name' => [
					'The website name must be a string.'
				],
				'website_address' => [
					'The website address field is required.'
				],
				'username' => [
					'The username must be a string.'
				],
				'password' => [
					'The password must be a string.'
				]
			]
		]);
		$this->checkIfLoginExists($user->id, $login, 'testLoginsStoreFieldsWrongType', 3);
	}

	/**
	 *  TLS5: test logins - store; wrong - web address is not valid URL address
	 *  @return void
	 */
	public function testLoginsStoreWebAddressNotUrl(): void {
		$user = $this->createUser();
		$login = [
			'website_name' => 'Instagram',
			'website_address' => 'instagram',
			'username' => 'bucky',
			'password' => 'password'
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route, $login);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'website_address' => [
					'The website address format is invalid.'
				]
			]
		]);
		$this->checkIfLoginExists($user->id, $login, 'testLoginsStoreWebAddressNotUrl',3);
	}

	/**
	 *  TLS6: test logins - store; wrong - values too long
	 *  @return void
	 */
	public function testLoginsStoreValuesTooLong(): void {
		$user = $this->createUser();
		$login = [
			'website_name' => Str::random(31),
			'website_address' => Str::random(256),
			'username' => Str::random(256),
			'password' => Str::random(256)
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route, $login);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				"website_name" => [
					"The website name may not be greater than 30 characters."
				],
				"website_address" => [
					"The website address may not be greater than 255 characters.",
					"The website address format is invalid."
				],
				"username" => [
					"The username may not be greater than 255 characters."
				],
				"password" => [
					"The password may not be greater than 255 characters."
				]
			]
		]);
		$this->checkIfLoginExists($user->id, $login, 'testLoginsStoreValuesTooLong',3);
	}

	/**
	 *  TLS7: test logins - store; wrong - access token not given
	 *  @return void
	 */
	public function testLoginsStoreUnauthenticated(): void {
		$login = [
			'website_name' => '9Gag',
			'website_address' => 'https://9gag.com',
			'username' => 'funnyName',
			'password' => 'password'
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route, $login);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'message' => 'Unauthenticated.',
		]);
	}

//-----------------------------------------------GET LOGIN-----------------------------------------------

	/**
	 *  TLG1: test logins - show; correct
	 *  @return void
	 */ 
	public function testLoginsGet(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$loginData = [
			'website_name' => $login->website_name,
			'website_address' => $login->website_address,
			'username' => $login->username,
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/'.$login->id);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$this->checkIfLoginExists($user->id, $loginData, 'testLoginsGet', 1);
	}

	/**
	 *  TLG2: test logins - show; wrong - requested login doesn't exist
	 *  @return void
	 */
	public function testLoginsGetLoginNotFound(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$loginData = [
			'website_name' => $login->website_name,
			'website_address' => $login->website_address,
			'username' => $login->username,
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/-5');
		$response->assertStatus(HttpStatus::STATUS_BAD_REQUEST);
		$response->assertJson(['error' => 'Resource does not exist.']);
		$this->assertDatabaseMissing('logins',[
			'id' => -5
		]);
	}

	/**
	 *  TLG3: test logins - show; wrong - access token not given
	 *  @return void
	 */
	public function testLoginsGetUnauthorized(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/'.$login->id);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson(['message' => 'Unauthenticated.']);
	}

	/**
	 *  TLG4: test logins - show; wrong - user doesn't own requested login
	 *  @return void
	 */
	public function testLoginsGetUserCannotAccessLogin(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$newUser = $this->createUser([
			'name' => 'Blubberwhale',
			'surname' => 'Candlestick',
			'email' => 'candlestick@website.com',
			'password' => bcrypt('SafePassword1')
		]);
		$loginData = [
			'website_name' => $login->website_name,
			'website_address' => $login->website_address,
			'username' => $login->username,
		];
		Passport::actingAs($newUser);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/'.$login->id);
		$response->assertStatus(HttpStatus::STATUS_FORBIDDEN);
		$response->assertJson(['error' => 'You cannot access this resource.']);
	}

//-----------------------------------------------GET ALL LOGIN-----------------------------------------------

	/**
	 *  TLG5: test logins - index; correct
	 *  @return void
	 */
	public function testLoginsGetAll(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$secondLogin = $this->createLogin($user->id);
		$loginData = [
			'website_name' => $login->website_name,
			'website_address' => $login->website_address,
			'username' => $login->username,
		];
		$secondLoginData = [
			'website_name' => $secondLogin->website_name,
			'website_address' => $secondLogin->website_address,
			'username' => $secondLogin->username,
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$this->checkIfLoginExists($user->id, $loginData, 'testLoginsGetAll1', 1);
		$this->checkIfLoginExists($user->id, $secondLoginData, 'testLoginsGetAll2', 1);
	}

	/**
	 *  TLG6: test logins - index; wrong - access token not given
	 *  @return void
	 */
	public function testLoginsGetAllUnauthorized(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$secondLogin = $this->createLogin($user->id);
		$loginData = [
			'website_name' => $login->website_name,
			'website_address' => $login->website_address,
			'username' => $login->username,
		];
		$secondLoginData = [
			'website_name' => $secondLogin->website_name,
			'website_address' => $secondLogin->website_address,
			'username' => $secondLogin->username,
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson(['message' => 'Unauthenticated.']);
	}

	/**
	 *  TLG7: test logins - index; wrong - user doesn't have any logins
	 *  @return void
	 */
	public function testLoginsGetAllNoLoginsOwned(): void {
		$user = $this->createUser([
			'name' => 'Rumblesack',
			'surname' => 'Crackerjack',
			'email' => 'crackerjack@website.com',
			'password' => bcrypt('SafePassword1')
		]);
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route);
		$response->assertStatus(HttpStatus::STATUS_BAD_REQUEST);
		$response->assertJson(['error' => 'User does not own any logins.']);
		$this->assertDatabaseMissing('logins',[
			'user_id' => $user->id
		]);
	}

//-----------------------------------------------UPDATE LOGIN-----------------------------------------------

	/**
	 *  TLU1: test logins - update; correct 
	 *  @return void
	 */
	public function testLoginsUpdate(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$updatedLogin = [
			'website_name' => 'UpdatedName',
			'website_address' => 'https://updated.gmail.com',
			'username' => 'updatedUsername',
			'password' => 'updatedPassword',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/'.$login->id, $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$this->checkIfLoginExists($user->id, $updatedLogin, 'testLoginsUpdate', 1);
	}

	/**
	 *  TLU2: test logins - update; wrong - extra fields given
	 *  @return void
	 */
	public function testLoginsUpdateExtraFields(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$updatedLogin = [
			'user_id' => 50,
			'website_name' => 'NewName',
			'website_address' => 'https://new.gmail.com',
			'username' => 'newUsername',
			'password' => 'newPassword',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/'.$login->id, $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$this->checkIfLoginExists(50, $updatedLogin, 'testLoginsUpdateExtraFields', 3);
	}

	/**
	 *  TLU3: test logins - update; wrong - access token not given
	 *  @return void
	 */
	public function testLoginsUpdateUnauthenticated(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$updatedLogin = [
			'website_name' => 'AnotherName',
			'website_address' => 'https://another.gmail.com',
			'username' => 'anotherUsername',
			'password' => 'anotherPassword',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/'.$login->id, $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson(['message' => 'Unauthenticated.']);
		$this->checkIfLoginExists($user->id, $updatedLogin, 'testLoginsUpdateUnauthenticated', 3);
	}

	/**
	 *  TLU4: test logins - update; wrong - requested login doesn't exist
	 *  @return void
	 */
	public function testLoginsUpdateLoginNotFound(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$updatedLogin = [
			'website_name' => 'SomeName',
			'website_address' => 'https://some.gmail.com',
			'username' => 'someUsername',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/-5', $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_BAD_REQUEST);
		$response->assertJson(['error' => 'Resource does not exist.']);
		$this->assertDatabaseMissing('logins',[
			'id' => -5
		]);
	}

	/**
	 *  TLU5: test logins - update; wrong - user doesn't own requested login
	 *  @return void
	 */
	public function testLoginsUpdateUserCannotAccessLogin(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$newUser = $this->createUser([
			'name' => 'Blubberwhale',
			'surname' => 'Candlestick',
			'email' => 'candlestick@website.com',
			'password' => bcrypt('SafePassword1')
		]);
		$updatedLogin = [
			'website_name' => 'ThatName',
			'website_address' => 'https://that.gmail.com',
			'username' => 'thatUsername',
		];
		Passport::actingAs($newUser);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->getJson($this->route.'/'.$login->id, $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_FORBIDDEN);
		$response->assertJson(['error' => 'You cannot access this resource.']);
		$this->checkIfLoginExists($user->id, $updatedLogin, 'testLoginsUpdateUserCannotAccessLogin', 3);
	}

	/**
	 *  TLU6: test logins - update; wrong - fields are in wrong format (all must be string)
	 *  @return void
	 */
	public function testLoginsUpdateFieldsWrongType(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$updatedLogin = [
			'website_name' => [6],
			'website_address' => 4.3,
			'username' => '',
			'password' => true,
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/'.$login->id, $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'website_name' => [
					'The website name must be a string.'
				],
				'website_address' => [
					'The website address must be a string.',
					'The website address format is invalid.'
				],
				'username' => [
					'The username must be a string.'
				],
				'password' => [
					'The password must be a string.'
				]
			]
		]);
		$this->checkIfLoginExists($user->id, $updatedLogin, 'testLoginsUpdateFieldsWrongType', 3);
	}

	/**
	 *  TLU7: test logins - update; wrong - web address is not valid URL address
	 *  @return void
	 */
	public function testLoginsUpdateWebAddressNotUrl(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$updatedLogin = [
			'website_name' => 'DifferentWebsite',
			'website_address' => 'differentWebsite',
			'username' => 'differentName',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/'.$login->id, $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'website_address' => [
					'The website address format is invalid.'
				]
			]
		]);
		$this->checkIfLoginExists($user->id, $updatedLogin, 'testLoginsUpdateWebAddressNotUrl', 3);
	}

	/**
	 *  TLU8: test logins - update; wrong - values too long
	 *  @return void
	 */
	public function testLoginsUpdateValuesTooLong(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$updatedLogin = [
			'website_name' => Str::random(31),
			'website_address' => Str::random(256),
			'username' => Str::random(256),
			'password' => Str::random(256)
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/'.$login->id, $updatedLogin);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				"website_name" => [
					"The website name may not be greater than 30 characters."
				],
				"website_address" => [
					"The website address may not be greater than 255 characters.",
					"The website address format is invalid."
				],
				"username" => [
					"The username may not be greater than 255 characters."
				],
				"password" => [
					"The password may not be greater than 255 characters."
				]
			]
		]);
		$this->checkIfLoginExists($user->id, $updatedLogin, 'testLoginsUpdateValuesTooLong', 3);
	}

//-----------------------------------------------DELETE LOGIN-----------------------------------------------

	/**
	 *  TLD1: test logins - delete; correct 
	 *  @return void
	 */
	public function testLoginsDelete(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->deleteJson($this->route.'/'.$login->id);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'success' => 'Login was deleted successfully.'
		]);
		$this->assertDatabaseMissing('logins',[
			'id' => $login->id
		]);
	}

	/**
	 *  TLD2: test logins - delete; wrong - access token not given
	 *  @return void
	 */
	public function testLoginsDeleteUnauthenticated(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->deleteJson($this->route.'/'.$login->id);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'message' => 'Unauthenticated.'
		]);
		$this->assertDatabaseHas('logins',[
			'id' => $login->id
		]);
	}

	/**
	 *  TLD3: test logins - delete; wrong - requested login doesn't exist
	 *  @return void
	 */
	public function testLoginsDeleteLoginNotFound(): void {
		$user = $this->createUser();
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->deleteJson($this->route.'/-5');
		$response->assertStatus(HttpStatus::STATUS_BAD_REQUEST);
		$response->assertJson(['error' => 'Resource does not exist.']);
	}

	/**
	 *  TLD4: test logins - delete; wrong - user doesn't own requested login
	 *  @return void
	 */
	public function testLoginsDeleteUserCannotAccessLogin(): void {
		$user = $this->createUser();
		$login = $this->createLogin($user->id);
		$newUser = $this->createUser([
			'name' => 'Blubberwhale',
			'surname' => 'Candlestick',
			'email' => 'candlestick@website.com',
			'password' => bcrypt('SafePassword1')
		]);
		Passport::actingAs($newUser);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->deleteJson($this->route.'/'.$login->id);
		$response->assertStatus(HttpStatus::STATUS_FORBIDDEN);
		$response->assertJson([
			'error' => 'You cannot access this resource.'
		]);
		$this->assertDatabaseHas('logins',[
			'id' => $login->id
		]);
	}
}