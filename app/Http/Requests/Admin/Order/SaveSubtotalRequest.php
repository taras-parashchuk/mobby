<?php

namespace App\Http\Requests\Admin\Order;

use App\Models\SubtotalTemplate;
use Illuminate\Foundation\Http\FormRequest;

class SaveSubtotalRequest extends FormRequest
{

    public $subtotal_template;

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
        if($this->input('subtotal_template_id')){
            $this->subtotal_template = SubtotalTemplate::findOrFail($this->input('subtotal_template_id'));
        }

        $this->merge([
            'order_id' => $this->route('order_id')
        ]);
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
            'order_id' => 'required|exists:orders,id',
            'subtotal_template_id' => 'exists:subtotal_templates,id',
            'is_percent_effect' => 'required|boolean',
            'value' => 'nullable',
        ];
    }
}
