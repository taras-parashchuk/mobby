<?php

namespace App\Http\Requests\Admin\Syncs\Moysklad;

use App\Models\Sync;
use Illuminate\Foundation\Http\FormRequest;

class OrderDownloadOrdersRequest extends FormRequest
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
        $this->activeSync = Sync::where('type', config('syncs.moysklad.dataTypes.order'))->where('data_type', config('syncs.moysklad.externalCode'))->where('finished', 0)->where(function ($q) {
            $q->where('stopped', '!=', 1)
                ->where('fatal_error','!=', 1);
        })->first();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sync' => [ function ($attribute, $value, $fail) {
                if (!is_null($this->activeSync)) {
                    $fail('sync already exists');
                }
            }],
        ];
    }

    protected function validationData()
    {
        return array_merge($this->all(), [
            'sync' => $this->activeSync,
        ]);
    }
}
