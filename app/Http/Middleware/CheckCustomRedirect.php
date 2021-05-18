<?php

namespace App\Http\Middleware;

use App\Models\RedirectSource;
use Closure;

class CheckCustomRedirect
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
        if(!$request->ajax()){
            $redirects = RedirectSource::with('target')->get();

            $current_url = $request->fullUrl();

            if($redirectInfo = $redirects->firstWhere('url', $current_url)){
                return redirect()->to($redirectInfo->target->url);
            }else{
                return $next($request);
            }
        }else{
            return $next($request);
        }
    }
}
