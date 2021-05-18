<?php

namespace App\Http\Controllers\Admin;

use App\Models\AttributeGroup;
use App\Models\AttributeGroupTranslation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AttributeGroupController extends Controller
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'translates.*.name' => ['string', 'between:3,200'],
            'sort_order' => ['nullable','integer', 'digits_between:1,6'],
            'translates.*.locale' => ['exists:languages']
        ];
    }

    //
    public function index(Request $request)
    {
        if($request->input('autocomplete')){
            $attributes_groups = AttributeGroup::get()->map(function($attribute_group){
                return [
                    'id' => $attribute_group->id,
                    'name' => $attribute_group->translate->name,
                ];
            });
        }else{
            $attributes_groups = AttributeGroup::with('translates')->get();
        }

        return response()->json(compact('attributes_groups'));
    }

    public function update(Request $request, $id)
    {
        $this->rules['id'] = ['exists:attribute_groups'];

        $request->validate($this->rules);

        $attribute_group = AttributeGroup::findOrFail($id);

        $attribute_group->translates()->delete();

        $this->save($attribute_group, $request);

        return response()->json([
            'id' => $attribute_group->id,
            'text' => [
                'success_updated' => trans('form.result.success-updated')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);

        $attribute_group = new AttributeGroup();

        $this->save($attribute_group, $request);

        return response()->json([
            'id' => $attribute_group->id,
            'text' => [
                'success_created' => trans('form.result.success-created')
            ]
        ]);
    }

    public function destroy($id)
    {
        //
        AttributeGroup::findOrFail($id)->delete();

        return response()->json([
            'text' => ['success_deleted' => trans('form.result.success-deleted')]
        ]);
    }

    private function save(AttributeGroup &$attributeGroup, Request $request)
    {
        $attributeGroup->sort_order =  $request->input('sort_order', 0);

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new AttributeGroupTranslation(
                [
                    'name' => $translate['name'],
                    'locale' => $translate['locale']
                ]
            );
        }

        $attributeGroup->save();

        $attributeGroup->translates()->delete();

        $attributeGroup->translates()->saveMany($translations);
    }
}
