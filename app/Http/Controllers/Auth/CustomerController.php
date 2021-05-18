<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\HelperFunction;
use App\Helpers\Image;
use App\Models\Order;
use App\Rules\Telephone;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    //
    public function index()
    {
        return view('pages.account');
    }

    public function info()
    {
        $user = \Auth::user()->load('wishlist');

        $user->wishlist->each(function($product){
           $product->thumb =  $product->resizeImage( 150, 150);
        });

        $user->wishlist->each->append('href', 'available', 'translate', 'priceFormat', 'pricesFormat', 'specialFormat', 'stock_title');

        return $user;
    }

    public function update(Request $request, $path = '')
    {
        $rules = [];

        switch ($path) {
            case 'edit-contacts':
                if (!$request->input('email') && !$request->input('telephone')) {
                    return;
                } else {
                    $rules = [
                        'firstname' => ['bail', 'required', 'string', 'between:2,32'],
                        'lastname' => ['bail', 'required', 'string', 'between:2,64'],
                    ];

                    if ($request->input('email')) {
                        $rules['email'] = ['email', Rule::unique('users', 'email')->ignore(\Auth::user()->id)];
                    }

                    if ($request->input('telephone')) {

                        $request->merge(['telephone' => preg_replace("/[^0-9]/", '', $request->input('telephone'))]);

                        $rules['telephone'] = ['string', new Telephone()];
                    }
                }
                break;
            case 'security':
                $rules['password'] = ['bail', 'required', 'string', 'min:4', 'confirmed'];
                break;
        }

        if (!$rules || $request->validate($rules)) {

            $for_update = [
                'firstname' => $request->input('firstname', \Auth::user()->firstname),
                'lastname' => $request->input('lastname', \Auth::user()->lastname),
                'email' => $request->input('email', \Auth::user()->email),
                'telephone' => preg_replace('#\D#', '', $request->input('telephone', \Auth::user()->telephone)),
            ];

            if ($request->input('password')) {
                $for_update['password'] = Hash::make($request->input('password'));
            }

            if ($path == 'subscribes') {
                $for_update['newsletter'] = $request->input('newsletter', false);
            } else {
                $for_update['newsletter'] = \Auth::user()->newsletter;
            }

            \Auth::user()->update($for_update);
        }
    }

    public function orders()
    {
        $orders = Order::withCount('products')->with('products','total', 'history', 'totals', 'histories', 'shipping', 'payment')->where('user_id', Auth::id())->get();

        return compact('orders');
    }

    public function order($order_id)
    {
        $order = Order::withCount('products')->with('products','total', 'history', 'totals', 'histories', 'shipping', 'payment')->where('user_id', Auth::id())->find($order_id);

        return compact('order');
    }
}
