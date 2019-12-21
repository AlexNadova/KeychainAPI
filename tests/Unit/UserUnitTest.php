<?php

namespace Tests\Unit;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;
use Tests\Testcases\UserTestCase;
use App\Helpers\HttpStatus;
use App\User;
use DateTime;

class UserUnitTests extends UserTestCase
{
	public $route = 'http://127.0.0.1:8000/api/v1';

//-----------------------------------------------REGISTRATION-----------------------------------------------

	/**
	 *  TUR1: test registration; correct
	 * 	@return void
	 */
	public function testRegistration(): void {
		$user = [
			'name' => 'Snozzlebert',
			'surname' => 'Cramplesnutch',
			'email' => 'cramplesnutch@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseHas('users', [
			'name' => 'Snozzlebert',
			'surname' => 'Cramplesnutch',
			'email' => 'cramplesnutch@website.com',
		]);
		$this->assertDatabaseHas('email_verification', [
			'email_update' => 'cramplesnutch@website.com'
		]);
		$response->assertStatus(HttpStatus::STATUS_CREATED);
		$response->assertJson([
			'success' => 'User was created.'
		]);
	}	

	/**
	 *  TUR2: test registration; correct - doesn't accept any extra fields
	 *	@return void
	 */
	public function testRegistrationExtraFields(): void {
		$user = [
			'name' => 'Baseballmitt',
			'surname' => 'Crumplehorn',
			'email' => 'crumplehorn@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
			'email_verified_at' => '2019-12-17 11:27:00',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseHas('users', [
			'name' => 'Snozzlebert',
			'surname' => 'Cramplesnutch',
			'email' => 'cramplesnutch@website.com',
		]);
		$this->assertDatabaseMissing('users', [
			'email_verified_at' => '2019-12-17 11:27:00'
		]);
		$this->assertDatabaseHas('email_verification', [
			'email_update' => 'cramplesnutch@website.com'
		]);
		$response->assertStatus(HttpStatus::STATUS_CREATED);
		$response->assertJson([
			'success'=> 'User was created.'
		]);
	}

	/**
	 *  TUR3: test registration; wrong - email already in use
	 *  @return void
	 */
	public function testRegistrationEmailAlreadyInUse(): void {
		$user = [
			'name' => 'Billiardball',
			'surname' => 'Crimpysnitch',
			'email' => 'crimpysnitch@website.com',
			'password' => bcrypt('SafePassword1'),
			'c_password' => 'SafePassword1',
		];
		User::create($user);
		$user['name'] = 'Bourgeoisie';
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'name' => 'Bourgeoisie',
			'email' => 'crimpysnitch@website.com'
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'email' => [
					'The email has already been taken.'
				]
			]
		]);
	}

	/**
	 *  TUR4: test registration; wrong - name, surname and password are too short
	 *  @return void
	 */
	public function testRegistrationValuesTooShort(): void {
		$user = [
			'name' => 'B',
			'surname' => 'C',
			'email' => 'b.c@website.com',
			'password' => 'SP1',
			'c_password' => 'SP1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'b.c@website.com'
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.'
				],
				'surname' => [
					'The surname format is invalid.'
				],
				'password' => [
					'The password format is invalid.'
				]
			]
		]);
	}

	/**
	 *  TUR5: test registration; wrong - name, surname are too long
	 *  @return void
	 */
	public function testRegistrationValuesTooLong(): void {
		$user = [
			'name' => 'Nametoooooooooooolong', //21
			'surname' => 'Thiscantpaaaaaaaaaaaass', //23
			'email' => 'thiscantpaaaaaaaaaaaass@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'thiscantpaaaaaaaaaaaass@website.com'
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.'
				],
				'surname' => [
					'The surname format is invalid.'
				]
			]
		]);
	}

	/**
	 *  TUR6: test registration; wrong - passwords don't match
	 *  @return void
	 */
	public function testRegistrationPasswordsDontMatch(): void {
		$user = [
			'name' => 'Bourgeoisie',
			'surname' => 'Cuttlefish',
			'email' => 'cuttlefish@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'cuttlefish@website.com'
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'c_password' => [
					'The c password and password must match.'
				]
			]
		]);
	}

	/**
	 *  TUR7: test registration; wrong - name & surname & password contain special characters
	 *  @return void
	 */
	public function testRegistrationValuesContainSpecialCharacters(): void {
		$user = [
			'name' => '!Bourgeoisie',
			'surname' => 'C@uttlefish',
			'email' => 'cuttlefish@website.com',
			'password' => 'SafePassword1?',
			'c_password' => 'SafePassword1?',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'cuttlefish@website.com'
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.'
				],
				'surname' => [
					'The surname format is invalid.'
				]
			]
		]);
	}
	
	/**
	 *  TUR8: test registration; wrong - name & surname contain numbers
	 *  @return void
	 */
	public function testRegistrationValuesContainNumbers(): void {
		$user = [
			'name' => 'Bo2urgeoisie',
			'surname' => 'Cuttlefish5',
			'email' => 'cuttlefish@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'cuttlefish@website.com'
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.'
				],
				'surname' => [
					'The surname format is invalid.'
				]
			]
		]);
	}

	/**
	 *  TUR9: test registration; wrong - fields are of wrong type, should be string
	 *  @return void
	 */
	public function testRegistrationFieldsWrongType(): void {
		$user = [
			'name' => null,
			'surname' => '',
			'email' => [],
			'password' => 1,
			'c_password' => 1,
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'name' => null
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name field is required.'
				],
				'surname' => [
					'The surname field is required.'
				],
				'email' => [
					'The email field is required.'
				],
				'password' => [
					'The password format is invalid.',
					'The password must be a string.'
				]
			]
		]);
	}
	
	/**
	 *  TUR10: test registration; wrong - email is invalid email address
	 *  @return void
	 */
	public function testRegistrationEmailWrongFormat(): void {
		$user = [
			'name' => 'Bourgeoisie',
			'surname' => 'Cuttlefish',
			'email' => 'cuttlefish',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'cuttlefish'
		]);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'email' => [
					'The email must be a valid email address.'
				]
			]
		]);
	}

	
	/**
	 *  TUR11: test registration; wrong - password is in wrong form (all lowercase), should have capital letter, lowercase letter and number
	 *  @return void
	 */
    public function testRegistrationPasswordOnlyLowerCase(): void {
		$user = [
			'name' => 'Bourgeoisie',
			'surname' => 'Cuttlefish',
			'email' => 'cuttlefish@website.com',
			'password' => 'onlylowercase',
			'c_password' => 'onlylowercase',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'cuttlefish@website.com'
		]);
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
	 *  TUR12: test registration; wrong - password is in wrong form (all capital letters), should have capital letter, lowercase letter and number
	 *  @return void
	 */
    public function testRegistrationPasswordOnlyUpperCase(): void {
		$user = [
			'name' => 'Bourgeoisie',
			'surname' => 'Cuttlefish',
			'email' => 'cuttlefish@website.com',
			'password' => 'ONLYUPPERCASE',
			'c_password' => 'ONLYUPPERCASE',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'cuttlefish@website.com'
		]);
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
	 *  TUR13: test registration; wrong - password is of wrong form (only numbers), should have capital letter, lowercase letter and number
	 *  @return void
	 */
	public function testRegistrationPasswordOnlyNumbers(): void {
		$user = [
			'name' => 'Bourgeoisie',
			'surname' => 'Cuttlefish',
			'email' => 'cuttlefish@website.com',
			'password' => '0123456789',
			'c_password' => '0123456789',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$this->assertDatabaseMissing('users', [
			'email' => 'cuttlefish@website.com'
		]);
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
	 *  TUR14: test registration; wrong - no data given - all fields are required
	 *  @return void
	 */
	public function testRegistrationFieldsMissing(): void {
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register');
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name field is required.'
				],
				'surname' => [
					'The surname field is required.'
				],
				'email' => [
					'The email field is required.'
				],
				'password' => [
					'The password field is required.'
				],
				'c_password' => [
					'The c password field is required.'
				]
			]
		]);
	}

//-----------------------------------------------LOGIN-----------------------------------------------
	
	/**
	 *  create user for login tests
	 */
	public function createUserForLoginTests() {
		$user = User::where([
			'email' => 'cucumberpatch@website.com'
		])->first();
		if(!$user){
			$user = [
				'name' => 'Battlefield',
				'surname' => 'Cucumberpatch',
				'email' => 'cucumberpatch@website.com',
				'password' => bcrypt('SafePassword1'),
				'email_verified_at' => now(),
			];
			$user = User::create($user);
			$user->email_verified_at = now();
			$user->update();
		}
		return $user;
	}

	/**
	 *  TUL1: test login; correct
	 *  @return void
	 */
	public function testLogin(): void {
		$user = $this->createUserForLoginTests();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/login', [
			'email' => $user->email,
			'password' => 'SafePassword1'
		]);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$this->assertDatabaseHas('oauth_access_tokens',[
			'user_id' => $user->id
		]);
	}	
	
	/**
	 *  TUL2: test login; wrong - incorrect email
	 *  @return void
	 */
	public function testLoginWrongEmail(): void {
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/login', [
			'email' => 'wrongmail@website.sk',
			'password' => 'SafePassword1'
			]);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'error' => 'User could not be authenticated.',
		]);
	}
	
	/**
	 *  TUL3: test login; wrong - email not verified
	 *  @return void
	 */
	public function testLoginEmailNotVerified(): void {
		$user = $this->createUserForLoginTests();
		$user->email_verified_at = null;
		$user->save();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/login', [
			'email' => $user->email,
			'password' => 'SafePassword1'
			]);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'error' => 'You first need to verify your email.',
		]);
	}

	/**
	 *  TUL4: test logout; wrong - incorrect password
	 *  @return void
	 */
	public function testLoginWrongPassword(): void {
		$user = $this->createUserForLoginTests();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/login', [
			'email' => $user->email,
			'password' => 'wrongformatpassword'
		]);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'error' => 'User could not be authenticated.',
		]);
	}

//-----------------------------------------------CREATE USER FOR TESTS-----------------------------------------------

	/**
	 *  create authenticated and verified user for tests
	 */
	public function createUser($user) {
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
			$user->email_verified_at = now();
			$user->update();
		}
		return $user;
	}

//-----------------------------------------------LOGOUT-----------------------------------------------

	/**
	 *  TUL5: test logout; correct
	 *  @return void
	 */
	public function testLogout(): void {
		$user = $this->createUser([
			'name' => 'Bulbasaur',
			'surname' => 'Banglesnatch',
			'email' => 'banglesnatch@website.com',
		]);
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->deleteJson($this->route.'/logout');
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'success' => 'User has been logged out.',
		]);
	}

	/**
	 *  TUL6: test logout; wrong - anuthenticated - R without token
	 *  @return void
	 */
	public function testLogoutNotAuthenticated(): void {
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json'
		])->deleteJson($this->route.'/logout');
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'message' => 'Unauthenticated.',
		]);
	}

//-----------------------------------------------SHOW-----------------------------------------------

	/**
     *  TUS1: test getting user; correct
     *  @return void
     */
    public function testShow(): void {
		$user = $this->createUser([
			'name' => 'Billyray',
			'surname' => 'Crackersprout',
			'email' => 'crackersprout@website.com',
		]);
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->get($this->route.'/user');
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'data' =>[
				'id' => $user->id,
				'name' => $user->name,
				'surname' => $user->surname,
				'email' => $user->email,
				'email_verified_at' => $user->email_verified_at,
				'created_at' => $user->created_at,
				'updated_at' => $user->updated_at
			]
		]);
	}
	
	/**
     *  TUS2: test getting user; wrong - unauthicated: no token used
     *  @return void
     */
    public function testShowUnauthicatedAccess(): void {
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->get($this->route.'/user');
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'message' => 'Unauthenticated.',
		]);
	}
	
//-----------------------------------------------DESTROY-----------------------------------------------

	/**
	 *  TUD1: test destroy; correct
	 *  @return void
	 */
	public function testDestroy(): void {
		$user = $this->createUser([
			'name' => 'Blubberdick',
			'surname' => 'Cumberbund',
			'email' => 'cumberbund@website.com',
		]);
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->delete($this->route.'/user');
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'success' => 'User was deleted successfully.'
		]);
		$this->assertDatabaseMissing('users', [
			'email' => $user->email
		]);
	}

	/**
	 *  TUD2: test destroy; wrong - unauthicated: no token used
	 *  @return void
	 */
	public function testDestroyUnauthicatedAccess(): void {
		$user = $this->createUser([
			'name' => 'Bunsenburner',
			'surname' => 'Nottinghill',
			'email' => 'nottinghill@website.com',
		]);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json'
		])->delete($this->route.'/user');
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'message' => 'Unauthenticated.'
		]);
		$this->assertDatabaseHas('users', [
			'email' => $user->email
		]);
	}

//-----------------------------------------------UPDATE-----------------------------------------------

    /** 
	 *  TUU1: test update; correct
	 *  @return void
	 */
	public function testUpdate(): void {
		$user = $this->createUser([
			'name' => 'Blasphemy',
			'surname' => 'Crackerdong',
			'email' => 'crackerdong@website.com',
		]);
		$updatedInfo = [
			'name' => 'UpdatedName',
			'surname' => 'UpdatedSurname',
			'password' => 'UpdatedPassword1',
			'c_password' => 'UpdatedPassword1',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedInfo);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'message' => 'User was updated.',
			'data'=> [
				'id' => $user->id,
				'name' => 'UpdatedName',
				'surname' => 'UpdatedSurname',
				'email' => 'crackerdong@website.com',
				'email_verified_at' => $user->email_verified_at,
				'created_at' => $user->created_at,
				'updated_at' => $user->updated_at,
			]
		]);
		$this->assertDatabaseHas('users',[
			'name' => 'UpdatedName',
			'surname' => 'UpdatedSurname',
			'email' => 'crackerdong@website.com'
		]);
	}

	/** 
	 *  TUU2: test update; correct - but extra fields added which should not be accepted
	 *	@return void
	 */
	 public function testUpdateExtraFields(): void {
		$user = $this->createUser([
			'name' => 'Buckminster',
			'surname' => 'Custardbath',
			'email' => 'custardbath@website.com',
		]);
		$updatedInfo = [
			'name' => 'NewName',
			'surname' => 'NewSurname',
			'password' => 'NewPassword1',
			'c_password' => 'NewPassword1',
			'email_updated_at' => '2019-12-18 14:08:00'
		];
		Passport::actingAs($user);
		$response = $this->actingAs($user)->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedInfo);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'message' => 'User was updated.',
			'data'=> [
				'id' => $user->id,
				'name' => 'NewName',
				'surname' => 'NewSurname',
				'email' => 'custardbath@website.com',
				'email_verified_at' => $user->email_verified_at->format('Y-m-d H:i:s'),
				'created_at' => $user->created_at->format('Y-m-d H:i:s'),
				'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
			]
		]);
		$this->assertDatabaseMissing('users',[
			'name' => 'UpdatedName',
			'surname' => 'UpdatedSurname',
			'email' => 'crackerdong@website.com',
			'email_verified_at' => '2019-12-18 14:08:00'
		]);
	}

	/**
	 *  // TUU3: test update; wrong - name, surname and password are too short
	 *  @return void
	 */
	public function testUpdateValuesTooShort(): void {
		$user = $this->createUser([
			'name' => 'Wellington',
			'surname' => 'Countryside',
			'email' => 'countryside@website.com',
		]);
		$updatedUser = [
			'name' => 'W',
			'surname' => 'C',
			'password' => 'SP1',
			'c_password' => 'SP1',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.'
				],
				'surname' => [
					'The surname format is invalid.'
				],
				'password' => [
					'The password format is invalid.'
				]
			]
		]);
		$this->assertDatabaseMissing('users',[
			'name' => 'W',
			'surname' => 'C',
		]);
	}

	/**
	 *  TUU4: test update; wrong - name, surname are too long
	 *  @return void
	 */
  	public function testUpdateValuesTooLong(): void {
		$user = $this->createUser([
			'name' => 'Benetton',
			'surname' => 'Crumplesack',
			'email' => 'crumplesack@website.com',
		]);
		$updatedUser = [
			'name' => 'Nametoooooooooooolong', //21
			'surname' => 'Thiscantpaaaaaaaaaaaass', //23
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.'
				],
				'surname' => [
					'The surname format is invalid.'
				]
			]
		]);
		$this->assertDatabaseMissing('users',[
			'name' => 'Nametoooooooooooolong',
			'surname' => 'Thiscantpaaaaaaaaaaaass',
		]);
	}

	/**
	 *  TUU5: test update; wrong - passwords don't match
	 *  @return void
	 */
	public function testUpdatePasswordsDontMatch(): void {
		$user = $this->createUser([
			'name' => 'Bouillabaisse',
			'surname' => 'Cumberbund',
			'email' => 'cumberbund@website.com',
		]);
		$updatedUser = [
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'c_password' => [
					'The c password and password must match.'
				]
			]
		]);
	}

	/**  
	 *  TUU6: test update; wrong - c_password given, password not
	 *  @return void
	 */
	public function testUpdatePasswordNotGiven(): void {
		$user = $this->createUser([
			'name' => 'Barnacle',
			'surname' => 'Concubine',
			'email' => 'concubine@website.com',
		]);
		$updatedUser = [
			'c_password' => 'SafePassword',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'c_password' => [
					'The c password and password must match.'
				]
			]
		]);
	}

	/**  
	 *  TUU7: test update; wrong - password given, c_password not
	 *  @return void
	 */
	public function testUpdateCPasswordNotGiven(): void {
		$user = $this->createUser([
			'name' => 'Brandybuck',
			'surname' => 'Scratchnsniff',
			'email' => 'scratchnsniff@website.com',
		]);
		$updatedUser = [
			'password' => 'SafePassword',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'c_password' => [
					'The c password field is required when password is present.'
				]
			]
		]);
	}
	
	/**
	 * 	TUU8: test update; wrong - name & surname contain special characters
	 *  @return void
	 */
	public function testUpdateValuesContainSpecialCharacters(): void {
		$user = $this->createUser([
			'name' => 'Bonaparte',
			'surname' => 'Copperwire',
			'email' => 'copperwire@website.com',
		]);
		$updatedUser = [
			'name' => '!Bonaparte',
			'surname' => 'Copp@erwire',
			'password' => 'SafePassword1?',
			'c_password' => 'SafePassword1?',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.'
				],
				'surname' => [
					'The surname format is invalid.'
				]
			]
		]);
		$this->assertDatabaseMissing('users',[
			'name' => $updatedUser['name'],
			'surname' => $updatedUser['surname'],
			'email' => $user->email
		]);
	}

	/**
	 *  TUU9: test update; wrong - name & surname contain numbers
	 *  @return void
	 */
	public function testUpdateValuesContainNumbers(): void {
		$user = $this->createUser([
			'name' => 'Benadryl',
			'surname' => 'Clavichord',
			'email' => 'clavichord@website.com',
		]);
		$updatedUser = [
			'name' => 'Benad5ryl',
			'surname' => '0Clavichord',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.'
				],
				'surname' => [
					'The surname format is invalid.'
				]
			]
		]);
		$this->assertDatabaseMissing('users',[
			'name' => $updatedUser['name'],
			'surname' => $updatedUser['surname'],
			'email' => $user->email
		]);
	}

	/**
	 *  TUU10: test update; wrong - fields are of wrong type, should be string
	 *  @return void
	 */
	public function testUpdateFieldsWrongType(): void {
		$user = $this->createUser([
			'name' => 'Bumbersplat',
			'surname' => 'Claritin',
			'email' => 'claritin@website.com',
		]);
		$updatedUser = [
			'name' => 9.5,
			'surname' => [],
			'password' => '',
			'c_password' => '',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'name' => [
					'The name format is invalid.',
					'The name must be a string.'
				],
				'surname' => [
					'The surname format is invalid.',
					'The surname must be a string.'
				],
				'password' => [
					'The password format is invalid.',
					'The password must be a string.'
				]
			]
		]);
		$this->assertDatabaseMissing('users',[
			'name' => $updatedUser['name'],
			'email' => $user->email
		]);
	}

	/**
	 *  TUU11: test update; wrong - password is in wrong form (all lowercase), should have capital letter, lowercase letter and number
	 *  @return void
	 */
	public function testUpdatePasswordOnlyLowerCase(): void {
		$user = $this->createUser([
			'name' => 'Bumbersplat',
			'surname' => 'Collywog',
			'email' => 'collywog@website.com',
		]);
		$updatedUser = [
			'password' => 'onlylowercase',
			'c_password' => 'onlylowercase',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password format is invalid.'
				]
			]
		]);
	}

	/**
	 *  TUU12: test update; wrong - password is in wrong form (all capital letters), should have capital letter, lowercase letter and number
	 *  @return void
	 */
	public function testUpdatePasswordOnlyUpperCase(): void {
		$user = $this->createUser([
			'name' => 'Bonaparte',
			'surname' => 'Kryptonite',
			'email' => 'kryptonite@website.com',
		]);
		$updatedUser = [
			'password' => 'ONLYUPPERCASE',
			'c_password' => 'ONLYUPPERCASE',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password format is invalid.'
				]
			]
		]);
	}

	/**
	 *  TUU13: test update; wrong - password is of wrong form (only numbers), should have capital letter, lowercase letter and number
	 *  @return void
	 */
	public function testUpdatePasswordOnlyNumbers(): void {
		$user = $this->createUser([
			'name' => 'Bakeacake',
			'surname' => 'Fromascratch',
			'email' => 'fromascratch@website.com',
		]);
		$updatedUser = [
			'password' => '0123456789',
			'c_password' => '0123456789',
		];
		Passport::actingAs($user);
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password format is invalid.'
				]
			]
		]);
	}

	/** 
	 *  TUU14: test update; wrong - no access token given
	 *  @return void
	 */
	public function testUpdateUnauthenticatedAccess(): void {
		$user = $this->createUser([
			'name' => 'Barnacle',
			'surname' => 'Lingerie',
			'email' => 'lingerie@website.com',
		]);
		$updatedUser = [
			'surname' => 'Notfound'
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'message' => 'Unauthenticated.'
		]);
		$this->assertDatabaseMissing('users',[
			'surname' => 'Notfound',
			'email' => 'lingerie@website.com',
		]);
	}
}
