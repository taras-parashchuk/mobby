<?php

namespace App\Http\Controllers;

use App\Helpers\HelperFunction;
use App\Models\Information;
use Illuminate\Http\Request;
use SEO;

class InformationController extends Controller
{
    //
    public function show($slug, $id)
    {
        $information = Information::where([
            ['slug', $slug],
            ['status', '1']
        ])->findOrFail($id);

        SEO::setTitle($information->translate->meta_title ?: $information->translate->name);
        SEO::setDescription($information->translate->meta_description ?: $information->translate->name);

        return view('pages.information', compact('information'));
    }
}
