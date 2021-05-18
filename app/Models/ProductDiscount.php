<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['user_group_id', 'quantity', 'price', 'date_start', 'priority', 'date_end'];

    protected $casts = [
        'user_group_id' => 'integer'
    ];



    public function getDateStartJsAttribute()
    {
        return $this->date_start ? date('c', strtotime($this->date_start)) : null;
    }

    public function getDateEndJsAttribute()
    {
        return $this->date_end ? date('c', strtotime($this->date_end)) : null;
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

    public function product()
    {
        return $this->belongsTo(Product::class);
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
