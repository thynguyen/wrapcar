<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class SettingController extends Controller
{
    public function index(Request $request)
    {

        $setting = \App\Models\Settings::first();
        $data = array(
            'setting' => $setting,
            'config' => \App\Models\Config::first(),
            'brands' => \config('wrap.brands'),
        );

        return view('setting.index', $data);
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'brand' => 'required|max:255',
            'product' => 'required|max:255',
            'product_year' => 'required|numeric',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect('setting')
                        ->withErrors($validator)
                        ->withInput();
        }

        $setting = \App\Models\Settings::first();
        if ($setting === NULL) {
            $setting = new \App\Models\Settings();
        }
        $setting->brand_car = $request->get('brand');
        $setting->keyword = $request->get('product');
        $setting->product_year = $request->get('product_year');
        $setting->city = $request->get('city');
        $setting->hop_so = $request->get('hop_so');
        $setting->color = $request->get('color');
        $setting->status = $request->get('status');
        $setting->created_at = date('Y-m-d H:i:s');
        $setting->save();

        $config = \App\Models\Config::first();
        if ($config === NULL) {
            $config = new \App\Models\Config();
        }
        $config->key = 'email_send';
        $config->value = $request->get('email');
        $config->save();

        $request->session()->flash('success', 'Cập nhật data thành công');
        return redirect('setting');
    }
}