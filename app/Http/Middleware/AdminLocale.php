<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;

class AdminLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \App::setLocale(Setting::get('admin_language'));

        return $next($request);
    }
}
