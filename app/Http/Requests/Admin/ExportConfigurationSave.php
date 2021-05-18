<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportConfigurationSave extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    private $error_path;

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if($this->isMethod('post')){
            $this->merge([
                'id' => null
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $rules = [
            'name' => ['required', Rule::unique('export_configurations')],
            'products_list_id' => ['nullable', 'exists:export_products_lists,id'],
            'settings.paths' => ['required', 'array'],
            'settings.attributes' => ['required', 'array'],
            'settings.attributes.is_selected_main' => ['required', 'boolean'],
            'settings.attributes.list' => ['present', 'array']
        ];

        if($this->input('id')){
            $rules['id'] = 'required|exists:export_configurations';
            $rules['name'][1] = $rules['name'][1]->ignore($this->input('id'));
        }

        return $rules;
    }

    public function withValidator($validator){
        $validator->after(function ($validator) {

            if ($this->hasErrorInPaths()) {
                $validator->errors()->add($this->error_path, trans('validation.custom.xml-path-incorrect', ['path' => trans('validation.sync_paths.xml.' . $this->error_path)]));
            }

        });
    }

    private function hasErrorInPaths()
    {
        $has_error = false;

        foreach ($this->input('settings.paths') as $path_code => $path){
            if($path['status']){
                if($path['is_attribute'] && empty($path['value'])){
                    $has_error = true;
                    break;
                }elseif(is_null($path['is_attribute']) && empty($path['value'])){
                    $has_error = true;
                    break;
                }
            }
        }

        if($has_error) $this->error_path = $path_code;

        return $has_error;
    }
}
