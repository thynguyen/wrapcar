<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PhoneExcept extends Model
{
    //Map to table in database
    protected $table='phone_except';

    public $timestamps = false;
}
