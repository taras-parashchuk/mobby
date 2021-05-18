<?php

namespace App\Http\Middleware;

use App\Models\Syncs\ExternalApi;
use Closure;
use Illuminate\Validation\ValidationException;

class CheckExternalApiStatus
{
    public function handle($request, Closure $next)
    {
        $externalCode = explode('/', request()->route()->uri);

        $externalApi = ExternalApi::where('code', $externalCode[2])->first();

        if ($externalApi->status == 0) {
            throw ValidationException::withMessages([
                trans('validation.custom.external_api_not_connected'),
            ]);
        }

        return $next($request);
    }
}
