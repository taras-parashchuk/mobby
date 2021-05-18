<?php

namespace App\Http\Requests\Admin\Order;

use App\Rules\Telephone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomer extends FormRequest
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
        $this->merge(['telephone' => preg_replace("/[^0-9]/", '', $this->input('telephone'))]);
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
            'user_id' => ['nullable', 'exists:users,id'],
            'user_group_id' => ['nullable', 'exists:user_groups,id'],
            'firstname' => ['nullable', 'string', 'max:40'],
            'surname' => ['nullable', 'string', 'max:40'],
            'lastname' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'telephone' => ['required', new Telephone()],
        ];
    }
}
