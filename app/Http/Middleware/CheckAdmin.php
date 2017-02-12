<?php 
namespace App\Http\Middleware;

use Closure, Auth;

class CheckAdmin
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user) {
            $role = config('wrap.roles');
            if ($user->role_id != $role['admin']) {
              return redirect()->route('home_index');
            }
            return $next($request);
        }
        return redirect()->route('auth_login');
//        return $next($request);
    }

}