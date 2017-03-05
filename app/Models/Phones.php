<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Phones extends Model
{
    //Map to table in database
    protected $table='phones';

    public $timestamps = false;
}
