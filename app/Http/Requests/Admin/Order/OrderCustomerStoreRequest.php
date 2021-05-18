<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Telephone;

class OrderCustomerStoreRequest extends FormRequest
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
            'user_id' => ['required', 'numeric', 'exists:users,id', 'nullable'],
            'firstname' => ['required'],
            'lastname' => ['required'],
            'email' => ['required', 'email', 'nullable'],
            'telephone' => ['required', new Telephone()],
        ];
    }
}
