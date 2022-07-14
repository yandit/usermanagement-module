<?php

namespace Modules\UserManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Native\Facades\Sentinel;

class UserManagementMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Sentinel::check();
        $route = $request->route()->getAction();                
        if ( (isset($route['as'])) ){
            $as = $route['as'];
            // dd($user->hasAccess(['admin.index']));
            if(!$user->hasAccess([$as])){
                abort(403, 'Forbidden');
            }                    
        } 

        return $next($request);
    }
}
