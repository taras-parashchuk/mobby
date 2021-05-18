<?php

namespace App\Http\Controllers;

use App\Events\NewOrderEvent;
use App\Helpers\HelperFunction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\User;
use Cart;
use Illuminate\Http\Request;
use App\Rules\Telephone;
use Validator;
use Illuminate\Support\Facades\Event;


class CheckoutController extends Controller
{
    //
    public function show(Request $request)
    {
        $request->session()->forget('checkout');

        return view('pages.checkout');
    }

    public function translation()
    {
        return trans('pages.checkout');
    }

    public function methods(Request $request, string $type)
    {
        if ($type !== 'shipping' && $type !== 'payment') return;

        if ($type === 'shipping') {
            $methods = Shipping::where('status', 1)->orderBy('sort_order')->select('code', 'name', 'fields', 'has_api')->get()->each->append('decoded_fields');
        } else {
            $methods = Payment::where('status', 1)->orderBy('sort_order')->select('code', 'name', 'fields', 'has_api')->get()->each->append('decoded_fields');
        }

        $methods = $methods->keyBy('code');

        return $methods;

    }

    public function method(string $code)
    {

    }

    public function save(Request $request, string $type)
    {
        $request->session()->reflash();

        if ($type === 'user') {

            if ($request->input('customer.tel')) {
                $request->merge(['tel' => preg_replace("/[^0-9]/", '', $request->input('customer.tel'))]);
            }

            if ($request->input('customer') && !Validator::make($request->all(), [
                    'customer.full_name' => ['bail', 'required', 'string', 'min:2', 'max:64'],
                    'tel' => ['bail', 'required', 'string', new Telephone()],
                    'customer.email' => ['nullable', 'email']
                ])->fails()) {

                $request->session()->forget('checkout.customer');

                $full_name = explode(' ', $request->input('customer.full_name'));

                $request->session()->put('checkout.customer.surname', $full_name[0]);

                $request->session()->put('checkout.customer.firstname', $full_name[1] ?? '');

                $request->session()->put('checkout.customer.lastname', $full_name[2] ?? '');

                $request->session()->put('checkout.customer.telephone', $request->input('tel', null));

                $request->session()->put('checkout.customer.email', $request->input('customer.email', null));

                $json['success'] = true;

            } else {
                $json['error'] = false;
            }
        } elseif ($type === 'shipping' || $type === 'payment') {

            if ($type === 'shipping') {
                $methods = Shipping::where('status', 1)->orderBy('sort_order')->pluck('code');

            } else {

                $methods = Payment::where('status', 1)->orderBy('sort_order')->pluck('code');
            }

            $request->session()->forget("checkout.{$type}.method");

            if ($type === 'shipping') {
                $request->session()->forget("checkout.address");
            } elseif ($type === 'payment') {
                $request->session()->forget("checkout.other");
            }

            if ($request->input($type) && $request->input("$type.{$type}_method")
                && $methods && $methods->contains($request->input("$type.{$type}_method"))) {

                foreach ($request->input($type) as $key => $value) {
                    if ($key === "{$type}_method") {
                        $request->session()->put("checkout.$type.method", $value);
                    } elseif ($type === 'shipping') {
                        $request->session()->put("checkout.address.$key", $value);
                    } elseif ($type === 'payment') {
                        $request->session()->put("checkout.other.$key", $value);
                    }
                }

                $json['success'] = true;

            } else {
                $json['error'] = false;
            }
        }

        return response()->json($json);
    }

    public function confirm(Request $request)
    {

        $data = [
            'user_id' => \Auth::id(),
            'user_group_id' => \Auth::id() ? \Auth::user()->group_id : null,
            'firstname' => session()->get('checkout.customer.firstname'),
            'surname' => session()->get('checkout.customer.surname') ?? '',
            'lastname' => session()->get('checkout.customer.lastname') ?? '',
            'email' => session()->get('checkout.customer.email') ?? '',
            'telephone' => session()->get('checkout.customer.telephone'),
            'shipping_code' => session()->get('checkout.shipping.method'),
            'payment_code' => session()->get('checkout.payment.method'),
            'comment' => session()->get('checkout.other.comment'),
            'locale' => app()->getLocale(),
            'currency_code' => currency()->getUserCurrency(),
            'ip' => $request->ip(),
            'forwarded_ip' => $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('HTTP_CLIENT_IP') ?? '',
            'user_agent' => $request->server('HTTP_USER_AGENT') ?? '',
            'accept_language' => $request->server('HTTP_ACCEPT_LANGUAGE') ?? '',
            'exchange_rate' => currency()->getCurrency()['exchange_rate'],
        ];

        if (session()->get('checkout.address')) {
            $data['address'] = json_encode(session()->get('checkout.address'), JSON_UNESCAPED_UNICODE);
        }

        $order = Order::create($data);

        $products = [];

        foreach (Cart::getContent() as $cart_product) {

            $product = Product::select('id', 'sku', 'type')->find($cart_product->id)->append('translate');

            $products[] = [
                'product_id' => $cart_product->id,
                'sku' => $product->sku,
                'name' => $product->translate->name,
                'quantity' => $cart_product->quantity,
                'price' => $cart_product->price,
                'specification' => json_encode($product->getVariantSpecification())
            ];
        };

        $order->products()->createMany($products);

        if ($request->input('type') === 'fast_order') {
            $comment = trans('pages.checkout.confirm.fast_order');
        } else {
            $comment = trans('pages.checkout.confirm.full_order');
        }

        foreach (Cart::getConditions() as $condition) {

            if($condition->getType() === 'discount'){
                $value = $condition->getAttributes()['class']::getCalculatedValue();
            }else{
                $value = $condition->getValue();
            }

            $totals[] = [
                'code' => $condition->getType(),
                'name' => trans("cart.conditionals.heading.{$condition->getName()}"),
                'value' => $value,
                'sort_order' => $condition->getOrder(),
            ];
        }

        $totals[] = [
            'code' => 'total',
            'name' => trans('cart.text.total'),
            'value' => Cart::getSubTotal(),
            'sort_order' => isset($condition) ? $condition->getOrder() + 1 : 0
        ];

        $order->totals()->createMany($totals);

        if ($request->input('type') === 'fast_order') {

            $order->fast_order = true;

            $order->histories()->create([
                'order_status_id' => 1,
                'notify' => 0,
                'comment' => $comment,
                'date_added' => now()
            ]);

            Cart::clear();

            $request->session()->forget('checkout');

            event(new NewOrderEvent($order));

            return response()->json([
                'success' => 1,
                'redirect' => route('checkout.success')
            ]);

        } else {

            $order->fast_order = false;

            if(!Payment::where('code', $data['payment_code'])->first()->has_api){

                $order->histories()->create([
                    'order_status_id' => 1,
                    'notify' => 0,
                    'comment' => $comment,
                    'date_added' => now()
                ]);

                $request->session()->forget('checkout');

                event(new NewOrderEvent($order));

                Cart::clear();

                return response()->json([
                    'success' => 1,
                    'redirect' => route('checkout.success')
                ]);

            }else{

                \Session::put('checkout.order_id', $order->id);
                \Session::put('checkout.order_comment', $comment);

                return response()->json([
                    'success' => 1
                ]);
            }
        }
    }

    public function fast_order(Request $request)
    {
        if ($request->input('telephone')) {
            $request->merge(['telephone' => preg_replace("/[^0-9]/", '', $request->input('telephone'))]);
        }

        $request->validate([
            'telephone' => ['required', 'string', new Telephone()],
            'id' => ['required', 'exists:products,id']
        ]);

        $order = Order::create([
            'user_id' => \Auth::id(),
            'user_group_id' => User::getGroupId(),
            'telephone' => $request->input('telephone'),
            'locale' => app()->getLocale(),
            'currency_code' => currency()->getUserCurrency(),
            'ip' => $request->ip(),
            'forwarded_ip' => $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('HTTP_CLIENT_IP') ?? '',
            'user_agent' => $request->server('HTTP_USER_AGENT') ?? '',
            'accept_language' => $request->server('HTTP_ACCEPT_LANGUAGE') ?? '',
            'exchange_rate' => currency()->getCurrency()['exchange_rate'],
        ]);

        $product = Product::with('prices', 'special', 'discount')->find($request->id);

        $order->products()->create([
            'product_id' => $request->input('id'),
            'sku' => $product->sku,
            'name' => $product->translate->name,
            'quantity' => $product->minimum,
            'price' => $product->specialOrCalculatePrice,
            'specification' => json_encode($product->getVariantSpecification())
        ]);

        $order->histories()->create([
            'order_status_id' => 1,
            'notify' => 0,
            'comment' => trans('pages.checkout.confirm.fast_order'),
            'date_added' => now()
        ]);

        $order->totals()->createMany([
            [
                'code' => 'total',
                'name' => trans('cart.text.total'),
                'value' => $product->specialOrCalculatePrice,
                'sort_order' => 1
            ]
        ]);

        $order->fast_order = true;

        event(new NewOrderEvent($order));

        return response()->json([
            'success' => trans('form.result.success-sent')
        ]);

    }

    public function success()
    {
        return view('pages.checkout_success');
    }
}
