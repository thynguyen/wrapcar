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
        $page = $request->get('page', 1); // Get the current page or default to 1, this is what you miss!
        $perPage = 20;
        $offset = ($page * $perPage) - $perPage;

        $keyword = $request->get('keyword');
        $time = $request->get('time');
        $city = $request->get('city');
        $timeVals = config('wrap.time_list_value');
        $timeVal = isset($timeVals[$time]) ? $timeVals[$time] : null;

        $content = new \App\Models\Contents();

        $arrExept = array();
        if (!empty($keyword)) {
            $execpts = $content->get_content_except($keyword, $timeVal, $city);
            if ($execpts->count()) {
                foreach ($execpts as $except) {
                    $arrExept[] = $except->id_string;
                }
            }
            $arrExept = explode(',', implode(',', $arrExept));
        }

        $result = $content->getContent($keyword, $timeVal, $city, $arrExept, $offset, $perPage);
        $total = $content->getTotal();

        $pagination = new LengthAwarePaginator(
                $result, 
                $total, 
                $perPage,
                $page,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );

        $data = array(
            'time' => $request->get('time'),
            'city' => $request->get('city'),
            'timeList' => config('wrap.time_list'),
            'citiList' => config('wrap.city_list'),
            'keyword' => $keyword,
            'pagination' => $pagination,
        );

        return view('search.index', $data);
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