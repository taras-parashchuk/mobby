<?php

return [

    'elko' => [
        'login' => env('ELKO_LOGIN'),
        'password' => env('ELKO_PASSWORD')
    ],

    'brain' => [
        'login' => env('BRAIN_LOGIN'),
        'password' => env('BRAIN_PASSWORD')
    ],

    'cifroteh' => [
        'consumer_key' => env('CIFROTEH_CONSUMER_KEY'),
        'consumer_secret' => env('CIFROTEH_CONSUMER_SECRET'),
        'accessToken' => env('CIFROTEH_ACCESS_TOKEN'),
        'accessTokenSecret' => env('CIFROTEH_ACCESS_SECRET')
    ]

];