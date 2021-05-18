<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2019-05-13
 * Time: 20:07
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Telephone implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $res = true;

        $clear_value = preg_replace("/[^0-9]/", '', $value);

        if ( (mb_strlen($value) !== mb_strlen($clear_value)) && (mb_strlen($value) !== 10 && mb_strlen($value) !== 12 && mb_strlen($clear_value) !== 10 && mb_strlen($clear_value) !== 12) ) {

            $res = false;
        }

        return $res;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.custom.telephone.format');
    }
}