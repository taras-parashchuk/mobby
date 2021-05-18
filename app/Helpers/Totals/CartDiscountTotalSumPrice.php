<?php

namespace App\Helpers\Totals;

use App\Helpers\HelperFunction;
use App\Models\Total;
use Cart;
use Darryldecode\Cart\CartCondition;


class CartDiscountTotalSumPrice implements CartTotalInterface
{
    private static $condition_template = [
        'name' => 'discount_about_subtotal',
        'type' => 'discount',
        'target' => 'subtotal',
        'value' => '0',
        'attributes' => [
            'template' => '',
            'class' => CartDiscountTotalSumPrice::class
        ]
    ];

    public static function calculate()
    {
        $discounts = Total::where('code', 'DiscountTotalSumPrice')->first()->decoded_setting->discounts;

        $discounts = collect($discounts);

        $subtotal = Cart::getSubTotalWithoutConditions();

        $discountInfo = $discounts->first(function($discount) use ($subtotal){
            return ($discount->user_group_id === 0 || $discount->user_group_id == auth()->user()->getGroupId()) && ($discount->min <= $subtotal && $discount->max >= $subtotal);
        });

        if($discountInfo){

            if($discountInfo->type === 1){
                self::$condition_template['value'] = '-'.$discountInfo->discount.'%';
            }elseif($discountInfo->type === 2){
                self::$condition_template['value'] = '-'.$discountInfo->discount;
            }

            Cart::condition( new CartCondition(self::$condition_template));

            $discount = currency(abs(Cart::getSubTotal() - Cart::getSubTotalWithoutConditions()), config('settings.main_currency'), currency()->getUserCurrency());

            self::$condition_template['attributes']['template'] = (string)view('totals.CartDiscountTotalSumPrice', [
                'discount' => $discount
            ]);

            Cart::condition( new CartCondition(self::$condition_template));

        }
    }

    public static function getCalculatedValue()
    {
        return abs(Cart::getSubTotal() - Cart::getSubTotalWithoutConditions());
    }

}