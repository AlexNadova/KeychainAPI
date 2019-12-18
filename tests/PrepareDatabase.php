<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
trait PrepareDatabase
{
    /**
    * If true, setup has run at least once.
    * @var boolean
    */
	protected static $setUpHasRunOnce = false;    
	
	/**
    * After the first run of setUp 
    * @return void
    */
    public function setUp(): void
    {
        parent::setUp();
        if (!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');
            Artisan::call('passport:install');
            static::$setUpHasRunOnce = true;
        }
    }
}