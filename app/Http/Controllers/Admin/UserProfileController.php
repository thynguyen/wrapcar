<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserProfileController extends Controller
{
    /**
     * Edit user
     * @param Request $request
     * @param Integer $id user id
     * @return type
     */
    public function edit(Request $request) {

        $currentUser = Auth::user();

        $user = User::find($currentUser->id);
        if ($user == null) {
            $request->session()->flash('error_message', trans('user.no_user'));
            return redirect()->route('admin_dashboard');
        }

        if ($request->isMethod('POST')) {

            $rules =  array(
                'name'     => 'required|max:255',
                'password'    => 'max:255',
                'confirm_password'    => 'max:255|same:password',
            );

            // run the validation rules on the inputs from the form
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->route('admin_profile_edit')
                            ->withErrors($validator)
                            ->withInput();
            }

            $user->name = $request->get('name');
            if ($request->get('password') !== NULL && $request->get('password') !== '') {
                $user->passord = bcrypt($request->get('password'));
            }
            $user->save();
            $request->session()->flash('success', 'Cập nhật thành công');
            return redirect(route('admin_profile_edit'));
        }

        return view('admin/profile/form', [
            'user' => $user,
        ]);
    }
}
