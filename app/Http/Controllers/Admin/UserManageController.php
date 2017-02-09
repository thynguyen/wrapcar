<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

use App\Models\User;

class UserManageController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::getList();

        return view('admin/user/list', [
          'users' => $users,
        ]);
    }

    /**
     * Create user
     * @param Request $request
     * @param Integer $id user id
     * @return type
     */
    public function add(Request $request) {
        if ($request->isMethod('POST')) {

            $rules =  array(
                'name'     => 'required|max:255',
                'email'     => 'required|max:255|unique:users,email',
                'password'    => 'required|max:255',
                'confirm_password'    => 'required|max:255|same:password',
                'roles'  => 'required|exists:roles,id',
                'status'       => 'required|max:1|in:0,1',
            );

            // run the validation rules on the inputs from the form
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->route('admin_user_create')
                            ->withErrors($validator)
                            ->withInput();
            }

            $user = new User();
            $user->name = $request->get('name');
            $user->email = $request->get('email');
            $user->role_id = $request->get('roles');
            $user->status = $request->get('status');
            $user->password = bcrypt($request->get('password'));
            $user->save();

            $request->session()->flash('success', 'Thành công');
            return redirect()->route('admin_user_manage');
        }

        return view('admin/user/form', [
            'roles' => $this->_getRoles(),
            'action' => route('admin_user_create'),
        ]);
    }

    /**
     * Edit user
     * @param Request $request
     * @param Integer $id user id
     * @return type
     */
    public function edit(Request $request) {
        $userId = $request->get('id');
        $user = User::find($userId);
        if ($user == null) {
            $request->session()->flash('error_message', 'Data not found');
            return redirect()->route('admin_user_manage');
        }

        if ($request->isMethod('POST')) {

            $rules =  array(
                'name'     => 'required|max:255',
                'password'    => 'max:255',
                'confirm_password'    => 'max:255|same:password',
                'roles'  => 'required|exists:roles,id',
                'status'       => 'required|max:1|in:0,1',
            );

            // run the validation rules on the inputs from the form
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->route('admin_user_edit', array('id' => $user->id))
                            ->withErrors($validator)
                            ->withInput();
            }

            $user->name = $request->get('name');
            $user->role_id = $request->get('roles');
            $user->status = $request->get('status');
            if ($request->get('password') !== NULL && $request->get('password') !== '') {
                $user->password = bcrypt($request->get('password'));
            }
            $user->save();
            $request->session()->flash('success', 'Thành công');
            return redirect()->route('admin_user_manage');
        }

        return view('admin/user/form', [
            'user' => $user,
            'roles' => $this->_getRoles(),
            'action' => route('admin_user_edit', array('id' => $user->id))
        ]);
    }

    /**
     * Delete user
     * @param Request $request
     * @param Integer $id user id
     * @return type
     */
    public function delete(Request $request) {
        $userId = $request->get('user_id');
        $user = User::find($userId);
        if ($user == null) {
            $request->session()->flash('success_message', 'Data not found');
            return redirect()->route('admin_user_manage');
        }

        $user->delete();
        $request->session()->flash('success', 'Thành công');

        return redirect()->route('admin_user_manage');
    }

    private function _getRoles()
    {
        $roles = \App\Models\Role::all(array('id', 'name'));

        return $roles;
    }
}
