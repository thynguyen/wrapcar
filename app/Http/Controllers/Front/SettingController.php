<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Validator;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function __construct() {
        
    }

    public function index(Request $request)
    {
        $model = new \App\Models\Settings();
        $pagination = $model->getLists();

        $data = array();

        $data = $this->getSetting($data);

        $data['pagination'] = $pagination;

        return view('setting.index', $data);
    }

    public function edit(Request $request, $setting_id)
    {
        $setting = \App\Models\Settings::find($setting_id);
        if ($setting === null) {
            $request->session()->flash('error', 'Data không tồn tại');
            return redirect()->route('setting_index');
        }
        $model = new \App\Models\Settings();
        $pagination = $model->getLists();

        $data = array();

        $data = $this->getSetting($data);

        $data['setting'] = $setting;
        $data['pagination'] = $pagination;

        return view('setting.index', $data);
    }

    public function update(Request $request)
    {
//        if (!Auth::check()) {
//            return redirect()->route('home_index');
//        }
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'brand' => 'required|max:255',
            'product' => 'required|max:255',
            'product_year' => 'required|numeric',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->route('setting_index')
                        ->withErrors($validator)
                        ->withInput();
        }

        $setting = \App\Models\Settings::find($request->get('setting_id'));
        if ($setting === NULL) {
            $setting = new \App\Models\Settings();
        }

        $config = \App\Models\Config::where('user_id', Auth::id())->first();
        if ($config === null) {
            $config = new \App\Models\Config();
            $config->user_id = Auth::id();
        }
        $config->email = $request->get('email');
        $config->save();

        $setting->user_id = Auth::id();
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
        return redirect()->route('setting_index');
    }

    public function delete(Request $request, $setting_id)
    {
        if (!Auth::check()) {
            return redirect()->route('home_index');
        }
        $setting = \App\Models\Settings::find($setting_id);
        if ($setting === null) {
            $request->session()->flash('error', 'Data không tồn tại');
            return redirect()->route('setting_index');
        }
        $setting->delete();

        $request->session()->flash('success', 'Cập nhật data thành công');
        return redirect()->route('setting_index');
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

    protected function getSetting($data)
    {
        $config = \App\Models\Config::where('user_id', Auth::id())->first();

        $data['config'] = $config;
        $data['brands'] = \config('wrap.brands');
        $data['hop_so_list'] = \config('wrap.hop_so_list');
        $data['product_year_list'] = $this->getYear();
        $data['color_list'] = \config('wrap.color_list');
        $data['city_list'] = \config('wrap.city_list');

        return $data;
    }
}