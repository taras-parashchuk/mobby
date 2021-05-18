<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\CategoryFilterAttribute;
use App\Models\Language;
use App\Models\ProductVariantAttributeValue;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Helpers\CustomValidation;

class AttributeController extends Controller
{

    use CustomValidation;

    private $rules;

    private $request;

    public function __construct()
    {
        $this->rules = [
            'group_id' => ['nullable'],
            'slug' => ['nullable', 'string', 'between:1,40', Rule::unique('attributes')],
            'sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
            'status' => ['boolean', 'required'],
            'translates.*.locale' => ['exists:languages'],
            'main_info_id' => ['nullable', 'exists:informations,id'],
            'translates.*.name' => ['required', 'max:160', Rule::unique('attribute_translations', 'name')],
            'translates.*.postfix' => ['nullable', 'max:16', 'string'],
            'translates.*.summary' => ['nullable', 'max:140', 'string'],

            'values.*.translates.*.locale' => ['exists:languages'],
            'values.*.translates.*.value' => 'bail|required|max:100',
            'values.*.image' => ['nullable', 'string', 'max:200'],
            'values.*.color' => ['nullable', 'string', 'max:9'],
            //'values.*.slug' => ['nullable', 'string', 'between:1,40', Rule::unique('attribute_values')],
            'values.*.sort_order' => ['bail', 'nullable', 'integer', 'digits_between:1,6'],
            'values.*.status' => ['boolean', 'required']
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
        if ($request->has('autocomplete')) {
            $attributes = Attribute::enabled()->get()->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->translate->name
                ];
            });
        } elseif ($request->has('with_translate')) {
            $attributes = Attribute::enabled()->get()->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->translate->name,
                    'translate' => $attribute->translate
                ];
            });
        } else {
            $attributes = Attribute::with('translates')
                ->orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))
                ->paginate($request->input('perPage'));

            $attributes->each(function($attribute){
               $this->repairTranslates($attribute);
            });
        }

        return response()->json(
            compact('attributes')
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->request = $request;

        //
        $request->validate($this->rules, $this->messages());

        $attribute = new Attribute();

        $default_trans_index = array_search(app()->getLocale(), array_column($request->input('translates'), 'locale'));

        if ($request->input('slug')) {
            $attribute->slug = $request->input('slug');
        } else {
            $attribute->slug = Str::slug($request->input('translates.' . $default_trans_index . '.name'), '-', app()->getLocale());
        }

        $attribute->sort_order = $request->input('sort_order', 0);

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new AttributeTranslation(
                [
                    'locale' => $translate['locale'],
                    'name' => $translate['name'],
                ]
            );
        }

        $attribute->status = true;

        $attribute->save();

        $attribute->translates()->saveMany($translations);

        return response()->json([
            'id' => $attribute->id,
            'slug' => $attribute->slug,
            'text' => trans('form.result.success-created')
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        //
        $attribute = Attribute::with(['translates'])->findOrFail($id);

        $this->repairTranslates($attribute);

        foreach ($attribute->values as $value) {
            $this->repairValueTranslates($value);
        }

        $attribute->values->each->append('filemanager_thumb');

        $attribute = $attribute->toArray();

        return response()->json(compact('attribute'));
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
        $attribute = Attribute::with('values')->findOrFail($id);

        $this->rules['slug'][3] = $this->rules['slug'][3]->ignore($id);

        $this->rules['translates.*.name'][2] = $this->rules['translates.*.name'][2]->where(function ($query) use ($id) {
            return $query->where('attribute_id', '<>', $id);
        });

        if ($request->input('group_id')) {
            $this->rules['group_id'] = ['exists:attribute_groups,id'];
        }

        $this->rules['status'] = ['required', 'boolean', function ($attribute, $value, $fail) use ($request, $id) {
            if (!$value && ProductVariantAttributeValue::where('attribute_id', $id)->count()) {
                $fail(trans('validation.custom.inactivate_attribute_in_variants'));
            }
        }];

        if($request->has('values')){
            foreach ($request->input('values') as $index => $attribute_value){

                $this->rules["values.$index.slug"][3] = ['nullable', 'string', 'between:1,40', Rule::unique('attribute_values')->ignore($attribute_value['id'])];

                $this->rules["values.$index.status"][2] = ['required', 'boolean', function ($attribute, $value, $fail) use ($request, $attribute_value) {
                    if (!$value && ProductVariantAttributeValue::where('attribute_value_id', $attribute_value['id'])->count()) {
                        $fail(trans('validation.custom.inactivate_attribute__value_in_variants'));
                    }
                }];

            }
        }

        $validator = \Validator::make($request->all(), $this->rules);

        $errors = $validator->errors();

        if(count($errors->messages())){
            abort(403, $errors->first());
        }

        $attribute->status = $request->input('status');

        $default_trans_index = array_search(app()->getLocale(), array_column($request->input('translates'), 'locale'));

        if ($request->input('slug')) {
            $attribute->slug = $request->input('slug');
        } else {
            $attribute->slug = Str::slug($request->input('translates.' . $default_trans_index . '.name'), '-', app()->getLocale());
        }

        $attribute->group_id = $request->input('group_id', 0);
        $attribute->sort_order = $request->input('sort_order', 0);
        $attribute->main_info_id = $request->input('main_info_id');

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new AttributeTranslation(
                [
                    'locale' => $translate['locale'],
                    'name' => $translate['name'],
                    'postfix' => $translate['postfix'],
                    'summary' => $translate['summary']
                ]
            );
        }

        $attribute->save();

        $attribute->translates()->delete();

        $attribute->translates()->saveMany($translations);

        $eAttributeValues = collect();

        if($request->has('values')){

            foreach ($request->input('values') as $index => $attribute_value){

                $eAttributeValue = AttributeValue::findOrNew($attribute_value['id']);

                $eAttributeValue->attribute_id = $id;

                $eAttributeValue->prefer_style = $attribute_value['prefer_style'];
                $eAttributeValue->image = $attribute_value['image'];

                if($attribute_value['slug']){
                    $eAttributeValue->slug = Str::slug(str_replace(['.', ','], '-', $attribute_value['slug']));
                }else{
                    $default_trans_index = array_search(app()->getLocale(), array_column($request->input("values.$index.translates"), 'locale'));

                    $eAttributeValue->slug = Str::slug(str_replace(['.', ','], '-', $request->input("values.$index.translates.$default_trans_index.value")), '-', app()->getLocale());
                }

                $eAttributeValue->sort_order = $attribute_value['sort_order'];
                $eAttributeValue->status = $attribute_value['status'];
                $eAttributeValue->color = (string)$attribute_value['color'];

                $eAttributeValueTranslates = [];

                foreach ($attribute_value['translates'] as $translate){
                    $eAttributeValueTranslate = new AttributeValueTranslation();

                    $eAttributeValueTranslate->locale = $translate['locale'];
                    $eAttributeValueTranslate->value = $translate['value'];

                    $eAttributeValueTranslates[] = $eAttributeValueTranslate;

                }

                $eAttributeValue->save();

                $eAttributeValues->push($eAttributeValue);

                $eAttributeValue->translates()->delete();
                $eAttributeValue->translates()->saveMany($eAttributeValueTranslates);

            }

            foreach ($attribute->values as $attribute_value){
                if(!$eAttributeValues->firstWhere('id', $attribute_value['id'])){
                    $attribute_value->delete();
                }
            }
        }

        return response()->json([
            'id' => $attribute->id,
            'slug' => $attribute->slug,
            'values' => $eAttributeValues->map(function($eAttributeValue){
                return [
                    'id' => $eAttributeValue->id,
                    'slug' => $eAttributeValue->slug
                ];
            }),
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
        if (ProductVariantAttributeValue::where('attribute_id', $id)->count()) {
            abort(422, trans('validation.custom.inactivate_attribute_in_variants'));
        } else {
            Attribute::findOrFail($id)->delete();

            return response()->json([
                'text' => trans('form.result.success-deleted')
            ]);
        }
    }

    private function repairTranslates(&$model)
    {
        foreach (Language::getOnlyActive() as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new AttributeTranslation([
                        'name' => '',
                        'postfix' => '',
                        'summary' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }

    private function repairValueTranslates(&$model)
    {
        foreach (Language::getOnlyActive() as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new AttributeValueTranslation([
                        'attribute_value_id' => $model->id,
                        'value' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }
}
