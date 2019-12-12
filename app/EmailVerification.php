<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
	public $table = 'email_verification';

	public $timestamps = false;
	
	protected $fillable = [
		'user_id', 'token', 'email_update'
	];
}
