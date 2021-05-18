<?php

namespace App\Http\Controllers\Admin;

use App\Events\UpdateOrderStatusEvent;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->has('current-year')) {

            $orders = \DB::table('orders')->selectRaw('year(created_at) year, month(created_at) month, count(id) count')
                ->whereRaw('`created_at` > DATE_ADD(Now(),INTERVAL- 6 MONTH)')
                ->groupBy( 'month')
                ->orderBy( 'year', 'asc')
                ->orderBy( 'month', 'asc')
                ->get();

        } else {
            $orders = Order::with('total', 'history.status')->orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))
                ->paginate($request->input('perPage'));
        }

        return compact('orders');
    }

    public function show($id)
    {
        $order = Order::with(['user', 'currency', 'language', 'payment', 'shipping', 'totals', 'histories.status', 'products'])->findOrFail($id);

        $order->viewed = true;

        $order->save();

        $order->histories->each(function ($h) {
            $h->status->append('translate');
        });

        $order->append('decodeAddressInfo');

        $payment_name_info = $order->payment ? json_decode($order->payment->name) : null;

        $shipping_name_info = $order->shipping ? json_decode($order->shipping->name) : null;

        if ($payment_name_info) {
            $order->payment->name = $payment_name_info->{app()->getLocale()};
        }

        if ($shipping_name_info) {
            $order->shipping->name = $shipping_name_info->{app()->getLocale()};
        }

        $order->products = $order->products->map(function ($p) {

            $product = Product::find($p->product_id);

            if ($product && $product->type === 3) $p->product_id = $product->main_id;

            if($product){
                $p->image = $product->image;
                $p->filemanager_thumb = $product->filemanager_thumb;
                $p->href = $product->href;
            }

            return $p;
        });

        return $order;
    }

    public function destroy($id)
    {
        Order::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    public function histories($order_id)
    {
        $histories = OrderHistory::with('status')->where('order_id', $order_id)->get();

        return compact('histories');
    }

    public function storeHistory(Request $request, $order_id)
    {
        $request->validate([
            'order_status_id' => ['exists:order_statuses,id'],
            'notify' => ['boolean'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $order = Order::findOrFail($order_id);

        $order_history = $order->histories()->save(new OrderHistory([
            'order_status_id' => $request->input('order_status_id'),
            'notify' => $request->input('notify'),
            'comment' => $request->input('comment') ?: ''
        ]));

        if($request->input('order_status_id') == Setting::get('order_status_refused')){
            $order->products->each(function($order_product){
                $product = Product::find($order_product->product_id);

                if($product){

                    $product->quantity += $order_product->quantity;

                    $product->save();

                }

                $order_product->quantity = 0;

                $order_product->save();

            });
        }

        if($request->input('notify')){
            event(new UpdateOrderStatusEvent($order_history));
        }

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function not_viewed()
    {
        return Order::where('viewed', false)->count();
    }
}
