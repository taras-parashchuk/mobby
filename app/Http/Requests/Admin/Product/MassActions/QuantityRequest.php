<?php

namespace App\Http\Requests\Admin\Product\MassActions;

class QuantityRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'value' => ['required', 'numeric'],
            'direction' => ['present', 'sometimes:nullable|in:1,-1'],
        ]);
    }
}
