<?php

namespace App\Http\Controllers;

use App\Helpers\Image;
use App\Models\Total;

use Illuminate\Http\Request;
use Cart;
use App\Models\Product;
use DB;

class CartController extends Controller
{

    public function __construct()
    {
        //Cart::session(auth()->user()->id ?? \Session::token());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Cart::session(auth()->user()->id ?? \Session::token());

        $productsFromCart = Cart::getContent();

        $this->refreshTotals();

        $products = collect();

        $productsFromCart->each(function($productFromCart) use ($products){

            $dbProduct = Product::find($productFromCart->id);

            if($dbProduct){
                $products->push([
                    'id' => $productFromCart->id,
                    'qty' => $productFromCart->quantity,
                    'href' => $dbProduct->href,
                    'price' => currency($productFromCart->attributes->price * $productFromCart->quantity, config('settings.main_currency'), currency()->getUserCurrency()),
                    'special' => $productFromCart->attributes->special ? currency($productFromCart->attributes->special * $productFromCart->quantity, config('settings.main_currency'), currency()->getUserCurrency()) : false,
                    'thumb' => $dbProduct->resizeImage(75, 75),
                    'name' => $productFromCart->name,
                    'specification' => $dbProduct->getVariantSpecification()
                ]);
            }else{
                $this->destroy($productFromCart->id);
            }


        });

        //
        return response()->json(
            [
                'products' => $products,
                'conditionals' => $this->formatConditions(),
                'total' => currency(Cart::getTotal(), config('settings.main_currency'), currency()->getUserCurrency())
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        Cart::session(auth()->user()->id ?? \Session::token());

        $product = $main_product = Product::find($request->input('id'));

        if($product->type === 3){
            $main_product = Product::find($product->main_id, ['minimum']);
        }

        $product->append('translate');

        $available_qty = $product->quantity;
        $require_qty = (int)($request->input('qty', $main_product->minimum));

        $json = [];

        //if(!Cart::content()->where('id', $product->id)->count()){
        if ($available_qty < $require_qty) {
            $json['error'] = trans('cart.error.not_enough_quantity', ['qty' => $available_qty]);
        } else {
            $json['success'] = true;

            Cart::add([
                'id' => $product->id,
                'name' => $product->translate->name,
                'price' => $product->specialOrCalculatePrice,
                'quantity' => $require_qty,
                'attributes' => [
                    'price' => $product->discountOrPrice,
                    'special' => $product->specialConvertedPrice
                ]
            ]);
        }
        //}

        return response()->json($json);
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $product_id)
    {
        //

        Cart::session(auth()->user()->id ?? \Session::token());

        if(Cart::has($product_id) && $dbProduct = Product::find($product_id)){

            $require_qty = (int)$request->input('qty');

            $cartItem = Cart::get($product_id);

            $minimum = $dbProduct->type === 3 ? $dbProduct->main_product->minimum : $dbProduct->minimum;

            if($require_qty < $dbProduct->minimum){
                $json['error'] = trans('cart.error.minimum_quantity', ['qty' => $minimum]);
            }elseif ($dbProduct->quantity < $require_qty) {
                $json['error'] = trans('cart.error.not_enough_quantity', ['qty' => $dbProduct->quantity]);
            } else {

                Cart::update($product_id, [
                    'quantity' => $require_qty - $cartItem->quantity
                ]);

                $this->refreshTotals();

                $json['total'] = currency(Cart::getTotal(), config('settings.main_currency'), currency()->getUserCurrency());

                $json['conditionals'] = $this->formatConditions();

                if($cartItem->attributes->special){
                    $json['price'] = currency($cartItem->attributes->price * $require_qty, config('settings.main_currency'), currency()->getUserCurrency());
                    $json['special'] = currency($cartItem->attributes->special * $require_qty, config('settings.main_currency'), currency()->getUserCurrency());
                }else{
                    $json['price'] = currency($cartItem->price * $require_qty, config('settings.main_currency'), currency()->getUserCurrency());
                    $json['special'] = false;
                }

            }
        }

        return response()->json($json);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($product_id)
    {
        Cart::session(auth()->user()->id ?? \Session::token());

        if (Cart::has($product_id)) {

            Cart::remove($product_id);

            $this->refreshTotals();

            if(Cart::isEmpty()) Cart::clearCartConditions();
        }
    }

    public function total()
    {
        Cart::session(auth()->user()->id ?? \Session::token());

        return response(currency(Cart::getSubTotal(), config('settings.main_currency'), currency()->getUserCurrency()));
    }

    private function formatConditions()
    {
        $conditions = Cart::getConditions();

        //dd($conditions);

        foreach($conditions as $condition)
        {
            $conditions[$condition->getType()][] = [
                'value' => $condition->getValue(),
                'sort_order' => $condition->getOrder(),
                'attributes' => $condition->getAttributes(),
            ];
        }

        return $conditions;
    }

    private function refreshTotals()
    {
        Total::all()->each(function($total){

            $code = $total->code;

            $class_name = "App\Helpers\Totals\Cart$code";

            ($class_name)::calculate();

        });
    }
}
