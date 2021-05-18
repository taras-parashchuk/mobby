<?php

namespace App\Services;

use Illuminate\Auth\Passwords\PasswordBroker as BasePasswordBroker;

class CustomPasswordBroker extends BasePasswordBroker
{
// override the functions that you need here

    protected function validatePasswordWithDefaults(array $credentials)
    {
        [$password, $confirm] = [
            $credentials['password'],
            $credentials['password_confirmation'],
        ];

        return $password === $confirm && mb_strlen($password) >= 6;
    }
}