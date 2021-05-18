<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $appends = ['FullName'];
    //
    protected $fillable = [
        'user_id',
        'user_group_id',
        'firstname',
        'surname',
        'lastname',
        'email',
        'telephone',
        'address',
        'shipping_code',
        'payment_code',
        'comment',
        'locale',
        'currency_code',
        'ip',
        'forwarded_ip',
        'user_agent',
        'accept_language',
        'exchange_rate'
    ];



    public function products()
    {
        return $this->hasMany('App\Models\OrderProduct');
    }

    public function histories()
    {
        return $this->hasMany('App\Models\OrderHistory');
    }

    public function history()
    {
        return $this->hasOne('App\Models\OrderHistory')->latest('id');
    }

    public function totals()
    {
        return $this->hasMany('App\Models\OrderTotal');
    }

    public function total()
    {
        return $this->hasOne('App\Models\OrderTotal', 'order_id', 'id')
            ->where('code', '=','total');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment', 'payment_code', 'code');
    }

    public function shipping()
    {
        return $this->belongsTo('App\Models\Shipping', 'shipping_code', 'code');
    }

    public function language()
    {
        return $this->belongsTo('App\Models\Language', 'locale', 'locale');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_code', 'code');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getDecodeAddressInfoAttribute()
    {
        $address = [];

        if($this->shipping && $this->shipping->fields){

            $fields = [];

            foreach (json_decode($this->shipping->fields, true) as $field){
                if(isset($field['trans'][app()->getLocale()])){
                    $fields[$field['key']] = $field['trans'][app()->getLocale()]['name'];
                }else{
                    $fields[$field['key']] = $field['trans'][Setting::get('site_language')]['name'];
                }

            }

            foreach (json_decode($this->address, true) as $value){
                $address[] = [
                    'name' => $fields[$value['key']],
                    'value' => $value['value']
                ];
            }
        }

        return $address;

    }

    public function getFullNameAttribute()
    {
        $parts = [];

        if ($this->surname) array_push($parts, $this->surname);
        if ($this->firstname) array_push($parts, $this->firstname);
        if ($this->lastname) array_push($parts, $this->lastname);

        return implode(' ', $parts);
    }

    public function getCreatedAtAttribute($value)
    {
        if($value) return date(trans('admin.country_formats.date'), strtotime($value));
    }

    public function externalSource()
    {
        return $this->hasMany('App\Models\OrderExternalSource', 'order_id', 'id');
    }
}
