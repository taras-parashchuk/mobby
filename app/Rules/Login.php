<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Login implements Rule
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

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            if ((mb_strlen($value) !== mb_strlen(preg_replace("/[^0-9]/", '', $value))) || (mb_strlen($value) !== 10 && mb_strlen($value) !== 12)) {
                $res = false;
            }
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
        return trans('validation.custom.login.format');
    }
}
