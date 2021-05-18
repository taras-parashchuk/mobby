<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManyQuantitiesOrPricesRequest extends FormRequest
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
        $this->merge(['data_type' => $this->route()->parameter('data_type')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $value_rules = ['required'];

        if($this->input('data_type') === 'quantity'){
            $value_rules[] = 'integer';
        }elseif($this->input('data_type') === 'vendor_price'){
            $value_rules[] = 'numeric';
        }

        return [
            //
            'data_type' => ['required', 'in:quantity,vendor_price'],
            'products' => ['required', 'array'],
            'action' => ['required', 'in:plus,multiply,fixed'],
            'direction' => ['required', 'in:1,-1'],
            'value' => $value_rules
        ];
    }
}
