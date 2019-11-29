<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    // protected $primaryKey = 'id';

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

    /* public function user() {
        return $this->belongsTo(User::class);
    } */
}
