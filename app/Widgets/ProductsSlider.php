<?php

namespace App\Widgets;

use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductRelated;
use App\Models\ProductSpecial;
use App\Models\Setting;
use Arrilot\Widgets\AbstractWidget;
use App\Helpers\Image;
use DB;
use Request;

class ProductsSlider extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    private $productWith = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {

        if(!$this->productWith){
            $this->productWith = [
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
                'stock_status' => function($q){
                    $q->enabled();
                    $q->select('id');
                },
                'primary_variant' => function ($q) {
                    $q->without('translates');
                    $q->select('id', 'image', 'type', 'quantity', 'sku', 'slug', 'products.main_id');
                }];
        }

        if ($this->config['title']) {
            if (isset($this->config['title']->{\App::getLocale()})) {
                $this->config['title'] = $this->config['title']->{\App::getLocale()};
            } elseif (isset($this->config['title']->{Setting::get('site_language')})) {
                $this->config['title'] = $this->config['title']->{Setting::get('site_language')};
            } else {
                $this->config['title'] = '';
            }
        } else {
            $this->config['title'] = '';
        }

        $products = collect();

        if (is_string($this->config['product'])) {
            switch ($this->config['product']) {
                case 'special':
                    $products = Product::enabledAndDelivered()
                        ->notVariant()
                        ->without('translates')
                        ->whereHas('special')
                        ->with($this->productWith)
                        ->orderBy('date_available', 'DESC')
                        ->limit($this->config['limit'])
                        ->select('id', 'image', 'type', 'stock_status_id', 'price_unit_id', 'quantity', 'multiplicity', 'minimum', 'sku', 'slug', 'products.main_id', 'products.primary_variant_id', 'currency_code')
                        ->get();
                    break;
                case 'latest':
                    $products = Product::enabledAndDelivered()
                        ->notVariant()
                        ->without('translates')
                        ->with($this->productWith)
                        ->orderBy('date_available', 'DESC')
                        ->limit($this->config['limit'])
                        ->select('id', 'image', 'type', 'stock_status_id', 'price_unit_id', 'quantity', 'multiplicity', 'minimum', 'sku', 'slug', 'products.main_id', 'products.primary_variant_id', 'currency_code')
                        ->get();
                    break;
                case 'bestseller':
                    $products = OrderProduct::whereHas('product', function ($q) {
                        $q->select('id');
                        $q->enabledAndDelivered();
                    })->with([
                        'product' => function ($q) {
                            $q->select('id', 'image', 'type', 'price_unit_id', 'stock_status_id', 'quantity', 'multiplicity', 'minimum', 'sku', 'slug', 'products.main_id', 'products.primary_variant_id', 'currency_code');
                            $q->without('translates');
                        },
                        'product.price_unit' => function ($q) {
                            $q->select('id');
                            $q->where('status', true)->where('display', true);
                        },
                        'product.prices' => function ($q) {
                            $q->select('user_group_id', 'product_id', 'price_min', 'price_max', 'price');
                        },
                        'product.special' => function ($q) {
                            $q->select('product_id', 'price');
                        },
                        'product.discount' => function($q){
                            $q->select('product_id', 'price');
                        },
                        'product.primary_variant' => function ($q) {
                            $q->without('translates');
                            $q->select('id', 'image', 'type', 'quantity', 'sku', 'slug', 'products.main_id');
                        }
                    ])
                        ->select('product_id', DB::raw('SUM(quantity) as total'))
                        ->groupBy('product_id')
                        ->orderBy('total', 'DESC')
                        ->limit($this->config['limit'])
                        ->get()->map(function($order_product){
                            return $order_product->product;
                        });
                    break;
                case 'visited':
                    if ($visited_ids = Product::getVisitedProducts()) {

                        $excepted_ids = [];

                        if (\Route::currentRouteName() == 'product') {

                            $excepted_product = Product::select('id', 'main_id', 'type')->find(Request::route()->parameter('id'));

                            if ($excepted_product->type === 3) {
                                $excepted_ids[] = $excepted_product->main_id;
                            } else {
                                $excepted_ids[] = $excepted_product->id;
                            }
                        }

                        $products = Product::enabledAndDelivered()
                            ->notVariant()
                            ->without('translates')
                            ->visited($excepted_ids)
                            ->with($this->productWith)
                            ->whereIn('products.id', $visited_ids);

                        $products = $products->orderByRaw('FIELD(products.id,' . implode(',', $visited_ids) . ')')
                            ->select('id', 'image', 'type', 'stock_status_id', 'price_unit_id', 'quantity', 'multiplicity', 'minimum', 'sku', 'slug', 'products.main_id', 'products.primary_variant_id', 'currency_code')
                            ->get();

                    }
                    break;
                case 'related':
                    if (\Route::currentRouteName() == 'product') {

                        $recipient = Product::find(Request::route()->parameters['id']);

                        $source_ids = ProductRelated::where('recipient_id', $recipient->type == 3 ? $recipient->main_id : $recipient->id)->get('source_id');

                        if ($source_ids->count()) {
                            $products = Product::enabledAndDelivered()
                                ->notVariant()
                                ->without('translates')
                                ->with($this->productWith)
                                ->whereIn('id', $source_ids)
                                ->limit($this->config['limit'])
                                ->select('id', 'image', 'type', 'stock_status_id', 'price_unit_id', 'quantity', 'multiplicity', 'minimum', 'sku', 'slug', 'products.main_id', 'products.primary_variant_id', 'currency_code')
                                ->get();
                        }
                    }
                    break;
            }
        } elseif (is_array($this->config['product'])) {
            $ids = array_column($this->config['product'], 'id');

            if ($ids) {
                $products = Product::enabledAndDelivered()
                    ->notVariant()
                    ->without('translates')
                    ->with($this->productWith)
                    ->whereIn('products.id', $ids)
                    ->limit($this->config['limit'])
                    ->select('id', 'image', 'type', 'stock_status_id', 'price_unit_id', 'quantity', 'multiplicity', 'minimum', 'sku', 'slug', 'products.main_id', 'products.primary_variant_id', 'currency_code')
                    ->get();
            }
        }

        if ($products->count()) {

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
            }]);

            $products->each->append('href', 'hit', 'translate', 'priceFormat', 'pricesFormat',
                'specialFormat', 'available', 'special_diff', 'stock_title',
                'translate');

            $products->each(function ($product) {
                $product->thumb = $product->resizeImage($this->config['width'], $this->config['height']);
            });

            if ($products->count()) {
                return [
                    'view' => 'widgets.products_slider',
                    'data' => [
                        'has_container' => true,
                        'config' => (object)$this->config,
                        'products' => $products
                    ]
                ];
            }
        }
    }
}
