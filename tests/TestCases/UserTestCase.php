<?php

namespace Tests\Testcases;

use Illuminate\Foundation\Testing\TestCase;

abstract class UserTestCase extends TestCase
{
	use \Tests\CreatesApplication;

	// test registration; correct
	public function testRegistration(): void{}

	// test registration; wrong - email already in use
	public function testRegistrationEmailAlreadyInUse(): void{}

	//test registration; wrong - name, surname and password are too short
	public function testRegistrationValuesTooShort(): void{}

	//test registration; wrong - name, surname are too long
  	public function testRegistrationValuesTooLong(): void{}

	//test registration; wrong - passwords don't match
	public function testRegistrationPasswordsDontMatch(): void{}
	
	//test registration; wrong - name & surname & password contain special characters
	public function testRegistrationValuesContainSpecialCharacters(): void{}
	
	//test registration; wrong - name & surname contain numbers
	public function testRegistrationValuesContainNumbers(): void{}

	//test registration; wrong - name & surname are of wrong type, should be string
	public function testRegistrationNameSurnameWrongType(): void{}

	/**test registration; wrong - email is of wrong type, should be string
								 - email is invalid email address */
	public function testRegistrationEmailWrongTypeAndInvalid(): void{}

	//test registration; wrong - password is of wrong type, should be string
	public function testRegistrationPasswordWrongType(): void{}

	//test registration; wrong - password is in wrong form (all lowercase), should have capital letter, lowercase letter and number
	public function testRegistrationPasswordOnlyLowerCase(): void{}

	//test registration; wrong - password is in wrong form (all capital letters), should have capital letter, lowercase letter and number
	public function testRegistrationPasswordOnlyUpperCase(): void{}

	//test registration; wrong - password is of wrong form (only numbers), should have capital letter, lowercase letter and number
	public function testRegistrationPasswordOnlyNumbers(): void{}

	//test registration; wrong - empty fields - all fields are required
	public function testRegistrationFieldsMissing(): void{}

	//test registration; wrong - fields contain empty strings - all fields are required
	public function testRegistrationFieldsContainEmptyStrings(): void{}

	//test login; correct
	public function testLogin(): void{}

	//test login; wrong - incorrect email
	public function testLoginWrongEmail(): void{}

	//test login; wrong - incorrect password
	public function testLoginWrongPassword(): void{}

	//test logout; correct
	public function testLogout(): void{}

	//test logout; wrong -anuthenticated - R without token
	public function testLogoutNotAuthenticated(): void{}

	//test getting user; correct
	public function testShow(): void{}

	//test getting user; wrong - unauthicated: no token used
	public function testShowUnauthicatedAccess(): void{}

	//test destroy; correct
	public function testDestroy(): void{}

	//test destroy; wrong - unauthicated: no token used
	public function testDestroyUnauthicatedAccess(): void{}

	// test update; correct
	public function testUpdate(): void{}

	// test update; wrong - email already in use
	public function testUpdateEmailAlreadyInUse(): void{}

	// test update; wrong - given email is the same as old one
	public function testUpdateEmailSameAsOldOne(): void{}

	//test update; wrong - name, surname and password are too short
	public function testUpdateValuesTooShort(): void{}

	//test update; wrong - name, surname are too long
  	public function testUpdateValuesTooLong(): void{}

	//test update; wrong - passwords don't match
	public function testUpdatePasswordsDontMatch(): void{}
	
	//test update; wrong - name & surname & password contain special characters
	public function testUpdateValuesContainSpecialCharacters(): void{}

	//test update; wrong - name & surname contain numbers
	public function testUpdateValuesContainNumbers(): void{}

	//test update; wrong - name & surname are of wrong type, should be string
	public function testUpdateNameSurnameWrongType(): void{}

	/**test update; wrong - email is of wrong type, should be string
								 - email is invalid email address */
	public function testUpdateEmailWrongTypeAndInvalid(): void{}

	//test update; wrong - password is of wrong type, should be string
	public function testUpdatePasswordWrongType(): void{}

	//test update; wrong - password is in wrong form (all lowercase), should have capital letter, lowercase letter and number
	public function testUpdatePasswordOnlyLowerCase(): void{}

	//test update; wrong - password is in wrong form (all capital letters), should have capital letter, lowercase letter and number
	public function testUpdatePasswordOnlyUpperCase(): void{}

	//test update; wrong - password is of wrong form (only numbers), should have capital letter, lowercase letter and number
	public function testUpdatePasswordOnlyNumbers(): void{}

	//test update; wrong - fields contain empty strings - all fields are required
	public function testUpdateFieldsContainEmptyStrings(): void{}
}
