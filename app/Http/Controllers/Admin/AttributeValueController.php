<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Language;
use App\Models\ProductVariantAttributeValue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AttributeValueController extends Controller
{

    private $rules;

    private $request;

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'attribute_id' => ['exists:attributes,id'],
            'image' => ['nullable', 'string', 'max:200'],
            'color' => ['nullable', 'string', 'max:10'],
            'slug' => ['nullable', 'string', 'between:1,40', Rule::unique('attribute_values')],
            'sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
            'translates.*.locale' => ['exists:languages'],
            'translates.*.value' => ['required', 'max:100'],
            'status' => ['boolean', 'required']
        ];
    }

    public function index(Request $request)
    {
        if ($request->input('autocomplete')) {

            $values = AttributeValue::enabled()->whereHas('translates', function ($query) use ($request) {
                $query->where('value', 'like', '%' . $request->input('filter_name') . '%');
            })->get()->map(function($attribute_value){
                return [
                    'id' => $attribute_value->id,
                    'attribute_id' => $attribute_value->attribute_id,
                    'value' => $attribute_value->translate->value
                ];
            });

        }elseif($request->has('attribute_id') && $request->has('with_translates')){
            $values = AttributeValue::with('translates')->where('attribute_id', $request->input('attribute_id'))->get();
        }else{
            $values = AttributeValue::get();
        }

        return compact('values');

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

        $value = new AttributeValue();

        $this->save($request, $value);

        return response()->json([
            'id' => $value->id,
            'slug' => $value->slug,
            'text' => trans('form.result.success-created')
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

        $this->request = $request;

        $this->rules['slug'][3] = $this->rules['slug'][3]->ignore($id);

        $this->rules['status'] = ['required', 'boolean', function ($attribute, $value, $fail) use ($request, $id) {
            if (!$value && ProductVariantAttributeValue::where('attribute_value_id', $id)->count()) {
                $fail(trans('validation.custom.inactivate_attribute__value_in_variants'));
            }
        }];

        $request->validate($this->rules, $this->messages());

        $value = AttributeValue::findOrFail($id);

        $value->translates()->delete();

        $this->save($request, $value);

        return response()->json([
            'id' => $value->id,
            'slug' => $value->slug,
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

        if (ProductVariantAttributeValue::where('attribute_value_id', $id)->count()) {
            abort(422, trans('validation.custom.inactivate_attribute__value_in_variants'));
        } else {

            AttributeValue::findOrFail($id)->delete();

            return response()->json([
                'text' => trans('form.result.success-deleted')
            ]);
        }
    }

    public function save(Request $request, AttributeValue &$attributeValue)
    {

        Attribute::findOrFail($request->input('attribute_id'));

        $default_trans_index = array_search(app()->getLocale(), array_column($request->input('translates'), 'locale'));

        $attributeValue->attribute_id = $request->input('attribute_id');

        if ($request->input('slug')) {
            $attributeValue->slug = $request->input('slug');
        } else {
            $attributeValue->slug = Str::slug($request->input('translates.' . $default_trans_index . '.value'), '-', app()->getLocale());
        }

        $attributeValue->sort_order = $request->input('sort_order', 0);

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new AttributeValueTranslation(
                [
                    'locale' => $translate['locale'],
                    'value' => $translate['value'],
                ]
            );
        }

        $attributeValue->status = $request->input('status');

        $attributeValue->save();

        $attributeValue->translates()->saveMany($translations);

    }
}
