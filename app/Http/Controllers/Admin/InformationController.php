<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Article;
use App\Models\Information;
use App\Models\InformationTranslation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InformationController extends Controller
{
    private $rules = [];

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'translates.*.name' => ['required', 'string', 'max:200'],
            'translates.*.description' => ['nullable'],
            'translates.*.meta_title' => ['nullable', 'string', 'max:200'],
            'translates.*.meta_description' => ['nullable', 'string', 'max:200'],
            'translates.*.meta_keywords' => ['nullable', 'string', 'max:200'],
            'translates.*.locale' => ['required', 'exists:languages'],
            'image' => ['nullable','string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
            'status' => ['boolean'],
            'in_top' => ['boolean'],
            'in_bottom' => ['boolean'],
        ];
    }

    //
    public function index(Request $request)
    {
        if ($request->input('autocomplete')) {
            $informations = Information::enabled()->select('id')->get()->map(function($information){
                return [
                    'id' => $information->id,
                    'name' => $information->translate->name
                ];
            });
        } else {

            $informations = Information::with('translates')
                ->orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))
                ->paginate($request->input('perPage'));

        }

        return response()->json(compact('informations'));
    }

    public function edit($id)
    {
        $information = Information::with('translates')->without('translate')->findOrFail($id);

        $information->append('filemanager_thumb');

        $this->repairTranslates($information);

        return $information;
    }

    private function repairTranslates(&$model)
    {
        foreach (config('settings.languages') as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new InformationTranslation([
                        'name' => '',
                        'description' => '',
                        'meta_title' => '',
                        'meta_description' => '',
                        'meta_keywords' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $this->rules['slug'] = ['required', 'string', 'between:1,30'];

        $request->validate($this->rules, $this->messages());

        $information = Information::findOrFail($id);

        $information->image = $request->input('image');
        $information->in_top = $request->input('in_top');
        $information->in_bottom = $request->input('in_bottom');
        $information->status = $request->input('status');
        $information->slug = $request->input('slug');
        $information->sort_order = $request->input('sort_order');

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new InformationTranslation(
                [
                    'name' => $translate['name'],
                    'description' => $translate['description'],
                    'meta_title' => $translate['meta_title'],
                    'meta_description' => $translate['meta_description'],
                    'meta_keywords' => $translate['meta_keywords'],
                    'locale' => $translate['locale']
                ]
            );
        }

        $information->save();

        $information->translates()->delete();

        $information->translates()->saveMany($translations);

        return response()->json([
            'id' => $information->id,
            'href' => $information->href,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function store(Request $request)
    {
        //

        $this->rules['slug'] = ['nullable'];

        $request->validate($this->rules, $this->messages());

        $information = new Information();

        $information->in_top = $request->input('in_top');
        $information->in_bottom = $request->input('in_bottom');
        $information->status = $request->input('status');
        $information->sort_order = $request->input('sort_order');

        $default_trans_index = array_search(app()->getLocale(), array_column($request->input('translates'), 'locale'));

        $information->slug = Str::slug($request->input('translates.' . $default_trans_index . '.name'), '-', app()->getLocale());

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new InformationTranslation(
                [
                    'name' => $translate['name'],
                    'locale' => $translate['locale']
                ]
            );
        }

        $information->save();

        $information->translates()->saveMany($translations);

        return response()->json([
            'id' => $information->id,
            'href' => $information->href,
            'text' => trans('form.result.success-created')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Information::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }
}
