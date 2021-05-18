<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingPayment extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'shipping_code' => ['nullable', 'exists:shippings,code'],
            'payment_code' => ['nullable', 'exists:payments,code'],
            'address' => ['nullable', 'array']
        ];
    }
}
