<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Config extends Model
{
    //Map to table in database
    protected $table='config';

    public $timestamps = false;
}
