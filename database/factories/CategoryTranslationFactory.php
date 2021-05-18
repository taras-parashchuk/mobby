<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\CategoryTranslation;

$factory->define(CategoryTranslation::class, function () {

    $faker = Faker\Factory::create('uk_UA');

    return [
        'locale' => 'uk',
        'name' => $faker->name,
        'description' => $faker->text,
        'meta_title' => $faker->name,
        'meta_description' => $faker->text
    ];

});
