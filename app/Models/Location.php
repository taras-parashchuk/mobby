<?php

namespace App\Models;

use App\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\Model;


class Location extends Model
{
    //
    public $timestamps = false;

    use ModelHelper;

    protected $with = ['translates'];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function translates()
    {
        return $this->hasMany('App\Models\LocationTranslation')
            ->whereIn('locale', Language::getOnlyActive()->pluck('locale'));
    }

    public function scopeEnabled($query)
    {
        $query->where('status', 1);
    }

    public function getEmailsAttribute($value)
    {
        return json_decode($value ?: '', true) ?: [];
    }

    public function getTelephonesAttribute($value)
    {
        return json_decode($value ?: '', true) ?: [];
    }

    public function getTelephonesWithOperatorsAttribute()
    {
        $results = collect();

        $list_operators = array(
            'kyivstar' => array('039', '067', '068', '096', '097', '098'),
            'mtc' => array('050', '066', '095', '099'),
            'lifecell' => array('063', '093'),
            'utel' => array('091'),
            'PEOPLEnet' => array('092'),
            'intertelecom' => array('094')
        );

        foreach ($this->telephones as $telephone) {

            $first_code = strpos($telephone, '(') + 1;
            $last_code = strpos($telephone, ')');

            $operator_code = trim(substr($telephone, $first_code, $last_code - $first_code));

            foreach ($list_operators as $name_op => $list_operator) {
                if (in_array($operator_code, $list_operator)) {
                    $current_name = $name_op;
                    break;
                } else {
                    $current_name = '';
                }

            }
            if (!$current_name) {
                $current_name = 'other';
            }

            $results->push([
                'tel' => trim($telephone),
                'number' => str_replace(' ', '', $telephone),
                'operator' => $current_name,
            ]);
        }

        return $results;
    }
}
