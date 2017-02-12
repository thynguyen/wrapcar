<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Validator;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index_bk(Request $request)
    {
        $data = array();
        $pagination = null;

        $page = $request->get('page', 1); // Get the current page or default to 1, this is what you miss!
        $perPage = 10;
        $offset = ($page * $perPage) - $perPage;

        $keyword = $request->get('keyword');

        if (!empty($keyword)) {
            $options = array(
                'hostname' => 'localhost',
                'port'     => 8983,
                'path'     => 'solr/admin',
            );

            $client = new \SolrClient($options);

            $query = new \SolrQuery();

            $query->setQuery("product_year:{$keyword}");

//            $query->setStart(0);
//
            $query->setRows(20);

            $query_response = $client->query($query);

            $response = $query_response->getResponse();

            $total = isset($response['response']['numFound']) ? $response['response']['numFound'] : 0;
            $results = isset($response['response']['docs']) ? $response['response']['docs'] : null;
            $data = (!empty($results)) ? $results : array();

            // Get only the items you need using array_slice
            $itemsForCurrentPage = array_slice($data, $offset, $perPage, true);

            $pagination = new LengthAwarePaginator(
                $itemsForCurrentPage, 
                $total, 
                $perPage,
                $page,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        }
        return view('search.index', ['keyword' => $keyword, 'pagination' => $pagination]);
    }

    public function index(Request $request)
    {
//        $pagination = null;

//        $page = $request->get('page', 1); // Get the current page or default to 1, this is what you miss!
//        $perPage = 10;
//        $offset = ($page * $perPage) - $perPage;

        $keyword = $request->get('keyword');
        $content = new \App\Models\Contents();
        $pagination = $content->getContent($keyword);

        $data = array(
            'keyword' => $keyword,
            'pagination' => $pagination,
        );
        $data = array_merge($data, $this->getSetting($data));

        return view('search.index', $data);
    }

    public function setting(Request $request)
    {
        if (!Auth::check()) {
            return redirect(route('home_search'));
        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'brand' => 'required|max:255',
            'product' => 'required|max:255',
            'product_year' => 'required|numeric',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect('search')
                        ->withErrors($validator)
                        ->withInput();
        }

        $setting = \App\Models\Settings::first();
        if ($setting === NULL) {
            $setting = new \App\Models\Settings();
        }
        $setting->user_id = Auth::id();
        $setting->email = $request->get('email');
        $setting->brand_car = $request->get('brand');
        $setting->keyword = $request->get('product');
        $setting->product_year = $request->get('product_year');
        $setting->city = $request->get('city');
        $setting->hop_so = $request->get('hop_so');
        $setting->color = $request->get('color');
        $setting->status = $request->get('status');
        $setting->created_at = date('Y-m-d H:i:s');
        $setting->save();

        $request->session()->flash('success', 'Cập nhật data thành công');
        return redirect('search');
    }

    protected function getSetting($data)
    {
        $setting = \App\Models\Settings::first();
//        $data['setting'] = $setting;
//        $data['config'] = \App\Models\Config::first();
        $data['brands'] = \config('wrap.brands');
        $data['hop_so_list'] = \config('wrap.hop_so_list');
        $data['product_year_list'] = $this->getYear();
        $data['color_list'] = \config('wrap.color_list');
        $data['city_list'] = \config('wrap.city_list');

        return $data;
    }

    protected function getYear()
    {
        $arr = array('' => '--Năm sản xuất--');
        $year = date('Y');

        for ($i = $year; $i > 1989; $i--)
        {
            $arr[$i] = $i;
        }
        return $arr;
    }
}