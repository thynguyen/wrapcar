<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contents extends Model
{
    //Map to table in database
    protected $table='contents';
    public $total;

    public function getTotal()
    {
        return $this->total;
    }
    public function setTotal($total)
    {
        $this->total = $total;
    }
    public function getContent($keyword, $timeVal, $city, $offset, $limit, $isOwner = 1)
    {
        $query = DB::table($this->table)
            ->select('*')
            ->where(function($query) use ($keyword) {
            if (!empty($keyword)) {
                $parts = $this->seoUrl($keyword);
                $parts = explode('-', $parts);
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
        });
        if (!empty($timeVal)) {
            $query->where('created_at', '>=', date('Y-m-d H:i:s', strtotime($timeVal)));
        }
        if (!empty($city)) {
            $query->where('city', 'LIKE', "%{$city}%");
        }

        $query->where('is_owner', '=', $isOwner);

        $total = $query->count();
        if (!$total) {
            return array();
        }
        $this->setTotal($total);

        $query->unionAll($this->getContentNotOwner($keyword, $timeVal, $city));

        $query->skip($offset)->take($limit);

        return $query->get();
//        return $query->paginate(20);
    }

    public function getContentNotOwner($keyword, $timeVal, $city)
    {
        $query = DB::table($this->table)
            ->select('*')
            ->where(function($query) use ($keyword) {
            if (!empty($keyword)) {
                $parts = $this->seoUrl($keyword);
                $parts = explode('-', $parts);
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
        });
        if (!empty($timeVal)) {
            $query->where('created_at', '>=', date('Y-m-d H:i:s', strtotime($timeVal)));
        }
        if (!empty($city)) {
            $query->where('city', 'LIKE', "%{$city}%");
        }
        $query->where('is_owner', '=', 0);

        return $query;
    }

    public function getBookAuto($setting)
    {
        $query = DB::table($this->table);
        $query->select('link');

        $query->where('brand_car', 'LIKE', "%{$setting['brand_car']}%");
        $query->where('brand_car', 'LIKE', "%{$setting['keyword']}%");
        $query->where(function($query) use ($setting) {
            $query->where('product_year', 'LIKE', "%{$setting['product_year']}%");
            $query->orWhere('brand_car', 'LIKE', "%{$setting['product_year']}%");
        });
        if (!empty($setting['city'])) {
            $query->where(function($query) use ($setting) {
                $query->where('city', 'LIKE', "%{$setting['city']}%");
                $query->orWhere('contact', 'LIKE', "%{$setting['city']}%");
            });
        }
        if (!empty($setting['color'])) {
            $query->where(function($query) use ($setting) {
                $query->where('color', 'LIKE', "%{$setting['color']}%");
                $query->orWhere('color', '<>', '');
                $query->orWhere('short_content', 'LIKE', "%{$setting['color']}%");
            });
        }
        if (!empty($setting['hop_so'])) {
            $query->where('short_content', 'LIKE', "%{$setting['hop_so']}%");
        }
        $query->where('created_at', '>=', "{$setting['updated_at']}");

        return $query->get();
    }

    public function get_content_except($keyword, $timeVal, $city)
    {
        $query = DB::table($this->table)
            ->select("phone", DB::raw("COUNT(phone) as total"), DB::raw("group_concat(id separator ',') AS id_string"))
            ->where(function($query) use ($keyword) {
            if (!empty($keyword)) {
                $parts = $this->seoUrl($keyword);
                $parts = explode('-', $parts);
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
            }
        });
//        if (!empty($timeVal)) {
//            $query->where('created_at', '>=', date('Y-m-d H:i:s', strtotime($timeVal)));
//        }
        if (!empty($city)) {
            $query->where('city', 'LIKE', "%{$city}%");
        }
        $query->where(DB::raw("TRIM(phone)"), '!=', '0000000000');
//        $query->where(DB::raw('LENGTH(trim(phone))'), '>', 7);
//        $query->where('phone', 'REGEXP', '^([0-9]+\,*)+');
        $query->where(DB::raw('trim(phone)'), '!=', '');
        $query->where(DB::raw('trim(phone)'), '!=', ',');
        $query->groupBy('phone');
        $query->having('total', '>', 2);

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
