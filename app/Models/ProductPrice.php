<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    //
    protected $hidden = ['id'];

    protected $casts = [
        'price' => 'float'
    ];



    protected $fillable = ['user_group_id'];

    public static function refreshPrices($currency_code, $currency_exchange_rate)
    {
        self::where('currency_code', $currency_code)->get()->each(function ($product) use ($currency_exchange_rate) {
            $product->price = $product->vendor_price / $currency_exchange_rate;

            $product->save();
        });
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (string)($value / $this->getRate());
    }

    public function setPriceMinAttribute($value)
    {
        $this->attributes['price_min'] = (string)($value / $this->getRate());
    }

    public function setPriceMaxAttribute($value)
    {
        $this->attributes['price_max'] = (string)($value / $this->getRate());
    }

    private function getRate()
    {
        if ($this->currency_code !== Setting::get('currency')) {
            return Currency::where('code', $this->currency_code)->value('exchange_rate');
        } else {
            return 1;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {

            unset($model->currency_code);

        });
    }
}
