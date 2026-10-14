<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';
    protected $fillable = ['username', 'password_hash', 'role'];
    protected $hidden = ['password_hash'];
    public $timestamps = false;
}
