<?php

namespace Tests\Testcases;

use Illuminate\Foundation\Testing\TestCase;

abstract class ResetPasswordTestCase extends TestCase
{
	use \Tests\CreatesApplication,
		\Tests\PrepareDatabase;

	// TRP1: test password reset; correct
	public function testPasswordResetCreate(): void { }

	// TRP2: test password reset; wrong - email doesn't exist in DB
	public function testPasswordResetCreateEmailNotFound(): void { }

	// TRP3: test password reset; wrong - values have wrong type
	public function testPasswordResetCreateValuesWrongType(): void { }

	// TRP4: test password reset; wrong - values not given
	public function testPasswordResetCreateValuesNotGiven(): void { }

	// TRP5: test password reset; correct
	public function testPasswordReset(): void { }

	// TRP6: test password reset; wrong - field not given
	public function testPasswordResetFieldsNotGiven(): void { }

	// TRP7: test password reset; wrong - fields wrong type
	public function testPasswordResetFieldsWrongType(): void { }

	// TRP8: test password reset; wrong - password and c_password don't match
	public function testPasswordResetPasswordsDontMatch(): void { }

	// TRP9: test password reset; wrong - password wrong format (all lowercase)
	public function testPasswordResetPasswordAllLowercase(): void { }

	// TRP10: test password reset; wrong - password wrong format (all uppercase)
	public function testPasswordResetPasswordAllUppercase(): void { }

	// TRP11: test password reset; wrong - password wrong format (all numbers - still string)
	public function testPasswordResetPasswordAllNumbers(): void { }

	// TRP12: test password reset; wrong - token not found in DB
	public function testPasswordResetTokenNotFound(): void { }

	// TRP13: test password reset; wrong - token older than 12h
	public function testPasswordResetTokenTooOld(): void { }

	// TRP14: test password reset; wrong - user not found by email
	public function testPasswordResetUserNotFound(): void { }
}