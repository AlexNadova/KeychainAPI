<?php

namespace Tests\Testcases;

use Illuminate\Foundation\Testing\TestCase;

abstract class DomainTestCase extends TestCase
{
    use \Tests\CreatesApplication,
		\Tests\PrepareDatabase;
	
	// TLS8: test logins - store; correct: domain added automatically from website_address
	public function testLoginsStoreDomain(): void { }

	// TLU9: test logins - update; correct: if address is updated, domain is updated
    public function testLoginsUpdateDomain(): void { }
}