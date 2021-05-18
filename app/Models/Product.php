<?php

namespace App\Models;

use App\Helpers\CatalogFilter;
use App\Helpers\Image;
use App\Helpers\ModelHelper;
use App\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Cookie;

class Product extends Model
{
    use ModelHelper;

    #about type
    /*
     * 1 - simple product
     * 2 - variation product
     * 3 - variant
     */

    protected $with = ['translates'];

    private static $filters = [
        'visited_offset' => 20
    ];

    protected $casts = [
        'type' => 'integer',
        'vendor_price' => 'float',
        'sort_order' => 'integer',
        'status' => 'boolean',
        'stock_status_id' => 'integer',
        'minimum' => 'integer',
        'category_id' => 'integer',
        'image' => 'string',
        'filemanager_thumb' => 'string',
        'primary_variant_id' => 'integer',
        'main_id' => 'integer',
        'sku' => 'string',
        'price_unit_id' => 'integer',
        'is_zamanyha' => 'boolean',
        'warehouse_quantity' => 'integer'
    ];

    protected $guarded = [];

    protected $table = 'products';

    const MODEL_TYPE = 'products';

    public function getAvailableAttribute()
    {

        if ($this->type === 3) {
            $parent_model = $this->main_product;
        } else {
            $parent_model = $this;
        }

        if (isset($this->attributes['quantity'])) return (int)($this->attributes['quantity'] > 0 && $this->attributes['quantity'] >= $parent_model->attributes['minimum'] && $this->attributes['quantity']);
    }

    public function getStockTitleAttribute()
    {
        if ($this->stock_status && $this->quantity > 0) {
            return $this->stock_status->translate->title;
        } elseif ($this->quantity < 1) {
            return trans('catalog.text.out_stock', [], app()->getLocale());
        } else {
            return trans('catalog.text.in_stock', [], app()->getLocale());
        }
    }

    public function scopeWithTranslate($query)
    {
        return $query->leftJoin('product_translations as pt', function ($join) {
            $join->on('pt.product_id', 'products.id');
        })
            ->where('pt.locale', app()->getLocale())
            ->orWhere('pt.locale', Setting::get('site_language'));
    }

    public function scopeWithDiscount($query)
    {
        /* $sql = '(SELECT * FROM product_discounts WHERE ';

         if (\Auth::user()) {
             $sql .= 'user_group_id = ' . \Auth::user()->group_id;
         } else {
             $sql .= 'user_group_id IS NULL';
         }*/

        //$sql .= ' AND quantity = 1 AND date_start < NOW() AND (date_end > NOW() OR date_end IS NULL) ORDER BY priority DESC LIMIT 1) as pd';

        return $query->leftJoin('product_discounts as pd', function ($join) {
            $join->on('pd.product_id', '=', 'products.id');
            $join->on('pd.quantity', '=', \DB::raw(1));
        });
    }

    public function scopeWithSpecial($query)
    {
        return $query->leftJoin('product_specials as ps', function ($join) {
            $join->on('ps.product_id', '=', 'products.id');
        });
    }

    public function getPriceFormatAttribute()
    {
        $price = currency($this->discountOrPrice, config('settings.main_currency'), currency()->getUserCurrency());

        $price_unit = $this->getPriceUnitFormat();

        return $price . $price_unit;
    }

    public function getPricesFormatAttribute()
    {
        if ($this->type !== 1) {

            $min_price = currency($this->price_info->price_min, config('settings.main_currency'), currency()->getUserCurrency());
            $max_price = currency($this->price_info->price_max, config('settings.main_currency'), currency()->getUserCurrency());

            $price = $min_price != $max_price ? $min_price . ' - ' . $max_price : $min_price;

            $price_unit = $this->getPriceUnitFormat();

            return $price . $price_unit;
        }
    }

    public function getSpecialFormatAttribute()
    {
        if ($this->special) {
            if (is_numeric($this->special)) {
                $price = currency($this->special, $this->currency_code);
            } else {
                $price = currency($this->special->price, $this->currency_code, currency()->getUserCurrency());
            }

            $price_unit = $this->getPriceUnitFormat();

            return $price . $price_unit;

        }
    }

    public function getDiscountOrPriceAttribute()
    {
        if ($this->discount) {
            if (is_numeric($this->discount)) {
                return app('currency')->convert($this->discount, $this->currency_code, config('settings.main_currency'), false);
            } else {
                return app('currency')->convert($this->discount->price, $this->currency_code, config('settings.main_currency'), false);
            }
        } else {
            try {
                return $this->price_info->price;
            } catch (\Exception $exception) {
                dd($this->price_info);
            }
        }
    }

    public function getSpecialOrCalculatePriceAttribute()
    {
        return $this->specialConvertedPrice ?? $this->discountOrPrice;
    }

    public function getSpecialConvertedPriceAttribute()
    {
        if ($this->special) {
            if (is_numeric($this->special)) {
                return app('currency')->convert($this->special, $this->currency_code, config('settings.main_currency'), false);
            } else {
                return app('currency')->convert($this->special->price, $this->currency_code, config('settings.main_currency'), false);
            }
        } else {
            return null;
        }
    }

    public function getSpecialDiffAttribute()
    {
        if ($this->special) {

            $special_price = currency(is_numeric($this->special) ? $this->special : $this->special->price, $this->currency_code, config('settings.main_currency'), false);

            return trans('catalog.special.diff', ['diff' => currency(abs($special_price - $this->DiscountOrPrice), config('settings.main_currency'), currency()->getUserCurrency())]);
        }
    }

    public function getHrefAttribute()
    {
        if ($this->type === 2 && $this->primary_variant) {
            return route('product', [
                'id' => $this->primary_variant->id,
                'slug' => $this->primary_variant->slug
            ]);
        } else {
            return route('product', [
                'id' => $this->id,
                'slug' => $this->slug
            ]);
        }
    }

    public function getModelAttribute($model)
    {
        if ($this->type === 2 && $this->primary_variant) {
            return $this->primary_variant->sku ?? trans('common.text.empty_model');
        } else {
            return $model ?? trans('common.text.empty_model');
        }
    }

    public function scopeTranslate($query)
    {
        return $query->join('product_translations as pt', 'pt.product_id', 'products.id')
            ->where('locale', app()->getLocale());
    }

    public function translates()
    {
        return $this->hasMany('App\Models\ProductTranslation')
            ->whereIn('locale', Language::getOnlyActive()->pluck('locale'));
    }

    public function scopeInCategory($query, $category_id)
    {
        return $query->whereIn('id', ProductToCategory::where('category_id', $category_id)->select('product_id'));
    }

    public function scopeFilter($query, CatalogFilter $Filter)
    {
        return $Filter->apply($query);
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function manufacturer()
    {
        return $this->belongsTo('App\Models\Manufacturer');
    }

    public function testimonials()
    {
        return $this->hasMany('App\Models\Testimonial');
    }

    public function images()
    {
        return $this->hasMany('App\Models\ImageToProduct');
    }

    public function getCatalogThumbAttribute()
    {
        return $this->resizeImage(200, 200);
    }

    public function secondImage()
    {
        return $this->hasOne(ImageToProduct::class, 'product_id', 'id')
            ->where('sort_order', 1);
    }

    public function getSecondCatalogThumbAttribute()
    {
        if ($this->secondImage) {
            return $this->resizeImage(200, 200, $this->secondImage->src);
        } else {
            return null;
        }
    }

    public function getThumb()
    {
        return $this->resizeImage(473, 473);
    }

    public function getPopup()
    {
        return $this->resizeImage(1000, 1000);
    }

    public function getRelatedThumb()
    {
        return $this->resizeImage(70, 70);
    }

    public function resizeImage($width, $height, $path = null)
    {
        $id = $this->type === 3 ? $this->main_id : $this->id;

        if (!$path) {
            $path = $this->image;
        }

        return \App\Helpers\Image::resize($path ? "products/$id/$path" : '', $width, $height);
    }


    public function specials()
    {
        return $this->hasMany('App\Models\ProductSpecial');
    }

    public function special()
    {
        return $this->hasOne('App\Models\ProductSpecial')
            ->where(function ($query) {

                if (\Auth::user()) {
                    $query->where('user_group_id', \Auth::user()->group_id);
                } else {
                    $query->where('user_group_id', Setting::get('user_group_before_register'));
                }

                $query->where(function ($query) {
                    $query->where('date_start', '<', now())
                        ->orWhere('date_start', null);
                });

                $query->where(function ($query) {
                    $query->where('date_end', '>', now())
                        ->orWhere('date_end', null);
                });
            });
    }

    public function discount()
    {
        return $this->hasOne('App\Models\ProductDiscount')
            ->where(function ($query) {
                $query->where('user_group_id', \Auth::user()->group_id ?? Setting::get('user_group_before_register'));
                $query->where('quantity', 1);

                $query->where(function ($query) {
                    $query->where('date_start', '<', now())
                        ->orWhere('date_start', null);
                });

                $query->where(function ($query) {
                    $query->where('date_end', '>', now())
                        ->orWhere('date_end', null);
                });
            });
    }

    public function discounts()
    {
        return $this->hasMany('App\Models\ProductDiscount');
    }

    public function stock_status()
    {
        return $this->belongsTo('App\Models\StockStatus');
    }

    public function price_unit()
    {
        return $this->belongsTo('App\Models\PriceUnit');
    }

    public function getPriceUnitFormat()
    {

        if ($this->type === 3) {
            $model = $this->main_product;
        } else {
            $model = $this;
        }

        return $model->price_unit && $model->price_unit->display ? '/' . $model->price_unit->translate->name : '';
    }


    public function purchased()
    {
        return $this->hasMany('App\Models\OrderProduct', 'product_id', 'id');
    }

    public function scopeVisited($query, array $escape_ids = [])
    {
        return $query->whereIn('id', array_filter(self::getVisitedProducts(), function ($id) use ($escape_ids) {
            return !in_array($id, $escape_ids);
        }));
    }

    public static function setVisitedProduct($product_id)
    {
        $visited_products = self::getVisitedProducts();

        array_unshift($visited_products, (int)$product_id);

        $visited_products = array_unique($visited_products);

        if (count($visited_products) > self::$filters['visited_offset']) {
            array_splice($visited_products, self::$filters['visited_offset'] + 1);
        }

        return cookie('visited_products', json_encode($visited_products), 60 * 60 * 24 * 10);
    }

    public static function getVisitedProducts(): array
    {
        return (array)json_decode(Cookie::get('visited_products'), true);
    }

    public function getFilemanagerThumbAttribute()
    {
        if ($this->image) {

            if ($this->type === 3) {
                $id = $this->main_product->id;
            } elseif ($this->type === 2) {
                $id = $this->main_id;
            } else {
                $id = $this->id;
            }


            if ($thumb = \App\Helpers\Image::getFileManagerThumb('products', $id, $this->image)) {
                return $thumb;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function categories()
    {
        return $this->hasManyThrough(
            'App\Models\Category',
            'App\Models\ProductToCategory',
            'product_id',
            'id',
            'id',
            'category_id');
    }

    public function compare_categories()
    {
        return $this->hasManyThrough(
            'App\Models\Category',
            'App\Models\ProductToCompareCategory',
            'product_id',
            'id',
            'id',
            'category_id'
        );
    }

    public function to_categories()
    {
        return $this->hasMany('App\Models\ProductToCategory');
    }

    public function to_compare_categories()
    {
        return $this->hasMany('App\Models\ProductToCompareCategory');
    }

    public function to_attributes()
    {
        return $this->hasMany('App\Models\AttributeToProduct', 'product_id', 'id');
    }

    public function to_attributes_primary()
    {
        return $this->to_attributes()->where('main', true);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class, 'product_id', 'id');
    }

    public function getPriceInfoAttribute()
    {
        return $this->prices->firstWhere('user_group_id', User::getGroupId());
    }

    public function scopeWithPriceInfo($query)
    {
        return $query->join('product_prices as pp', function ($join) {
            $join
                ->on('pp.product_id', 'products.id')
                ->on('pp.user_group_id', \DB::raw(User::getGroupId()));
        });
    }

    public function variants()
    {
        return $this->hasMany(
            'App\Models\Product',
            'main_id',
            'id'
        );
    }

    public function to_variant_attribute_values()
    {
        return $this->hasMany(ProductVariantAttributeValue::class, 'product_id', 'id');
    }

    public function variant_attribute_values()
    {
        return $this->hasManyThrough(
            AttributeValue::class,
            ProductVariantAttributeValue::class,
            'product_id',
            'id',
            'id',
            'attribute_value_id');
    }

    public function scopeEnabled($query)
    {
        $query->where('status', 1);
    }

    public function scopeEnabledAndDelivered($query)
    {
        $query->where([
            ['status', 1],
            ['date_available', '<=', now()]
        ]);
    }

    public function scopeNotVariant($query)
    {
        $query->where('type', '<>', 3);
    }

    public function scopeSimple($query)
    {
        $query->where('type', 1);
    }

    public function primary_variant()
    {
        return $this->hasOne(Product::class, 'id', 'primary_variant_id');
    }

    public function main_product()
    {
        return $this->hasOne(Product::class, 'id', 'main_id');
    }

    public function variants_all_attribute_values()
    {
        return $this->hasManyThrough(
            AttributeValue::class,
            ProductVariantAttributeValue::class,
            'main_id',
            'id',
            'id',
            'attribute_value_id'
        );
    }

    public function to_related()
    {
        return $this->hasMany(ProductRelated::class, 'recipient_id', 'id');
    }

    public function setVendorPriceAttribute($value)
    {
        $this->attributes['vendor_price'] = (string)$value;
    }

    public function relateds()
    {
        return $this->hasManyThrough(
            Product::class,
            ProductRelated::class,
            'recipient_id',
            'id',
            'id',
            'source_id'
        );
    }

    public function scopeWithMainTranslate($query)
    {
        return $query->leftJoin('product_translations as pt', function ($join) {
            $join->on('pt.product_id', 'products.id');
        })->where('pt.locale', Setting::get('site_language'));
    }

    public function getHitAttribute()
    {
        return \Config::get('hit_products_ids')->contains($this->id);
    }

    public function getVariantSpecification()
    {
        if ($this->type === 3) {
            $specifications = [];

            $this->to_variant_attribute_values->each(function ($product_variant_attribute_value) use (&$specifications) {
                $specifications[$product_variant_attribute_value->attribute->translate->name] = $product_variant_attribute_value->attribute_value->translate->value;
            });

            return $specifications;
        } else {
            return [];
        }
    }

    public function thumb_second()
    {
        return $this->images()
            ->where('sort_order', 1);
    }

    public function primary_variant_thumb_second()
    {
        return $this->hasOne(ImageToProduct::class, 'product_id', 'primary_variant_id')
            ->where('sort_order', 1);
    }

    public function setExtra1Attribute($value)
    {
        if (!$value) {
            $this->attributes['extra_1'] = $this->id;
        } else {
            $this->attributes['extra_1'] = $value;
        }
    }

    public function setExtra2Attribute($value)
    {
        if (!$value) {
            if ($this->type === 2) {
                $this->attributes['extra_2'] = $this->id;
            } elseif ($this->type === 3) {
                $this->attributes['extra_2'] = $this->main_id;
            } else {
                $this->attributes['extra_2'] = $value;
            }
        } else {
            $this->attributes['extra_2'] = $value;
        }
    }

    public function getDateAvailableJsAttribute()
    {
        return $this->date_available ? date('c', strtotime($this->date_available)) : null;
    }

    public function getDateAvailableDayAttribute()
    {
        return Carbon::createFromTimeString($this->date_available)->format('d.m.y');
    }

    public function setDateAvailableAttribute($value)
    {
        $this->attributes['date_available'] = $this->convertDate($value);
    }

    private function convertDate($value)
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    public function getSkuAttribute($value)
    {
        if (!app()->runningInConsole() && strpos(request()->route()->getPrefix(), 'admin') === false && !$value) {
            return trans('common.text.empty_model');
        } else {
            return $value;
        }
    }

    public function suppliers()
    {
        return $this->hasMany(SupplierProduct::class, 'product_id', 'id');
    }

    public function getVisualLabelsAttribute()
    {
        $labels = [];

        if(!$this->status){
            $labels[] = 'status';
        }elseif($this->date_available > now()){
            $labels[] = 'date_available';
        }elseif($this->quantity == 0){
            $labels[] = 'quantity';
        }

        if($this->is_zamanyha){
            $labels[] = 'zamanyha';
        }

        return $labels;
    }

    public function export_lists()
    {
        return $this->hasManyThrough(
            ExportProductsList::class,
            ProductToExportProductsList::class,
            'product_id',
            'id',
            'id',
            'products_list_id'
            );
    }

    public function to_export_lists()
    {
        return $this->hasMany(ProductToExportProductsList::class, 'product_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if(!$model->currency_code) $model->currency_code = Setting::get('currency');

            if(!$model->type) $model->type = 1;

        });

        static::saving(function ($model) {

            if ($model->currency_code) $model->currency_code = strtoupper($model->currency_code);

            if ($model->type == 2) {
                if (!$model->variants->count() || !$model->primary_variant_id) {
                    $model->status = 0;
                }
            }

            if (!$model->extra_1) $model->extra_1 = $model->id;

            if(is_null($model->price_source)){
                $model->vendor_price = $model->warehouse_price;
            }else{

                $supplier = $model->suppliers->firstWhere('supplier_code', $model->price_source);

                if($supplier){
                    $model->vendor_price = $supplier->rrc_price;
                }

            }

            self::calculateSummaryQuantity($model);

        });

        static::saved(function ($model) {

            self::setPrices($model);

            if (!$model->extra_1) {
                $model->extra_1 = $model->id;
                $model->update();
            }

        });

        static::created(function ($model) {
            Image::createFolderIfNotExist($model, 'products', $model->id);
        });

        static::deleted(function ($model) {
            Image::deleteFolder('products', $model->id);
        });
    }

    public static function setPrices($product)
    {
        $prices = collect();

        if ($product->type === 1 || $product->type === 3) {

            $specials = $product->specials ?? collect();

            $discounts = $product->discounts ?? collect();

            UserGroup::all()->each(function ($group) use ($product, $prices, $discounts, $specials) {
                $price_info = new ProductPrice();

                $price_info->currency_code = $product->currency_code;
                $price_info->user_group_id = $group->id;

                $price_info->price = $product->vendor_price;

                $discount = $discounts->first(function ($discount) use ($group) {
                    return (
                        $discount->user_group_id == $group->id && $discount->quantity == 1 && ($discount->date_start < now() || $discount->date_start == null) && ($discount->date_end > now() || $discount->date_end == null)
                    );
                });

                $special = $specials->first(function ($special) use ($group) {
                    return (
                        $special->user_group_id == $group->id &&
                        ($special->date_start < now() || $special->date_start == null) &&
                        ($special->date_end > now() || $special->date_end == null)
                    );
                });

                $price_info->price_min = min(
                    $product->vendor_price,
                    $special->price ?? $discount->price ?? $product->vendor_price
                );

                $prices->push($price_info);

            });

        } else {

            if ($product->currency_code !== Setting::get('currency')) {
                $rate = Currency::where('code', $product->currency_code)->value('exchange_rate');
            } else {
                $rate = 1;
            }

            foreach ($product->variants as $variant) {

                $variant_prices = collect();

                $variant_discounts = $variant->discounts ?? collect();

                $variant_specials = $variant->specials ?? collect();

                UserGroup::all()->each(function ($group) use ($rate, $product, $variant, $variant_prices, $variant_discounts, $variant_specials, $prices) {
                    $price_info = new ProductPrice();

                    $price_info->currency_code = $product->currency_code;
                    $price_info->user_group_id = $group->id;

                    $price_info->price = $variant->vendor_price;

                    $discount = $variant_discounts->first(function ($discount) use ($group) {
                        return (
                            $discount->user_group_id == $group->id &&
                            $discount->quantity == 1 &&
                            ($discount->date_start < now() || $discount->date_start == null) &&
                            ($discount->date_end > now() || $discount->date_end == null)
                        );
                    });

                    $special = $variant_specials->first(function ($special) use ($group) {
                        return (
                            $special->user_group_id == $group->id &&
                            ($special->date_start < now() || $special->date_start == null) &&
                            ($special->date_end > now() || $special->date_end == null)
                        );
                    });

                    $price_info->price_min = min(
                        $variant->vendor_price,
                        $special->price ?? $discount->price ?? $variant->vendor_price
                    );

                    $price_info->price_max = max(
                        $variant->vendor_price,
                        $special->price ?? $discount->price ?? $variant->vendor_price
                    );

                    if ($c_price = $prices->firstWhere('user_group_id', $price_info->user_group_id)) {
                        if ($price_info->price_min < $c_price->price_min) {
                            $c_price->price_min = $price_info->price_min * $rate;
                        }
                        if ($price_info->price_max > $c_price->price_max) {
                            $c_price->price_max = $price_info->price_max * $rate;
                        }
                    } else {
                        $new_global_price = new ProductPrice();

                        $new_global_price->currency_code = $product->currency_code;
                        $new_global_price->price = $price_info->price * $rate;
                        $new_global_price->price_min = $price_info->price_min * $rate;
                        $new_global_price->price_max = $price_info->price_max * $rate;
                        $new_global_price->user_group_id = $price_info->user_group_id;

                        $prices->push($new_global_price);
                    }
                });

            }
        }

        $product->prices()->delete();

        try {
            $product->prices()->saveMany($prices);
        } catch (\Exception $exception) {
            info('check product prices');
        }


    }

    public static function calculateSummaryQuantity(&$product)
    {
        $quantity = $product->warehouse_quantity;

        foreach ($product->suppliers as $supplier){
            $quantity += $supplier->quantity;
        }

        $product->quantity = $quantity;
    }

    public function externalSource()
    {
        return $this->hasMany('App\Models\ProductExternalSource', 'product_id', 'id');
    }
}
