<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ExcelAddToQueueRequest extends FormRequest
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
            'file' => ['file', 'required', 'max:10000', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel', function ($attribute, $value, $fail) {
                try {
                    \PhpOffice\PhpSpreadsheet\IOFactory::load($this->file('file')->getRealPath());
                } catch (\Exception $exception) {
                    $fail(trans('validation.custom.file-excel-is-not-readable'));
                }

            }]
        ];
    }
}
