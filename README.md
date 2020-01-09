## Peachenka API

Peachenka is password manager application for Biddingtools Group. It was create as part of final project of AP Degree Computer science program on University College of Norther Denmark.

<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Download

This project can be downloaded as ZIP or with command:
	
    https://github.com/AlexNadova/KeychainAPI.git

## Setup and start the server

First you need to genreate application key. Look in .env file if APP_KEY is set. If not, run:

    php artisan key:generate
    
Then configure your database in .enf file. Afterwards, run migrations with:

    php artisan migrate
   
And install Passport with:

    php artisan passport:install
    
Server can be started with command:

    php artisan serve
    
## HELP

You can find more help on [Laravel](https://laravel.com)
