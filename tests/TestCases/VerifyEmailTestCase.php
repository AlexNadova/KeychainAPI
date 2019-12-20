<?php

namespace Tests\Testcases;

use Illuminate\Foundation\Testing\TestCase;

abstract class VerifyEmailTestCase extends TestCase
{
	use \Tests\CreatesApplication,
		\Tests\PrepareDatabase;

	// TVE1: test email verification; correct
	public function testEmailUpdate(): void { }

	// TVE2: test email verification; wrong - field empty
	public function testEmailUpdateFieldsEmpty(): void { }

	// TVE3: test email verification; wrong - fields wrong type (all should be string)
	public function testEmailUpdateFieldsWrongType(): void { }

	// TVE4: test email verification; wrong - new email already in use
	public function testEmailUpdateNewEmailAlreadyInUse(): void { }

	// TVE5: test email verification; wrong - user with given email doesn't exist
	public function testEmailUpdateUserNotFound(): void { }

	// TVE6: test email verification; wrong - access token not used
	public function testEmailUpdateAnauthenticated(): void { }

	// TVE7: test email verification; correct 
	public function testEmailVerification(): void { }

	// TVE8: test email verification; wrong - email verification token not given
	public function testEmailVerificationTokenNotGiven(): void { }

	// TVE9: test email verification; wrong - token is of wrong type (should be string)
	public function testEmailVerificationTokenWrongType(): void { }

	// TVE10: test email verification; wrong - token not found in DB
	public function testEmailVerificationTokenNotFound(): void { }

	// TVE11: test email verification; wrong - token older than 12h
	public function testEmailVerificationTokenTooOld(): void { }

	// TVE12: test email verification; wrong - user doesn't exist anymore (was deleted)
	public function testEmailVerificationUserNotFound(): void { }

	// TVE13: test email verification; wrong - new email already in use
	public function testEmailVerificationNewEmailAlreadyInUse(): void { }
}