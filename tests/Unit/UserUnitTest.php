<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Testcases\UserTestCase;
use App\Helpers\HttpStatus;
use App\User;

class UserUnitTests extends UserTestCase
{
	public $route = 'http://127.0.0.1:8000/api/v1';

	/**
	 * test registration; correct
	 * 
	 * @return void
	 */
	public function testRegistration(): void{
		$user = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'testy.testovsky@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$response->assertStatus(HttpStatus::STATUS_CREATED);
		$response->assertJson([
			'success'=> 'User was created.'
			]);
	}	

	 /**
	 * test registration; wrong - email already in use
	 * 
	 * @return void
	 */
	public function testRegistrationEmailAlreadyInUse(): void{
		$user = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'testy.testovsky@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - name, surname and password are too short
	 * 
	 * @return void
	 */
	public function testRegistrationValuesTooShort(): void{
		$user = [
			'name' => 'T',
			'surname' => 'T',
			'email' => 't.t@website.com',
			'password' => 'SP1',
			'c_password' => 'SP1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - name, surname are too long
	 * 
	 * @return void
	 */
	public function testRegistrationValuesTooLong(): void{
		$user = [
			'name' => 'TestyTestyTestyTesty', //20
			'surname' => 'TestovskyTestovsky', //18
			'email' => 'TestyTestyTestyTesty.TestovskyTestovsky@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - passwords don't match
	 * 
	 * @return void
	 */
	public function testRegistrationPasswordsDontMatch(): void{
		$user = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - name & surname & password contain special characters
	 * 
	 * @return void
	 */
	public function testRegistrationValuesContainSpecialCharacters(): void{
		$user = [
			'name' => '!Testy',
			'surname' => 'Tes@tovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'SafePassword1?',
			'c_password' => 'SafePassword1?',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - name & surname contain numbers
	 * 
	 * @return void
	 */
	public function testRegistrationValuesContainNumbers(): void{
		$user = [
			'name' => 'Test1y',
			'surname' => '0Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - name & surname are of wrong type, should be string
	 * 
	 * @return void
	 */
	public function testRegistrationNameSurnameWrongType(): void{
		$user = [
			'name' => 1,
			'surname' => 1,
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
				]
			]
		]);
	}
	
	 /**
	 * test registration; wrong - email is of wrong type, should be string
	 * 							- email is invalid email address
	 * 
	 * @return void
	 */
	public function testRegistrationEmailWrongTypeAndInvalid(): void{
		$user = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 1,
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'email' => [
					'The email must be a string.',
					'The email must be a valid email address.'
				]
			]
		]);
	}

	 /**
	 * test registration; wrong - password is of wrong type, should be string
	 * 
	 * @return void
	 */
	public function testRegistrationPasswordWrongType(): void{
		$user = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 1,
			'c_password' => 1,
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password format is invalid.',
					'The password must be a string.'
				]
			]
		]);
	}
	
	 /**
	 * test registration; wrong - password is in wrong form (all lowercase), should have capital letter, 
	 * lowercase letter and number
	 * 
	 * @return void
	 */
	public function testRegistrationPasswordOnlyLowerCase(): void{
		$user = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'onlylowercase',
			'c_password' => 'onlylowercase',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - password is in wrong form (all capital letters), should have capital 
	 * letter, lowercase letter and number
	 * 
	 * @return void
	 */
	public function testRegistrationPasswordOnlyUpperCase(): void{
		$user = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'ONLYUPPERCASE',
			'c_password' => 'ONLYUPPERCASE',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - password is of wrong form (only numbers-still string), should have capital 
	 * letter, lowercase letter and number
	 * 
	 * @return void
	 */
	public function testRegistrationPasswordOnlyNumbers(): void{
		$user = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => '0123456789',
			'c_password' => '0123456789',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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
	 * test registration; wrong - empty fields - all fields are required
	 * 
	 * @return void
	 */
	public function testRegistrationFieldsMissing(): void{
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

	/**
	 * test registration; wrong - fields contain empty strings - all fields are required
	 * 
	 * @return void
	 */
	public function testRegistrationFieldsContainEmptyStrings(): void{
		$user = [
			'name' => '',
			'surname' => '',
			'email' => '',
			'password' => '',
			'c_password' => '',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/register', $user);
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

	/**
	 * test login; correct
	 * 
	 * @return void
	 */
	public function testLogin(): void{
		$user = factory(User::class)->create();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/login', [
			'email' => $user->email,
			'password' => 'SafePassword1'
			]);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->dump();
	}	
	
	/**
	 * test login; wrong - incorrect email
	 * 
	 * @return void
	 */
	public function testLoginWrongEmail(): void{
		$user = factory(User::class)->create();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/login', [
			'email' => 'wrongmail@website.sk',
			'password' => 'SafePassword1'
			]);
		$response->assertStatus(HttpStatus::STATUS_FORBIDDEN);
		$response->assertJson([
			'error' => 'User could not be authenticated.',
		]);
	}

	 /**
	 * test logout; wrong - incorrect password
	 * 
	 * @return void
	 */
	public function testLoginWrongPassword(): void{
		$user = factory(User::class)->create();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->postJson($this->route.'/login', [
			'email' => $user->email,
			'password' => 'wrongformatpassword'
			]);
		$response->assertStatus(HttpStatus::STATUS_FORBIDDEN);
		$response->assertJson([
			'error' => 'User could not be authenticated.',
		]);
	}

	 /**
	 * test logout; correct
	 * 
	 * @return void
	 */
	public function testLogout(): void{
		$user = factory(User::class)->create();
		$token =  $user->createToken('test')->accessToken;
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token
		])->getJson($this->route.'/logout');
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'success' => 'User has been logged out.',
		]);
	}

	 /**
	 * test logout; wrong - no token passed
	 * 
	 * @return void
	 */
	public function testLogoutNotAuthenticated(): void{
		$user = factory(User::class)->create();
		$token =  $user->createToken('test')->accessToken;
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json'
		])->getJson($this->route.'/logout');
		$response->assertStatus(HttpStatus::STATUS_UNAUTHORIZED);
		$response->assertJson([
			'message' => 'Unauthenticated.',
		]);
	}

    /**
     * test getting user; correct
     *
     * @return void
     */
    public function testShow(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('testToken')->accessToken;
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
		])->get($this->route.'/user');
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->dump();
	}
	
	/**
     * test getting user; wrong - unauthicated: no token used
     *
     * @return void
     */
    public function testShowUnauthicatedAccess(): void{
		$user = \factory(User::class)->create();
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		])->get($this->route.'/user');
		$response->assertStatus(HttpStatus::STATUS_FORBIDDEN);
		$response->assertJson([
			'message' => 'You cannot access this resource..',
		]);
	}
	
	/**
	 * test destroy; correct
	 * @return void
	 */
	public function testDestroy(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
		])->delete($this->route.'/user');
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'success' => 'User was deleted successfully.'
		]);
	}

	/**
	 * test destroy; wrong - unauthicated: no token used
	 * @return void
	 */
	public function testDestroyUnauthicatedAccess(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json'
		])->delete($this->route.'/user');
		$response->assertStatus(HttpStatus::STATUS_FORBIDDEN);
		$response->assertJson([
			'message' => 'You cannot access this resource..'
		]);
	}

	/** 
	 * test update; correct
	 * @return void
	 */
	public function testUpdate(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedInfo = [
			'name' => 'UpdatedName',
			'surname' => 'UpdatedSurname',
			'email' => 'updated.email@website.com',
			'password' => 'UpdatedPassword1',
			'c_password' => 'UpdatedPassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
		])->putJson($this->route.'/user', $updatedInfo);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'message' => 'User was updated.',
			'data'=> [
				'id' => $user->id,
				'name' => 'UpdatedName',
				'surname' => 'UpdatedSurname',
				'email' => 'updated.email@website.com',
				'email_verified_at' => $user->email_verified_at->format('Y-m-d H:i:s'),
				'created_at' => $user->created_at->format('Y-m-d H:i:s'),
				'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
			]
		]);
	}

	/**
	 * test update; wrong - email already in use
	 * @return void
	 */
	public function testUpdateEmailAlreadyInUse(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$user2 = \factory(User::class)->create();
		$infoToUpdate = [
			'email' => $user2->email
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
		])->putJson($this->route.'/user', $infoToUpdate);
		$response->assertStatus(HttpStatus::STATUS_BAD_REQUEST);
		$response->assertJson([
			'error' => 'This email is already in use.'
		]);
	}

	/**
	 * test update; wrong - given email is the same as old one
	 * @return void
	 */
	public function testUpdateEmailSameAsOldOne(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$infoToUpdate = [
			'email' => $user->email
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
		])->putJson($this->route.'/user', $infoToUpdate);
		$response->assertStatus(HttpStatus::STATUS_OK);
		$response->assertJson([
			'message' => 'User was updated.',
			'data'=> [
				'id' => $user->id,
				'name' => $user->name,
				'surname' => $user->surname,
				'email' => $user->email,
				'email_verified_at' => $user->email_verified_at->format('Y-m-d H:i:s'),
				'created_at' => $user->created_at->format('Y-m-d H:i:s'),
				'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
			]
		]);
	}

	/**
	 * test update; wrong - name, surname and password are too short
	 * @return void
	 */
	public function testUpdateValuesTooShort(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'T',
			'surname' => 'T',
			'email' => 't.t@website.com',
			'password' => 'SP1',
			'c_password' => 'SP1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
	}

	/**
	 * test update; wrong - name, surname are too long
	 * @return void
	 */
  	public function testUpdateValuesTooLong(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'TestyTestyTestyTesty', //20
			'surname' => 'TestovskyTestovsky', //18
			'email' => 'TestyTestyTestyTesty.TestovskyTestovsky@website.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
	}

	/**
	 * test update; wrong - passwords don't match#
	 * @return void
	 */
	public function testUpdatePasswordsDontMatch(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
	 * test update; wrong - name & surname & password contain special characters
	 * @return void
	 */
	public function testUpdateValuesContainSpecialCharacters(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => '!Testy',
			'surname' => 'Tes@tovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'SafePassword1?',
			'c_password' => 'SafePassword1?',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
	}

	/**
	 * test update; wrong - name & surname contain numbers
	 * @return void
	 */
	public function testUpdateValuesContainNumbers(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'Test1y',
			'surname' => '0Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
	}

	/**
	 * test update; wrong - name & surname are of wrong type, should be string
	 * @return void
	 */
	public function testUpdateNameSurnameWrongType(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 1,
			'surname' => 1,
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
				]
			]
		]);
	}

	/**
	 * test update; wrong - email is of wrong type, should be string
	 *					  - email is invalid email address 
	 * @return void
	*/
	public function testUpdateEmailWrongTypeAndInvalid(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 1,
			'password' => 'SafePassword1',
			'c_password' => 'SafePassword1',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'email' => [
					'The email must be a string.',
					'The email must be a valid email address.'
				]
			]
		]);
	}

	/**
	 * test update; wrong - password is of wrong type, should be string
	 * @return void
	 */
	public function testUpdatePasswordWrongType(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 1,
			'c_password' => 1,
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				'password' => [
					'The password format is invalid.',
					'The password must be a string.'
				]
			]
		]);
	}

	/**
	 * test update; wrong - password is in wrong form (all lowercase), should have capital letter, lowercase letter and number
	 * @return void
	 */
	public function testUpdatePasswordOnlyLowerCase(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'onlylowercase',
			'c_password' => 'onlylowercase',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
	 * test update; wrong - password is in wrong form (all capital letters), should have capital letter, lowercase letter and number
	 * @return void
	 */
	public function testUpdatePasswordOnlyUpperCase(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => 'ONLYUPPERCASE',
			'c_password' => 'ONLYUPPERCASE',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
	 * test update; wrong - password is of wrong form (only numbers), should have capital letter, lowercase letter and number
	 * @return void
	 */
	public function testUpdatePasswordOnlyNumbers(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => 'Testy',
			'surname' => 'Testovsky',
			'email' => 'Testy.Testovsky@anotherWebsite.com',
			'password' => '0123456789',
			'c_password' => '0123456789',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
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
	 * test update; wrong - fields contain empty strings - all fields are required
	 * @return void
	 */
	public function testUpdateFieldsContainEmptyStrings(): void{
		$user = \factory(User::class)->create();
		$token = $user->createToken('test')->accessToken;
		$updatedUser = [
			'name' => '',
			'surname' => '',
			'email' => '',
			'password' => '',
			'c_password' => '',
		];
		$response = $this->withHeaders([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer '.$token,
		])->putJson($this->route.'/user', $updatedUser);
		$response->assertStatus(HttpStatus::STATUS_UNPROCESSABLE_ENTITY);
		$response->assertJson([
			'message' => 'The given data was invalid.',
			'errors'=> [
				"name" => [
					"The name format is invalid.",
					"The name must be a string."
				],
				"surname" => [
					"The surname format is invalid.",
					"The surname must be a string."
				],
				"email" => [
					"The email must be a string.",
					"The email must be a valid email address."
				],
				"password" => [
					"The password format is invalid.",
					"The password must be a string."
				]
			]
		]);
	}
}
