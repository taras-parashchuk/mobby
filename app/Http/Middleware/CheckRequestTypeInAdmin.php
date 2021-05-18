<?php

namespace App\Http\Middleware;

use Closure;
use Route;

class CheckRequestTypeInAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (!\Auth::id() || !\Auth::user() ||  !\Auth::user()->isAdmin()) abort('404');

        if(strpos(Route::currentRouteName(), 'unisharp.lfm') !== false || strpos(\Request::route()->getActionMethod(), 'getQueueStatus') !== false  || strpos(Route::currentRouteName(), 'nova_poshta') !== false){
            return $next($request);
        }else if (!$request->ajax()) {
            return view('admin');
        }
        return $next($request);
    }
}
