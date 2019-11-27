<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    // Customize the names of the columns used to store the timestamps.
    //const CREATED_AT = 'created';
    //const UPDATED_AT = 'last_modified';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'websiteName',
        'websiteAddress',
        'userName',
        'password'
    ];
}
