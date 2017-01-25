<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contents extends Model
{
    //Map to table in database
    protected $table='contents';

    public function getContent($keyword)
    {
        $query = DB::table($this->table);
        if (!empty($keyword)) {
            $query->where('brand_car', 'LIKE', "%{$keyword}%");
            $query->orWhere('code_car_site', 'LIKE', "%{$keyword}%");
            $query->orWhere('color', 'LIKE', "%{$keyword}%");
            $query->orWhere('km_run', 'LIKE', "%{$keyword}%");
            $query->orWhere('product_year', 'LIKE', "%{$keyword}%");
            $query->orWhere('price', 'LIKE', "%{$keyword}%");
            $query->orWhere('contact', 'LIKE', "%{$keyword}%");
            $query->orWhere('phone', 'LIKE', "%{$keyword}%");
            $query->orWhere('city', 'LIKE', "%{$keyword}%");
            $query->orWhere('short_content', 'LIKE', "%{$keyword}%");
        } else {
            $query->where('brand_car', '=', "{$keyword}");
        }

        return $query->paginate(20);
    }
}
