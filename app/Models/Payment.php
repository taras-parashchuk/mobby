<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //

    protected $appends = ['decoded_method_name'];

    protected $casts = [
        'has_api' => 'boolean'
    ];

    public $timestamps = false;



    public function getDecodedMethodNameAttribute()
    {
        $names = json_decode($this->name, true);

        return $names[app()->getLocale()] ?? $names[Setting::get('site_language')];

    }

    public function getDecodedFieldsAttribute()
    {
        if ($this->fields) {
            $fields = [];

            foreach (json_decode($this->fields, true) as $field) {
                $field['trans'] = $field['trans'][app()->getLocale()] ?? $field['trans'][Setting::get('site_language')];

                $fields[] = $field;
            }

            return $fields;
        }else{
            return [];
        }
    }
}
