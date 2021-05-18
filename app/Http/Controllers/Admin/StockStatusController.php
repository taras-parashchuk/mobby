<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Language;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockStatusTranslation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StockStatus;

class StockStatusController extends Controller
{
    private $rules = [];

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'translates.*.title' => ['required', 'string', 'between:3,200'],
            'translates.*.locale' => ['required', 'exists:languages'],
            'status' => ['boolean']
        ];
    }

    //
    public function index(Request $request)
    {
        if ($request->input('autocomplete')) {
            $stock_statuses = StockStatus::enabled()->get()->map(function($stock_status){
                return [
                    'id' => $stock_status->id,
                    'title' => $stock_status->translate->title
                ];
            });
        } else {
            $stock_statuses = StockStatus::with('translates')->get();

            foreach ($stock_statuses as $stock_status){
                $this->repairTranslates($stock_status);
            }
        }

        return response()->json(compact('stock_statuses'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules, $this->messages());

        $stock_status = new StockStatus();

        $this->save($request, $stock_status);

        return response()->json([
            'id' => $stock_status->id,
            'text' =>  trans('form.result.success-created')
        ]);
    }

    public function update(Request $request, $id)
    {
        $stock_status = StockStatus::findOrFail($id);

        $request->validate($this->rules, $this->messages());

        if(!$request->input('status')){
            Product::where('stock_status_id', $id)->update(['stock_status_id' => null]);
        }

        $this->save($request, $stock_status);

        return response()->json([
            'id' => $stock_status->id,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function destroy($id)
    {
        StockStatus::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    private function save(Request $request, StockStatus $stock_status)
    {
        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new StockStatusTranslation(
                [
                    'locale' => $translate['locale'],
                    'title' => $translate['title']
                ]
            );
        }

        $stock_status->status = $request->input('status');

        $stock_status->save();

        $stock_status->translates()->delete();

        $stock_status->translates()->saveMany($translations);
    }

    private function repairTranslates(&$model)
    {
        foreach (Language::getOnlyActive() as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new StockStatusTranslation([
                        'title' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }
}
