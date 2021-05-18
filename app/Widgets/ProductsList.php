<?php

namespace App\Widgets;

use App\Helpers\HelperFunction;
use App\Helpers\Image;
use App\Models\Setting;
use Arrilot\Widgets\AbstractWidget;
use App\Models\Product;

class ProductsList extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        if($this->config['title']){
            if(isset($this->config['title']->{\App::getLocale()})){
                $this->config['title'] = $this->config['title']->{\App::getLocale()};
            }elseif(isset($this->config['title']->{Setting::get('site_language')})){
                $this->config['title'] = $this->config['title']->{Setting::get('site_language')};
            }else{
                $this->config['title'] = '';
            }
        }else{
            $this->config['title'] = '';
        }

        if($this->config['product']){
            $productsBuilder = Product::enabledAndDelivered()
                ->notVariant()
                ->without('translates')
                ->with([
                    'prices' => function ($q) {
                        $q->select('user_group_id', 'product_id', 'price_min', 'price_max', 'price');
                    },
                    'price_unit' => function ($q) {
                        $q->select('id');
                        $q->where('status', true)->where('display', true);
                    },
                    'special' => function ($q) {
                        $q->select('product_id', 'price');
                    },
                    'discount' => function($q){
                        $q->select('product_id', 'price');
                    },
                    'primary_variant' => function ($q) {
                        $q->without('translates');
                        $q->select('id', 'image', 'type', 'quantity', 'sku', 'slug', 'products.main_id');
                    }]);
            if (is_string($this->config['product'])) {
                if ($this->config['product'] === 'latest') {
                    $productsBuilder->orderBy('date_available', 'DESC');
                }
            } else {

            }

            $products = $productsBuilder->limit($this->config['limit'])
                ->select('id', 'image', 'type', 'stock_status_id', 'price_unit_id', 'quantity', 'multiplicity', 'minimum', 'sku', 'slug', 'products.main_id', 'products.primary_variant_id', 'currency_code')
                ->get();

            $products = $products->map(function($product){

                if($product->type === 2){

                    $oldProduct = $product;

                    $product = $oldProduct->primary_variant;

                    unset($oldProduct->primary_variant);

                    $product->main_product = $oldProduct;

                    $product->prices = $oldProduct->prices;
                }

                return $product;

            });

            $products->load(['translates' => function ($q) {
                $q->select('product_id', 'name', 'locale');
            },]);

            $products->each->append('translate', 'specialFormat', 'priceFormat', 'stock_title', 'pricesFormat', 'available', 'href');

            $products->each(function ($product) {
                $product->thumb = $product->resizeImage($this->config['width'], $this->config['height']);

                if($product->type == 2){
                    $product->translate = $product->primary_variant->translate;
                }
            });

            if(count($products)){
                return [
                    'view' => 'widgets.products_list',
                    'data' => [
                        'products' => $products,
                        'config' => $this->config,
                    ]
                ];
            }
        }
    }
}
