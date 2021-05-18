<?php

namespace App\Http\Controllers\Admin;

use App\Models\Currency;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{

    private $rules;

    public function __construct()
    {
        $this->rules = [
            'name' => ['required', 'string', 'max:200', Rule::unique('currencies')],
            'code' => ['required', 'string', 'max:10', Rule::unique('currencies')],
            'symbol' => ['required', 'string', 'max:25'],
            'format' => ['required', 'string', 'max:50'],
            'exchange_rate' => ['required', 'numeric', function ($input) {
                return $input > 0;
            }],
            'active' => ['boolean']

        ];
    }

    //
    public function index(Request $request)
    {
        $currencies = [];

        if ($request->input('autocomplete')) {
            foreach (currency()->getActiveCurrencies() as $currency) {
                $currencies[] = $currency;
            };
        } else {
            foreach (currency()->getCurrencies() as $currency) {
                $currencies[] = $currency;
            };
        }

        return response()->json(compact('currencies'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules);

        if (currency()->create([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'symbol' => $request->input('symbol'),
            'format' => $request->input('format'),
            'exchange_rate' => $request->input('exchange_rate'),
            'active' => $request->input('active'),
        ])) {

            return response()->json([
                'id' => \DB::table('currencies')->where('code', $request->input('code'))->first()->id,
                'text' => trans('form.result.success-created')
            ]);
        }
    }

    public function update(Request $request, $id)
    {

        $currency = Currency::findOrFail($id);

        $this->rules['name'][3] = $this->rules['name'][3]->ignore($id);
        $this->rules['code'][3] = $this->rules['code'][3]->ignore($id);


        $this->rules['active'] = ['required', 'boolean', function ($attribute, $value, $fail) use ($request, $currency) {
            if (!$request->input('active') && $currency->code === Setting::get('currency')) {
                $fail(trans('validation.custom.inactive_main_currency'));
            }
        }];


        $request->validate($this->rules);

        $currency->name = $request->input('name');
        $currency->code = $request->input('code');
        $currency->symbol = $request->input('symbol');
        $currency->format = $request->input('format');
        $currency->exchange_rate = $request->input('exchange_rate');
        $currency->active = $request->input('active');

        $isDirty = $currency->isDirty('exchange_rate');

        $currency->save();

        currency()->clearCache();

        if ($isDirty) {

            Product::with('variants', 'specials', 'discounts', 'prices')
                ->where('currency_code', $currency->code)
                ->where('type', '<>', 3)
                ->select('id', 'vendor_price', 'type', 'currency_code')
                ->get()
                ->each(function ($product) {
                    Product::setPrices($product);
                });
        }

        return response()->json([
            'id' => $currency->id,
            'text' => trans('form.result.success-updated')
        ]);

    }

    public function destroy($code)
    {

        $products = Product::with('variants', 'specials', 'discounts', 'prices')
            ->where('type', '<>', 3)->where('currency_code', $code)->get();

        if ($code == Setting::get('currency')) {
            abort(422, trans('validation.custom.inactive_main_currency'));
        } elseif (currency()->delete($code)) {

            $products->each(function ($product) use ($code) {

                $product->currency_code = Setting::get('currency');
                $product->save();

                Product::setPrices($product);
            });

            return response()->json([
                'text' => trans('form.result.success-deleted')
            ]);
        }
    }
}
