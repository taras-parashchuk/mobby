<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Repositories\CartRepository;
use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
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
        if($this->has('id')){

            $product = Product::where('id', $this->input('id'))->firstOrFail();

            $this->quantity = $product->quantity;

            if(!$this->has('qty')){
                $this->merge([
                    'qty' => $product->minimum
                ]);
            }

            $this->require_qty = $this->input('qty');
            $this->checkQuantity = (new CartRepository)->checkQuantity($product, $this->require_qty);
        }

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => ['required'],
            'qty' => ['required',
                function ($attribute, $value, $fail) {
                    if (!$this->checkQuantity) {
                        $fail(trans('cart.error.not_enough_quantity', ['qty' => $this->quantity]));
                    }
                },
            ],
        ];
    }
}
