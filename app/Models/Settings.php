<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth;

class Settings extends Model
{
    //Map to table in database
    protected $table='settings';

    public function getLists()
    {
        $query = DB::table($this->table);
        $user = Auth::user();
        if ($user) {
            $role = config('wrap.roles');
            if ($user->role_id != $role['admin']) {
                $query->where('user_id', $user->id);
            }
        }

        return $query->paginate(10);
    }
}
