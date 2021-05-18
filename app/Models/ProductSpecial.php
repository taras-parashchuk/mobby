<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductSpecial extends Model
{

    protected $fillable = ['user_group_id', 'price', 'date_start', 'priority', 'date_end'];

    public $timestamps = false;

    protected $casts = [
        'user_group_id' => 'integer'
    ];



    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getDateStartJsAttribute()
    {
        return $this->date_start ? date('c', strtotime($this->date_start)) : '';
    }

    public function getDateEndJsAttribute()
    {
        return $this->date_end ? date('c', strtotime($this->date_end)) : '';
    }

    public function setDateStartAttribute($value)
    {
        $this->attributes['date_start'] = $this->convertDate($value);
    }

    public function setDateEndAttribute($value)
    {
        $this->attributes['date_end'] = $this->convertDate($value);
    }

    private function convertDate($value)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    public function customer_group()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id', 'id');
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (string)$value;
    }

}
