<?php

namespace App\Http\Controllers\Admin;

use App\Models\RedirectSource;
use App\Models\RedirectTarget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class RedirectController extends Controller
{
    private $rules = [
        'redirects.*.url' => ['required', 'url'],
        'redirects.*.sources.*.url' => ['required','url']
    ];

    //
    public function index()
    {
        return RedirectTarget::with('sources')->get();
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);

        RedirectTarget::get()->each->delete();

        foreach ($request->input('redirects.*') as $redirect){

            $redirectTarget = new RedirectTarget();
            $redirectTarget->url =$redirect['url'];
            $redirectTarget->save();

            $sources = [];

            foreach ($redirect['sources'] as $source){
                $redirectSource = new RedirectSource();
                $redirectSource->url = $source['url'];

                $sources[] = $redirectSource;
            }

            $redirectTarget->sources()->saveMany($sources);
        }


        return response()->json([
            'text' => trans('form.result.success-created')
        ]);

    }
}
