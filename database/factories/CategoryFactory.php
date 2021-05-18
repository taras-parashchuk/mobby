<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;
use App\Models\Category;
use Illuminate\Support\Str;

$factory->define(Category::class, function (Faker $faker) {
    return  [
        'image' => $faker->image('public/uploads/images/catalog', 200, 200),
        'parent_id' => 0,
        'sort_order' => $faker->randomDigit(0),
        'status' => 1,
        'slug' => $faker->unique()->slug(2, false),
    ];
});
