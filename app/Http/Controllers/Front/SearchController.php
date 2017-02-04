<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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
        $data = array();
//        $pagination = null;

//        $page = $request->get('page', 1); // Get the current page or default to 1, this is what you miss!
//        $perPage = 10;
//        $offset = ($page * $perPage) - $perPage;

        $keyword = $request->get('keyword');
        $content = new \App\Models\Contents();
        $pagination = $content->getContent($keyword);


        return view('search.index', ['keyword' => $keyword, 'pagination' => $pagination]);
    }
}