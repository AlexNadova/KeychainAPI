<?php

namespace App\Helpers;

class HttpStatus{
	const STATUS_OK = 200;
	const STATUS_CREATED = 201;
	const STATUS_BAD_REQUEST = 400;
	const STATUS_UNAUTHORIZED = 401;
	const STATUS_FORBIDDEN = 403;
	const STATUS_UNPROCESSABLE_ENTITY = 422;
	const STATUS_INTERNAL_SERVER_ERROR = 500;
}