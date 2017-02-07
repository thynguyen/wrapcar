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
            $parts = $this->seoUrl($keyword);
            $parts = explode('-', $parts);
//            var_dump($parts);exit;
            if (count($parts)) {
                $query->where(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('brand_car', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('code_car_site', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('color', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('km_run', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('product_year', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('price', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('contact', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('phone', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('city', 'LIKE', "%{$part}%");
                    }
                });
                $query->orWhere(function($query) use ($parts) {
                    foreach ($parts as $part) {
                        $query->where('short_content', 'LIKE', "%{$part}%");
                    }
                });
            }
        } else {
            $keyword = 'null';
            $query->where('brand_car', '=', "{$keyword}");
        }

        return $query->paginate(20);
    }

    public function getBookAuto($setting)
    {
        $query = DB::table($this->table);
        $query->where('brand_car', 'LIKE', "%{$setting->brand_car}%");
        $query->where('brand_car', 'LIKE', "%{$setting->keyword}%");
        $query->where(function($query) use ($setting) {
            $query->where('product_year', 'LIKE', "%{$setting->product_year}%");
            $query->orWhere('brand_car', 'LIKE', "%{$setting->product_year}%");
        });
        if (!empty($setting->city)) {
            $query->where(function($query) use ($setting) {
                $query->where('city', 'LIKE', "%{$setting->city}%");
                $query->orWhere('contact', 'LIKE', "%{$setting->city}%");
            });
        }
        if (!empty($setting->color)) {
            $query->where(function($query) use ($setting) {
                $query->where('color', 'LIKE', "%{$setting->color}%");
                $query->orWhere('short_content', 'LIKE', "%{$setting->color}%");
            });
        }
        if (!empty($setting->hop_so)) {
            $query->where('short_content', 'LIKE', "%{$setting->hop_so}%");
        }
        $query->where('created_at', '>', "{$setting->created_at}");

        return $query->get();
    }
    
    public function seoUrl($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }
}
