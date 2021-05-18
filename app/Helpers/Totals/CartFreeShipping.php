<?php

namespace App\Helpers\Totals;


use App\Helpers\HelperFunction;
use App\Models\Total;
use Cart;
use Darryldecode\Cart\CartCondition;

class CartFreeShipping implements CartTotalInterface
{

    private static $condition_template = [
        'name' => 'free_shipping',
        'type' => 'shipping',
        'target' => 'total',
        'value' => '0'
    ];

    public static function calculate()
    {
        $setting = Total::where('code', 'FreeShipping')->select('setting')->first()->decoded_setting;

        $total = Cart::getTotal();

        if( $total > $setting->min || $total > $setting->min_recomended ){

            $is_recommended = $total > $setting->min_recomended ? true : false;

            self::$condition_template['attributes'] = [
                'is_recommended' => $is_recommended,
                'template' => (string)view('totals.CartFreeShipping')
            ];

            Cart::condition( new CartCondition(self::$condition_template));

        }
    }
}