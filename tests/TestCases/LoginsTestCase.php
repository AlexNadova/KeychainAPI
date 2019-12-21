<?php

namespace Tests\Testcases;

use Illuminate\Foundation\Testing\TestCase;

abstract class LoginsTestCase extends TestCase
{
	use \Tests\CreatesApplication,
		\Tests\PrepareDatabase;

	// TLS1: test logins - store; correct
	public function testLoginsStore(): void { }

	// TLS2: test logins - store; correct but extra fields given which should not be accepted 
	public function testLoginsStoreExtraFields(): void { }

	// TLS3: test logins - store; wrong - fields not given
	public function testLoginsStoreFieldsNotGiven(): void { }

	// TLS4: test logins - store; wrong - fields are of wrong type (all should be string)
	public function testLoginsStoreFieldsWrongType(): void { }

	// TLS5: test logins - store; wrong - web address is not valid URL address
	public function testLoginsStoreWebAddressNotUrl(): void { }

	// TLS6: test logins - store; wrong - values too long
	public function testLoginsStoreValuesTooLong(): void { }

	// TLS7: test logins - store; wrong - access token not given
	public function testLoginsStoreUnauthenticated(): void { }

	// TLG1: test logins - show; correct 
	public function testLoginsGet(): void { }

	// TLG2: test logins - show; wrong - requested login doesn't exist
	public function testLoginsGetLoginNotFound(): void { }

	// TLG3: test logins - show; wrong - access token not given
	public function testLoginsGetUnauthorized(): void { }

	// TLG4: test logins - show; wrong - user doesn't own requested login
	public function testLoginsGetUserCannotAccessLogin(): void { }

	// TLG5: test logins - index; correct 
	public function testLoginsGetAll(): void { }

	// TLG6: test logins - index; wrong - access token not given
	public function testLoginsGetAllUnauthorized(): void { }

	// TLG7: test logins - index; wrong - user doesn't have any logins
	public function testLoginsGetAllNoLoginsOwned(): void { }

	// TLU1: test logins - update; correct 
	public function testLoginsUpdate(): void { }

	// TLU2: test logins - update; wrong - extra fields given
	public function testLoginsUpdateExtraFields(): void { }

	// TLU3: test logins - update; wrong - access token not given
	public function testLoginsUpdateUnauthenticated(): void { }

	// TLU4: test logins - update; wrong - requested login doesn't exist
	public function testLoginsUpdateLoginNotFound(): void { }

	// TLU5: test logins - update; wrong - user doesn't own requested login
	public function testLoginsUpdateUserCannotAccessLogin(): void { }

	// TLU6: test logins - update; wrong - fields are in wrong format (all must be string)
	public function testLoginsUpdateFieldsWrongType(): void { }

	// TLU7: test logins - update; wrong - web address is not valid URL address
	public function testLoginsUpdateWebAddressNotUrl(): void { }

	// TLU8: test logins - update; wrong - values too long
	public function testLoginsUpdateValuesTooLong(): void { }

	// TLD1: test logins - delete; correct 
	public function testLoginsDelete(): void { }

	// TLD2: test logins - delete; wrong - access token not given
	public function testLoginsDeleteUnauthenticated(): void { }

	// TLD3: test logins - delete; wrong - requested login doesn't exist
	public function testLoginsDeleteLoginNotFound(): void { }

	// TLD4: test logins - delete; wrong - user doesn't own requested login
	public function testLoginsDeleteUserCannotAccessLogin(): void { }
}