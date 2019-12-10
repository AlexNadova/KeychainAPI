<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Login;
use Faker\Generator as Faker;

$factory->define(Login::class, function (Faker $faker) {
    return [
        'websiteName' => $faker->domainName,
        'websiteAddress' => $faker->url,
        'username' => $faker->userName,
        'password' => $faker->password, //'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
    ];
});
