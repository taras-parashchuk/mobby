<?php

namespace App\Http\Requests\Admin\Order;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public $product;

    private $order_product;

    private $available_quantity;

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
        $this->product = Product::findOrFail($this->input('product_id'));

        $this->available_quantity = $this->product->quantity;

        $order_product_id = $this->route('product');

        if($order_product_id){

            $this->order_product = OrderProduct::findOrFail($order_product_id);

            if($this->order_product->product_id === $this->product->id){
                $this->available_quantity += $this->order_product->quantity;
            }

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
            //
            'product_id' => ['required', function ($attribute, $value, $fail) {

                if (in_array($this->product->type, [1, 3]) && $this->available_quantity < $this->input('required_quantity')) {
                    $fail(trans('cart.error.not_enough_quantity', ['qty' => $this->available_quantity]));
                }

                return true;
            }],
            'required_quantity' => ['required', 'numeric', 'min:1']
        ];
    }
}
