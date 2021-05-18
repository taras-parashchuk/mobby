<?php

namespace App\Http\Middleware;

use App\Models\Syncs\ExternalApi;
use Closure;

class ExternalApiAuth
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
        $externalCode = explode('/', request()->route()->uri);

        $externalApi = ExternalApi::where('code', $externalCode[2])->first();

        if (is_null($externalApi->login) || is_null($externalApi->password)) {
            return redirect()->route('externals-api');
        }

        return $next($request);
    }
}
