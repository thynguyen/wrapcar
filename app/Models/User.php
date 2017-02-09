<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public static function getList()
    {
        $query = DB::table("users");
        $query->select('users.*', 'roles.name', 'roles.id as role_id');
        $query->join('roles', 'users.role_id', '=', 'roles.id');
        $query->where('users.id', '<>', auth()->id());

        return $query->paginate(20);
    }
}
