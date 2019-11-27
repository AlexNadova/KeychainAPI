<?php

use Illuminate\Database\Seeder;
use App\Login;

class LoginTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Login::class, 15)->create();
    }
}
