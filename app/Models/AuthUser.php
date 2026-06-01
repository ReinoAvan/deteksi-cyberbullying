<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AuthUser extends Authenticatable
{
    protected $table = 'auth_users';

    protected $fillable = [
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];
}
