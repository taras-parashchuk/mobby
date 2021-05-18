<?php

namespace App\Http\Requests\Admin\Product\MassActions;

class SpecialPriceRequest extends BaseRequest
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
            'has_rule' => ['required', 'boolean'],
            'user_group' => ['nullable', 'exists:user_groups,id'],
            'date_start' => ['nullable', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
            'diff' => ['nullable', 'in:%']
        ]);

    }
}
