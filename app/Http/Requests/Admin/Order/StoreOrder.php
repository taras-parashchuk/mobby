<?php

namespace App\Http\Requests\Admin\Order;

use App\Models\Cart;
use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrder extends FormRequest
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
        $this->orderCustomer = session()->get('orderCustomer');
        $this->orderShippingPayment = session()->get('orderShippingPayment');
        $cart = Cart::with('cartProducts.product')->findOrFail(session()->get('cart_id'));
        $this->products = [];
        $this->cart = $cart;
        foreach ($cart->cartProducts as $item) {
            $this->products[$item->id] = $item->product->toArray();
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
            'user_id' => 'nullable|exists:users,id',
            'firstname' => 'required|string|max:40',
            'lastname' => 'required|string|max:40',
            'surname' => 'required|string|max:40',
            'email' => 'nullable|email|max:120',
            'telephone' => ['required', new Telephone(), 'max:30'],
//            'shipping_code' => 'nullable|exists:shippings,code',
//            'payment_code' => 'nullable|exists:payments,code',
            'products' => 'required|array',
            'products.*.id' => ['required', Rule::exists('products', 'id')->whereIn('type', [1, 3])],
            'products.*.quantity' => ['required', function ($attribute, $value, $fail) {

                $matches = [];
                preg_match('/products.([0-9]+).quantity/ui', $attribute, $matches);

                $cartProduct = $this->cart->cartProducts->firstWhere('id', $matches[1]);
                if(isset($matches[1])){

                    if ( $value < $cartProduct->quantity) {
                        $fail(trans('cart.error.not_enough_quantity', ['qty' => $value]));
                    }

                }

                return true;
            }],
//            'subtotals' => 'nullable|array',
//            'subtotals.*.subtotal_template_id' => 'exists:subtotal_templates,id',
//            'subtotals.*.is_percent_effect' => 'required|boolean',
//            'subtotals.*.value' => 'nullable',
        ];
    }

    protected function validationData()
    {
        return array_merge($this->all(), [
            'user_id' => $this->orderCustomer['user_id'],
            'firstname' => $this->orderCustomer['firstname'],
            'surname' => $this->orderCustomer['surname'],
            'lastname' => $this->orderCustomer['lastname'],
            'email' => $this->orderCustomer['email'],
            'telephone' => $this->orderCustomer['telephone'],
            'shipping_code' => $this->orderShippingPayment['shipping_code'],
            'payment_code' => $this->orderShippingPayment['payment_code'],
            'products' => $this->products,
        ]);
    }
}
