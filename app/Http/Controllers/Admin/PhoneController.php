<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class PhoneController extends Controller
{
    /**
     * Edit user
     * @param Request $request
     * @param Integer $id user id
     * @return type
     */
    public function index(Request $request) {

        $phone = \App\Models\PhoneExcept::first();
        if ($phone === null) {
            $phone = new \App\Models\PhoneExcept();
            $phone->key = 'phone_except';
        }

        if ($request->isMethod('POST')) {

            $rules =  array(
                'phone'     => 'required',
            );

            // run the validation rules on the inputs from the form
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->route('admin_phone_black_list')
                            ->withErrors($validator)
                            ->withInput();
            }
            $phones = $request->get('phone');
            $phones = explode(',', $phones);
            $phones = array_filter($phones);
            $arr = array_count_values($phones);
            $dup = array();
            foreach ($arr as $key => $item) {
                if (!empty($item) && $item > 1) {
                    $dup[] = $key;
                }
            }
            $phone->content = $request->get('phone');
            $phone->created_at = date('Y-m-d H:i:s');
            $phone->save();
            $request->session()->flash('success', 'Cập nhật thành công');
            if (count($dup)) {
                $request->session()->flash('warning', 'Phone bị trùng: ' . implode(',', $dup));
            }
            return redirect(route('admin_phone_black_list'));
        }

        return view('admin/phone/form', [
            'phone' => $phone,
        ]);
    }
}
