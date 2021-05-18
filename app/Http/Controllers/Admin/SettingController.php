<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FileManager;
use App\Models\AttributeGroupTranslation;
use App\Models\AttributeTranslation;
use App\Models\AttributeValueTranslation;
use App\Models\BannerSlide;
use App\Models\CategoryTranslation;
use App\Models\InformationTranslation;
use App\Models\LocationTranslation;
use App\Models\OrderStatusTranslation;
use App\Models\PriceUnitTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Setting;
use App\Models\StockStatusTranslation;
use App\Models\UserGroupTranslation;
use App\Rules\Telephone;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Information;
use App\Models\Location;
use App\Models\OrderStatus;
use App\Models\PriceUnit;
use App\Models\StockStatus;
use App\Models\UserGroup;
use Illuminate\Http\File;


class SettingController extends Controller
{

    public function index()
    {
        $settings = Setting::all();

        $thumbnails = [];

        $settings->each(function ($setting) use (&$thumbnails) {
            switch ($setting->type) {
                case 'integer':
                    $setting->value = (int)$setting->value;
                    break;
                case 'array':
                    $setting->value = json_decode($setting->value);
                    break;
                case 'boolean':
                    $setting->value = (bool)$setting->value;
                    break;
            }

            if ($setting->name == 'header_logo') {
                if ($setting->value && $thumb = \App\Helpers\Image::getFileManagerThumb('settings', 'main', $setting->value)) {
                    $thumbnails['header_logo_thumb'] = $thumb;
                } else {
                    $thumbnails['header_logo_thumb'] = null;
                }
            } elseif ($setting->name == 'footer_logo') {
                if ($setting->value && $thumb = \App\Helpers\Image::getFileManagerThumb('settings', 'main', $setting->value)) {
                    $thumbnails['footer_logo_thumb'] = $thumb;
                } else {
                    $thumbnails['footer_logo_thumb'] = null;
                }
            } elseif ($setting->name == 'favicon_src') {
                if ($setting->value && $thumb = \App\Helpers\Image::getFileManagerThumb('settings', 'main', $setting->value)) {
                    $thumbnails['favicon_thumb'] = $thumb;
                } else {
                    $thumbnails['favicon_thumb'] = null;
                }
            }

        });

        return compact('settings', 'thumbnails');
    }

    public function store(Request $request)
    {
        $request->validate([
            'main.location' => ['nullable', 'exists:locations,id'],
            'main.sender_email' => ['required', 'email'],
            'main.emails' => ['nullable'],
            'main.site_language' => ['required', 'exists:languages,locale'],
            'main.admin_language' => ['required', 'exists:languages,locale'],
            'main.currency' => ['required', 'exists:currencies,code'],
            'main.maintenance' => ['required', 'boolean'],
            'main.user_group_before_register' => ['required', 'exists:user_groups,id'],
            'main.user_group_after_register' => ['required', 'exists:user_groups,id'],
            'main.order_status_after_create' => ['required', 'exists:order_statuses,id'],
            'main.order_status_refused' => ['required', 'exists:order_statuses,id'],
            'main.link_facebook' => ['nullable', 'url'],
            'main.link_instagram' => ['nullable', 'url'],
            'main.link_youtube' => ['nullable', 'url'],
            'main.link_telegram' => ['nullable', 'url']
        ]);

        foreach ($request->all() as $group_name => $group) {

            foreach ($group as $key => $val) {

                if (($key === 'site_language' && Setting::get('site_language') !== $val) || ($key === 'admin_language' && Setting::get('admin_language') !== $val)) {

                    ini_set('max_execution_time', -1);

                    $is_changed_language = true;

                    $models = [
                        'product_id' => ProductTranslation::class,
                        'article_id' => ArticleTranslation::class,
                        'attribute_id' => AttributeTranslation::class,
                        //'group_id' => AttributeGroupTranslation::class,
                        'attribute_value_id' => AttributeValueTranslation::class,
                        'category_id' => CategoryTranslation::class,
                        'information_id' => InformationTranslation::class,
                        'location_id' => LocationTranslation::class,
                        'order_status_id' => OrderStatusTranslation::class,
                        'price_unit_id' => PriceUnitTranslation::class,
                        'stock_status_id' => StockStatusTranslation::class,
                        'user_group_id' => UserGroupTranslation::class,
                        'banner_id' => BannerSlide::class
                    ];

                    foreach ($models as $primary_key => $model) {

                        $model::insertOrIgnore($model::whereIn('locale', [Setting::get($key), $val])->groupBy($primary_key)->havingRaw('COUNT(locale) = 1')->get()->map(function($row) use ($val){

                            if(isset($row['id'])) unset($row['id']);

                            $row->locale = $val;

                            return $row;

                        })->toArray());
                    }
                }else{
                    if(!isset($is_changed_language)) $is_changed_language = false;
                }

                if (($key === 'user_group_before_register' && Setting::get('user_group_before_register') !== $val) || ($key === 'user_group_after_register' && Setting::get('user_group_after_register') !== $val)) {

                    Product::get()->each(function ($product) {
                        Product::setPrices($product);
                    });
                }

                if (is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                    Setting::add($key, $val, $group_name, 'array');
                } elseif (is_int($val)) {
                    Setting::add($key, $val, $group_name, 'integer');
                } elseif (is_bool($val)) {
                    Setting::add($key, $val, $group_name, 'boolean');
                } else {
                    Setting::add($key, $val, $group_name);
                }
            }

        }

        return response()->json(
            [
                'text' => trans('form.result.success-updated'),
                'is_changed_language' => $is_changed_language
            ]
        );
    }

    public function setTmpFile(Request $request)
    {
        $file = $request->file('file');

        $file->storeAs('tmp-', $file->getClientOriginalName(), 'public');

        return Storage::url('tmp-' . '/' . $file->getClientOriginalName());
    }
}