<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Language;
use App\Models\OrderHistory;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\OrderStatusTranslation;
use Validator;

class OrderStatusController extends Controller
{
    //

    private $rules = [];

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'translates.*.name' => ['required', 'string', 'between:3,200'],
            'translates.*.locale' => ['required', 'exists:languages'],
            'sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
            'status' => ['boolean']
        ];
    }

    //
    public function index(Request $request)
    {
        if ($request->input('autocomplete')) {
            $order_statuses = OrderStatus::get()->map(function ($o) {
                return [
                    'id' => $o->id,
                    'name' => $o->translate->name
                ];
            });
        } else {
            $order_statuses = OrderStatus::with('translates')->get();

            foreach ($order_statuses as $order_status) {
                $this->repairTranslates($order_status);
            }
        }

        return response()->json(compact('order_statuses'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules, $this->messages());

        $order_status = new OrderStatus();

        $this->save($request, $order_status);

        return response()->json([
            'id' => $order_status->id,
            'text' => trans('form.result.success-created')
        ]);
    }

    public function update(Request $request, $id)
    {
        $order_status = OrderStatus::findOrFail($id);

        $this->rules['status'] = ['required', 'boolean', function ($attribute, $value, $fail) use ($request, $order_status) {
            if (!$request->input('status')) {
                if ($order_status->id === Setting::get('order_status_after_create')) {
                    $fail(trans('validation.custom.inactive_main_order_status_id'));
                } elseif (OrderHistory::where('order_status_id', $order_status->id)->first()) {
                    $fail(trans('validation.custom.order_status_used_in_order'));
                }
            }
        }];

        $request->validate($this->rules, $this->messages());

        $this->save($request, $order_status);

        return response()->json([
            'id' => $order_status->id,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function destroy($id)
    {
        $order_status = OrderStatus::findOrFail($id);

        Validator::make(['id' => $id], [
            'id' => [function ($attribute, $value, $fail) use ($order_status) {
                if ($order_status->id === Setting::get('order_status_after_create')) {
                    $fail(trans('validation.custom.inactive_main_order_status_id'));
                } elseif (OrderHistory::where('order_status_id', $order_status->id)->first()) {
                    $fail(trans('validation.custom.order_status_used_in_order'));
                }
            }]
        ])->validate();

        OrderStatus::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    private function save(Request $request, OrderStatus $order_status)
    {
        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new OrderStatusTranslation(
                [
                    'locale' => $translate['locale'],
                    'name' => $translate['name']
                ]
            );
        }

        $order_status->status = $request->input('status');

        $order_status->save();

        $order_status->translates()->delete();

        $order_status->translates()->saveMany($translations);
    }

    private function repairTranslates(&$model)
    {
        foreach (Language::getOnlyActive() as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new OrderStatusTranslation([
                        'name' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }
}
