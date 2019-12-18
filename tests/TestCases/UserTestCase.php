<?php

// namespace Tests\Testcases;

// use Illuminate\Foundation\Testing\TestCase;

// abstract class UserTestCase extends TestCase
// {
// 	use \Tests\CreatesApplication,
// 		\Tests\PrepareDatabase;

// 	// TUR1: test registration; correct
// 	public function testRegistration(): void { }

// 	// TUR2: test registration; correct - doesn't accept any extra fields
// 	public function testRegistrationExtraFields(): void { }

// 	// TUR3: test registration; wrong - email already in use
// 	public function testRegistrationEmailAlreadyInUse(): void { }

// 	// TUR4: test registration; wrong - name, surname and password are too short
// 	public function testRegistrationValuesTooShort(): void { }

// 	// TUR5: test registration; wrong - name, surname are too long
//   	public function testRegistrationValuesTooLong(): void { }

// 	// TUR6: test registration; wrong - passwords don't match
// 	public function testRegistrationPasswordsDontMatch(): void { }
	
// 	// TUR7: test registration; wrong - name & surname contain special characters
// 	public function testRegistrationValuesContainSpecialCharacters(): void { }
	
// 	// TUR8: test registration; wrong - name & surname contain numbers
// 	public function testRegistrationValuesContainNumbers(): void { }

// 	// TUR9: test registration; wrong - fields are of wrong type, should be string
// 	public function testRegistrationFieldsWrongType(): void { }

// 	// TUR10: test registration; wrong - email is invalid email address
// 	public function testRegistrationEmailWrongFormat(): void { }

// 	// TUR11: test registration; wrong - password is in wrong form (all lowercase), should have capital letter, lowercase letter and number
// 	public function testRegistrationPasswordOnlyLowerCase(): void { }

// 	// TUR12: test registration; wrong - password is in wrong form (all capital letters), should have capital letter, lowercase letter and number
// 	public function testRegistrationPasswordOnlyUpperCase(): void { }

// 	// TUR13: test registration; wrong - password is of wrong form (only numbers), should have capital letter, lowercase letter and number
// 	public function testRegistrationPasswordOnlyNumbers(): void { }

// 	// TUR14: test registration; wrong - no data given - all fields are required
// 	public function testRegistrationFieldsMissing(): void { }

// 	// TUL1: test login; correct
// 	public function testLogin(): void { }

// 	// TUL2: test login; wrong - incorrect email
// 	public function testLoginWrongEmail(): void { }

// 	// TUL3: test login; wrong - email not verified
// 	public function testLoginEmailNotVerified(): void { }

// 	// TUL4: test login; wrong - incorrect password
// 	public function testLoginWrongPassword(): void { }

// 	// TUL5: test logout; correct
// 	public function testLogout(): void { }

// 	// TUL6: test logout; wrong - anuthenticated - R without token
// 	public function testLogoutNotAuthenticated(): void { }

// 	// TUS1: test getting user; correct
// 	public function testShow(): void { }

// 	// TUS2: test getting user; wrong - unauthicated: no token used
// 	public function testShowUnauthicatedAccess(): void { }

// 	// TUD1: test destroy; correct
// 	public function testDestroy(): void { }

// 	// TUD2: test destroy; wrong - unauthicated: no token used
// 	public function testDestroyUnauthicatedAccess(): void { }

// 	// TUU1: test update; correct
// 	public function testUpdate(): void { }

// 	// TUU2: test update; correct - but extra fields added which should not be accepted
// 	public function testUpdateExtraFields(): void { }

// 	// TUU3: test update; wrong - name, surname and password are too short
// 	public function testUpdateValuesTooShort(): void { }

// 	// TUU4: test update; wrong - name, surname are too long
//   	public function testUpdateValuesTooLong(): void { }

// 	// TUU5: test update; wrong - passwords don't match
// 	public function testUpdatePasswordsDontMatch(): void { }

// 	// TUU6: test update; wrong - c_password given, password not
// 	public function testUpdatePasswordNotGiven(): void { }

// 	// TUU7: test update; wrong - password given, c_password not
// 	public function testUpdateCPasswordNotGiven(): void { }
	
// 	// TUU8: test update; wrong - name & surname contain special characters
// 	public function testUpdateValuesContainSpecialCharacters(): void { }

// 	// TUU9: test update; wrong - name & surname contain numbers
// 	public function testUpdateValuesContainNumbers(): void { }

// 	// TUU10: test update; wrong - fields are of wrong type, should be string
// 	public function testUpdateFieldsWrongType(): void { }

// 	// TUU11: test update; wrong - password is in wrong form (all lowercase), should have capital letter, lowercase letter and number
// 	public function testUpdatePasswordOnlyLowerCase(): void { }

// 	// TUU12: test update; wrong - password is in wrong form (all capital letters), should have capital letter, lowercase letter and number
// 	public function testUpdatePasswordOnlyUpperCase(): void { }

// 	// TUU13: test update; wrong - password is of wrong form (only numbers), should have capital letter, lowercase letter and number
// 	public function testUpdatePasswordOnlyNumbers(): void { }

// 	// TUU14: test update; wrong - no access token given
// 	public function testUpdateUnauthenticatedAccess(): void { }
// }
