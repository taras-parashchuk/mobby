<?php

namespace App\Http\Requests\Admin;

use App\Models\SyncConfiguration;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class XmlAddToQueueRequest extends FormRequest
{
    private $file_content;

    private $configuration_settings;

    private $error_path_param = null;

    private $paths = [];

    private $paths_with_default_value = [
        'category_parent_id',
        'product_old_price',
        'product_description',
    ];

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
        ini_set('upload_max_filesize', '100M');

        $configuration = SyncConfiguration::find($this->input('configuration_id'));

        if ($configuration) {

            $this->configuration_settings = $configuration->settings;

            try {

                if ($this->input('manually') && $this->file('file')) {
                    $this->file_content = simplexml_load_file($this->file('file')->getRealPath());
                } else {
                    $this->file_content = simplexml_load_file($this->input('link'));
                }

                $this->validatePaths();

            } catch (\Exception $exception) {
                $this->file_content = false;
            }

        }

        if (!$this->input('manually')) {
            if ($this->input('times.0') && $this->input('times.1')) {
                $this->merge([
                    'times' =>
                        [
                            Carbon::parse($this->input('times.0'))->format('H:i'),
                            Carbon::parse($this->input('times.1'))->format('H:i')
                        ]
                ]);
            } elseif($this->input('times.0')) {
                $this->merge([
                    'times' =>
                        [
                            Carbon::parse($this->input('times.0'))->format('H:i')
                        ]
                ]);
            }
        }

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'configuration_id' => ['bail', 'required', 'exists:sync_configurations,id', function ($attribute, $value, $fail) {
                if ($this->error_path_param) $fail(trans('validation.custom.xml-path-incorrect', ['path' => trans('validation.sync_paths.xml.' . $this->error_path_param)]));
            }],
            'link' => [Rule::requiredIf(function () {
                return !$this->input('manually');
            }), 'url'],
            'times.0' => [Rule::requiredIf(function () {
                return !$this->input('manually');
            }), 'date_format:H:i'],
            'times.1' => ['nullable', 'date_format:H:i'],
            'status' => [Rule::requiredIf(function () {
                return !$this->input('manually');
            }), 'boolean'],
            'file' => ['bail', Rule::requiredIf(function () {
                return $this->input('manually');
            }), 'file', 'max:100000', 'mimetypes:text/xml,application/xml', function ($attribute, $value, $fail) {
                if (!$this->file_content) $fail(trans('validation.custom.file-xml-with-errors'));
            }],
            'data_type' => 'in:xml,excel'
        ];
    }

    private function validatePaths()
    {
        foreach ($this->configuration_settings->paths as $path_code => $values) {

            switch ($path_code){
                case 'categories_container':
                case 'products_container':
                case 'site_source':

                    if(!$this->checkOnErrorPath($content = $this->file_content, $values, $path_code)) break 2;

                    break;
                case 'product_tag':

                    if(!$this->checkOnErrorPath($this->paths['products_container'], $values, $path_code)) break 2;

                    break;
                case 'category_tag':

                    if($this->paths['categories_container'] && !$this->checkOnErrorPath($this->paths['categories_container'], $values, $path_code)) break 2;

                    break;
                case 'category_id':

                    if(isset($this->paths['category_tag']) && !$this->checkOnErrorPath($this->paths['category_tag'], $values, $path_code)) break 2;

                    break;
                case 'category_name':

                    if(isset($this->paths['category_tag']) && !$this->checkOnErrorPath($this->paths['category_tag'], $values, $path_code)) break 2;

                    break;
                case 'category_parent_id':

                    if(isset($this->paths['category_tag']) && !$this->checkOnErrorPath($this->paths['category_tag'], $values, $path_code)) break 2;

                    break;

                case 'category_parent_id':

                    if(isset($this->paths['category_tag']) && !$this->checkOnErrorPath($this->paths['category_tag'], $values, $path_code)) break 2;

                    break;

                case 'product_attribute_name':
                case 'product_attribute_value':
                    if($this->paths['product_attribute'] && !$this->checkOnErrorPath($this->paths['product_attribute'], $values, $path_code)) break 2;
                    break;
                default:

                    if(!$this->checkOnErrorPath($this->paths['product_tag'], $values, $path_code)) break 2;

                    break;
            }



        }
    }

    public function attributes()
    {
        return [
            'times.0' => strtolower(trans('admin.columns.sync_time')),
            'times.1' => strtolower(trans('admin.columns.sync_time'))
        ];
    }

    private function checkOnErrorPath($content, $values, $path_code)
    {

        if(in_array($path_code, $this->paths_with_default_value)) return true;

        $paths_settings = $this->configuration_settings->creating_updating;

        if(!$values){

            if( $content->children()->count() && isset($paths_settings->{$path_code}) && ($paths_settings->{$path_code}->creating || $paths_settings->{$path_code}->updating)){
                $this->error_path_param = $path_code;
            }

        }else{

            foreach ($values as $value){
                if (isset($content->{$value})) {
                    $content = $content->{$value};
                }elseif (isset($content[$value])){
                    $content = $content[$value];
                } elseif(isset($paths_settings->{$path_code}) && ($paths_settings->{$path_code}->creating || $paths_settings->{$path_code}->updating)) {

                    $this->error_path_param = $path_code;

                    break;
                }
            }
        }

        if($this->error_path_param) return false;

        $this->paths[$path_code] = $content;

        return true;
    }
}
