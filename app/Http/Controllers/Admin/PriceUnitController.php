<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Language;
use App\Models\PriceUnit;
use App\Models\PriceUnitTranslation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PriceUnitController extends Controller
{
    //

    private $rules = [];


    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'translates.*.name' => ['required', 'string', 'between:1,200'],
            'translates.*.locale' => ['required', 'exists:languages'],
            'display' => ['required', 'boolean']
        ];
    }

    public function index(Request $request)
    {
        if ($request->input('autocomplete')) {
            $price_units = PriceUnit::enabled()->get()->map(function($price_unit){
                return [
                    'id' => $price_unit->id,
                    'name' => $price_unit->translate->name
                ];
            });
        } else {
            $price_units = PriceUnit::with('translates')->get();

            foreach ($price_units as $price_unit){
                $this->repairTranslates($price_unit);
            }
        }

        return response()->json(compact('price_units'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules, $this->messages());

        $price_unit = new PriceUnit();

        $this->save($request, $price_unit);

        return response()->json([
            'id' => $price_unit->id,
            'text' => trans('form.result.success-created')
        ]);
    }

    public function update(Request $request, $id)
    {
        $price_unit = PriceUnit::findOrFail($id);

        $request->validate($this->rules, $this->messages());

        $this->save($request, $price_unit);

        return response()->json([
            'id' => $price_unit->id,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function destroy($id)
    {
        PriceUnit::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    private function save(Request $request, PriceUnit $price_unit)
    {
        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new PriceUnitTranslation(
                [
                    'locale' => $translate['locale'],
                    'name' => $translate['name']
                ]
            );
        }

        $price_unit->status = $request->input('status');

        $price_unit->display = $request->input('display');

        $price_unit->save();

        $price_unit->translates()->delete();

        $price_unit->translates()->saveMany($translations);
    }

    private function repairTranslates(&$model)
    {
        foreach (Language::getOnlyActive() as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new PriceUnitTranslation([
                        'name' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }
}
