<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers{
        logout as performLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function login(Request $request) {

        $data = array('message' => '');

        if ($request->isMethod('POST')) {
            // validate the info, create rules for the inputs
            $rules = array(
                'email'    => 'required|email',
                'password' => 'required|alphaNum|min:3',
            );

            // run the validation rules on the inputs from the form
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect('login')
                            ->withErrors($validator)
                            ->withInput();
            }

            $loginUser = User::where('email', $request->get('email'))->first();
            if ($loginUser === NULL) {
                $data['message'] = 'Email hoặc mật khẩu không đúng';
            } else {
                if ($loginUser->status == 0) { 
                    $data['message'] = 'Tài khoản của bạn chưa được kích hoạt';
                } else {

                    // create our user data for the authentication
                    $userData = array(
                        'email'     => $request->get('email'),
                        'password'  => $request->get('password'),
                        'status'    => 1,
                        'role_id' => $loginUser->role_id
                    );

                    // attempt to do the login
                    if (Auth::attempt($userData)) {
                        if ($loginUser->role_id === 1) {
                            return redirect('admin/dashboard');
                        } else {
                            return redirect(route('home_index'));
                        }
                    } else {
                        $data['message'] = 'Email hoặc mật khẩu không đúng';
                    }
                }
            }
        }

        return view('auth/login', $data);
    }

    public function logout(Request $request)
    {
        $this->performLogout($request);

        return redirect(route('home_index'));
    }
}
