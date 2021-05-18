<?php

namespace App\Http\Controllers\Admin;


use App\Models\Language;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'locale' => [Rule::unique('languages'), 'required', 'string'],
            'name' => [Rule::unique('languages'), 'required', 'string'],
            'sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if ($request->input('filter_active')) {
            $languages = Language::where('status', 1)->get();
        } else {
            $languages = Language::all();
        }

        return response()->json(compact('languages'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate($this->rules);

        $language = new Language;

        $this->save($request, $language);

        return response()->json([
            'id' => $language->id,
            'text' =>  trans('form.result.success-created')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $this->rules['id'] = ['exists:languages'];

        $this->rules['locale'][0] = $this->rules['locale'][0]->ignore($id);
        $this->rules['name'][0] = $this->rules['name'][0]->ignore($id);

        $this->rules['status'] = ['required', 'boolean', function ($attribute, $value, $fail) use ($request) {
            if (!$request->input('status') && in_array($request->input('locale'), [Setting::get('site_language'), Setting::get('admin_language')]) ) {
                $fail(trans('validation.custom.inactive_main_language'));
            }
        }];

        $request->validate($this->rules);

        $language = Language::findOrFail($id);

        $this->save($request, $language);

        return response()->json([
            'id' => $language->id,
            'text' => trans('form.result.success-updated')
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
        Language::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    public function save(Request $request, Language &$language)
    {
        $language->name = $request->input('name');
        $language->locale = $request->input('locale');
        $language->sort_order = $request->input('sort_order', 0);
        $language->status = $request->input('status');
        $language->show_on_site = $request->input('show_on_site');
        $language->index = $request->input('index');

        $language->save();
    }

    public function translate()
    {
        return trans('admin');
    }
}
