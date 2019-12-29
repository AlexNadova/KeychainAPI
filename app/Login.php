<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Login extends Model
{
    // protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'website_name',
		'website_address',
		'domain',
        'username',
        'password'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    // Get
    public function getUsernameAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    // Set

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = Crypt::encryptString($value);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }
}
