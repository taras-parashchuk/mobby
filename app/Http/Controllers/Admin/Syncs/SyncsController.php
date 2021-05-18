<?php

namespace App\Http\Controllers\Admin\Syncs;

use App\Models\Syncs\ExternalApi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SyncsController extends Controller
{
    /**
     * Get all external api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $externalsApi = ExternalApi::all();

        return response($externalsApi);
    }
}
