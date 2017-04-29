<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('search.index');
    }
    public function testPage(Request $request)
    {
        $page = $request->get('page');
        $data = array('page' => $page);
        return view('search.test', $data);
    }
}