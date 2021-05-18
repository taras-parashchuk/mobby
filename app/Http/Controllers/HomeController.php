<?php

namespace App\Http\Controllers;

use App\Helpers\Excel\Imports\ExcelImport;
use App\Helpers\HelperFunction;
use App\Jobs\SupplierSync;
use App\Models\Article;
use App\Models\Attribute;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Information;
use App\Models\Layout;
use App\Models\LayoutModule;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SupplierProduct;
use App\Models\UserGroup;
use App\Services\Supplier\SupplierCifroteh;
use Illuminate\Support\Arr;
use SEO;
use Config;


class HomeController extends Controller
{
    public function index()
    {
        $home_page_info = json_decode(Setting::get('home_page'));

        $meta = Arr::first($home_page_info ?? [], function ($translate) {
            return $translate->locale === app()->getLocale();
        });

        if (!$meta) {
            $meta = Arr::first($home_page_info ?? [], function ($translate) {
                return $translate->locale === Setting::get('site_language');
            });
        }

        SEO::setTitle($meta->meta_title ?? Setting::get('company_name'));
        SEO::setDescription($meta->meta_description ?? Setting::get('company_name'));

        return view('pages.home');
    }

    public function test()
    {
        $s = new SupplierCifroteh();

        $products = json_decode($s->service->request("/api/rest/getstock?limit=100000&page=1", 'GET', null, array('Accept' => '*/*')), true);

        echo '<pre>';
        print_r($products);
        die();
    }

    public function load_users()
    {
        //set_time_limit(0);

        //\Excel::import(new ExcelUsersImport, 'uploads/legkapokupka.com.ua/files/bono.xlsx');
    }

    public function gd()
    {
        imagewebp('');
    }

    private function t()
    {
        //створення копій даних для створення демо сайтів з контентом
        /*
         * $theme = 'little';
         *
         *

        $result = collect();

        $items = UserGroup::with([
            'translates'
        ])->get();

        $items->each(function($item) use ($result){
            $result->push($item);
        });

        \Storage::disk('uploads')->put('demo-content/default/user_groups/user_groups.json', json_encode($result->toArray(), JSON_UNESCAPED_UNICODE));

        $result = collect();

        $items = Banner::with(['slides'])->get();

        $items->each(function($item) use ($result){
            $result->push($item);
        });

        \Storage::disk('uploads')->put('demo-content/'.$theme.'/banners/banners.json', json_encode($result->toArray(), JSON_UNESCAPED_UNICODE));

        $result = collect();

        $items = Information::with(['translates'])->get();

        $items->each(function($item) use ($result){
            $result->push($item);
        });

        \Storage::disk('uploads')->put('demo-content/'.$theme.'/informations/informations.json', json_encode($result->toArray(), JSON_UNESCAPED_UNICODE));

        $result = collect();

        $items = Article::with(['translates'])->get();

        $items->each(function($item) use ($result){
            $result->push($item);
        });

        \Storage::disk('uploads')->put('demo-content/'.$theme.'/articles/articles.json', json_encode($result->toArray(), JSON_UNESCAPED_UNICODE));

        $result = collect();

        $items = Attribute::with(['translates', 'values.translates'])->get();

        $items->each(function($item) use ($result){
            $result->push($item);
        });

        \Storage::disk('uploads')->put('demo-content/'.$theme.'/attributes/attributes.json', json_encode($result->toArray(), JSON_UNESCAPED_UNICODE));

        $result = collect();

        $items = Category::with(['translates'])->get();

        $items->each(function($item) use ($result){
            $result->push($item);
        });

        \Storage::disk('uploads')->put('demo-content/'.$theme.'/categories/categories.json', json_encode($result->toArray(), JSON_UNESCAPED_UNICODE));

        $result = collect();

        $items = Product::where('type', '<>', 3)->with([
            'translates',
            'prices',
            'to_categories',
            'to_compare_categories',
            'to_attributes.to_values',
            'specials',
            'discounts',
            'images',
            'to_related',
            'variants.translates',
            'variants.discounts',
            'variants.specials',
            'variants.images',
            'variants.variant_attribute_values',


        ])->get();

        $items->each(function($item) use ($result){
            $result->push($item);
        });

        \Storage::disk('uploads')->put('demo-content/'.$theme.'/products/products.json', json_encode($result->toArray(), JSON_UNESCAPED_UNICODE));

         */
    }
}
