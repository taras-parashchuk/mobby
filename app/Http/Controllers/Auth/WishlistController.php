<?php

namespace App\Http\Controllers\Auth;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WishlistController extends Controller
{
    private $withlist;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private function init()
    {
        $this->withlist = Wishlist::where('user_id', \Auth::user()->id)->pluck('product_id');
    }

    public function index()
    {
        $this->init();

        return $this->withlist;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(int $product_id)
    {
        $this->init();

        if (Product::find($product_id) && !$this->withlist->contains($product_id)) {
            $wishlistProduct = new Wishlist;

            $wishlistProduct->user_id = \Auth::id();
            $wishlistProduct->product_id = $product_id;

            $wishlistProduct->save();

            $this->withlist->push($product_id);
        }

        return $this->withlist;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($product_id)
    {
        $this->init();

        //
        if ($wishlistProduct = Wishlist::where('user_id', \Auth::id())->where('product_id', $product_id)->first()) {

            $wishlistProduct->delete();

            $this->withlist = $this->withlist->filter(function($value) use ($product_id){
                return $value !== $product_id;
            });
        }

        return $this->withlist;
    }
}
