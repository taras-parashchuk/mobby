<?php

namespace App\Http\Requests\Admin\Product\MassActions;

use App\Models\Currency;

class PriceRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'diff' => ['required', function ($attribute) {

                $values = Currency::select('code')->pluck('code');

                $values->push('%');

                return $values->contains($attribute);

            }],
            'value' => ['required', 'numeric'],
            'direction' => ['present', 'sometimes:nullable|in:1,-1'],
        ]);
    }
}
