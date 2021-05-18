<?php

namespace App\Http\Requests\Admin\Product\MassActions;

class StatusRequest extends BaseRequest
{

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->route()->parameter('status')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'status' => ['required', 'numeric', 'in:1,0'],
        ]);
    }
}
