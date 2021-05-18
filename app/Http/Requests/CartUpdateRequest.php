<?php

namespace App\Http\Requests;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Repositories\CartRepository;
use Illuminate\Foundation\Http\FormRequest;

class CartUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->cart = Cart::owner()->first();
        $cartProduct = CartProduct::where('id', $this->route('cart'))->with('product')->first();
        $this->quantity = $cartProduct->product->quantity;
        $this->require_qty = $this->input('qty');
        $this->minimum = $cartProduct->product->calculateMinimum;
        $this->checkQuantity = (new CartRepository)->checkQuantity($cartProduct->product, $this->require_qty);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'qty' => ['required'],
            'cart_product_id' => ['required', 'exists:cart_products,id',
                    function ($attribute, $value, $fail) {
                        if (!$this->checkQuantity) {
                            $fail(trans('cart.error.not_enough_quantity', ['qty' => $this->quantity]));
                        }
                    },
                    function ($attribute, $value, $fail) {
                        if ($this->minimum > $this->require_qty) {
                            $fail(trans('cart.error.minimum_quantity', ['qty' => $this->minimum]));
                        }
                    },
                ]
        ];
    }

    protected function validationData()
    {
        return array_merge($this->all(), [
            'cart_product_id' => $this->route('cart')
        ]);
    }
}
