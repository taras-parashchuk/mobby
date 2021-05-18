<?php

namespace App\Http\Controllers;

use App\Helpers\CatalogFilter;
use App\Models\Product;
use App\Models\ProductVariantAttributeValue;
use Illuminate\Http\Request;
use SEO;
use Artesaos\SEOTools\Facades\SEOMeta;
use Session;
use App\Helpers\HelperFunction;

class ProductController extends Controller
{
    //
    public function show($slug, $id)
    {
        $product = Product::enabledAndDelivered()->with([
            'images',
            'special',
            'discount',
            'stock_status',
            'variant_attribute_values'
        ])->without(
            'main_product',
            'main_product.group_price_info',
            'main_product.primary_variant')
            ->where([
                ['id', $id],
                ['slug', $slug]
            ])
            ->firstOrFail();

        if ($product->type === 3) {
            $product->load([
                    'main_product.variants_all_attribute_values',
                    'main_product.to_attributes.attribute',
                    'main_product.to_attributes.attribute.helper' => function ($q) {
                        return $q->enabled();
                    },
                    'main_product.to_attributes.attribute.helper.translates',
                    'main_product.to_attributes.values',
                    'main_product.testimonials' => function ($query) {
                        return $query->where('status', 1);
                    },
                    'main_product.category' => function ($q) {
                        $q->enabled();
                        $q->without('translates');
                        $q->withTranslate();
                        $q->select('categories.id', 'categories.slug', 'ct.name', 'categories._lft', 'categories._rgt');
                    }
                ]
            );

            $to_attributes = $product->main_product->to_attributes;

            $product->testimonials = $product->main_product->testimonials;

            $product->category = $product->main_product->category;

        } else {

            $product->load([
                    'to_attributes.attribute' => function ($query) {
                        return $query->enabled();
                    },
                    'to_attributes.values' => function ($query) {
                        return $query->enabled();
                    },
                    'testimonials' => function ($query) {
                        return $query->where('status', 1);
                    },
                    'category' => function ($q) {
                        $q->enabled();
                        $q->without('translates');
                        $q->withTranslate();
                        $q->select('categories.id', 'categories.slug', 'ct.name', 'categories._lft', 'categories._rgt');
                    },
                ]
            );

            $product->to_attributes = $product->to_attributes->filter(function ($to_attribute) {
                return ($to_attribute->attribute && $to_attribute->values->count());
            });

            $to_attributes = $product->to_attributes;
        }

        $product->append('translate', 'discountOrPrice', 'specialFormat', 'special_diff', 'stock_title', 'available');

        $reviewTotal = $product->testimonials->avg('rating');
        $reviewCount = $product->testimonials->count();

        $product->testimonials->each->append('date_added');

        $short_attributes = $to_attributes->filter(function ($to_attribute) {
            return $to_attribute->main && !$to_attribute->has_variant;
        });

        if ($product->main_product) {
            $all_variants_attribute_values = $product->main_product->variants_all_attribute_values->unique();

            $selected_values = $product->variant_attribute_values;

            $variant_attributes = collect();

            $all_variants_attribute_values->each(function ($attribute_value) use ($selected_values, $to_attributes, $variant_attributes, $all_variants_attribute_values) {
                if (!$variant_attributes->contains(function ($value) use ($attribute_value) {
                    return $value->id === $attribute_value->attribute->id;
                })) {
                    $new_attribute = $attribute_value->attribute;

                    $new_attribute->load('helper.translates');

                    if ($new_attribute->helper && $new_attribute->helper->status) {

                        $helper = $new_attribute->helper;

                        unset($new_attribute->helper);

                        $new_attribute->helper = [
                            'name' => $helper->translate->name,
                            'content' => $helper->translate->description
                        ];
                    } else {
                        unset($new_attribute->helper);
                    }

                    $new_attribute->values = $all_variants_attribute_values->where('attribute_id', $new_attribute->id);

                    $new_attribute->values->each(function ($value) use ($to_attributes, $new_attribute, $selected_values) {
                        if ($selected_values->contains($value)) {
                            $value->selected = true;
                        } else {
                            $value->selected = false;
                        }

                        $value->translate = $to_attributes->first(function ($to_attribute) use ($new_attribute) {
                            return $to_attribute->attribute_id === $new_attribute->id && $to_attribute->has_variant;
                        })->values->firstWhere('id', $value->id)->translate;
                    });

                    $new_attribute->values->map(function ($value) {
                        unset($value->attribute);

                        return $value;
                    });

                    $new_attribute->translate = $to_attributes->firstWhere('attribute_id', $new_attribute->id)->attribute->translate->toArray();

                    $variant_attributes->push($new_attribute);
                }

            });

            $variant_attributes = $variant_attributes->sortBy('sort_order')->values()->all();

        } else {
            $variant_attributes = [];
        }

        $id = $product->main_id ?? $product->id;

        $product->imageInfo = (object)[
            'src' => $product->image,
            'thumb' => $product->getThumb(),
            'popup' => $product->getPopup(),
            'small' => $product->getRelatedThumb(),
        ];

        $product->images->prepend($product->imageInfo);

        $product->images->each(function ($image) use ($product, $id) {
            if (!$image->thumb) $image->thumb = $image->getMainThumb($product);
            if (!$image->popup) $image->popup = $image->getPopup($product);
            if (!$image->small) $image->small = $image->getRelatedThumb($product);
        });

        $meta_extra = [
            'special' => $product->specialFormat,
            'price' => $product->priceFormat,
            'sku' => $product->sku,
            'attributes' => $to_attributes
        ];

        $to_attributes = $to_attributes->filter(function ($to_attribute) {
            return !$to_attribute->has_variant;
        });


        $product->title = $product->translate->meta_h1 ?: HelperFunction::get_seo_template_or_default('product', 'h1', $product->translate->name, $meta_extra);

        SEO::setTitle(HelperFunction::get_seo_template_or_default('product', 'meta_title', $product->translate->meta_title ?: $product->title, $meta_extra));
        SEO::setDescription(HelperFunction::get_seo_template_or_default('product', 'meta_title', $product->translate->meta_description ?: $product->title, $meta_extra));
        SEOMeta::setKeywords($product->translate->meta_keywords);

        return response(view('pages.product', compact('product', 'reviewCount', 'reviewTotal', 'to_attributes', 'short_attributes', 'variant_attributes')))
            ->withCookie(Product::setVisitedProduct($id));

    }

    public function getCategoryProducts(Request $request, CatalogFilter $filter)
    {
        list($order_column, $order_type) = explode('.', $request->input('sort'));

        $request_attributes = $request->input('attributes');

        $main_currency = config('settings.main_currency');

        $user_currency = currency()->getUserCurrency();

        $products = Product::enabledAndDelivered()
            ->notVariant()
            ->select('products.*')
            ->without('translates')
            ->with([
                'prices' => function ($q) {
                    $q->select('user_group_id', 'product_id', 'price_min', 'price_max', 'price');
                },
                'to_attributes_primary.attribute' => function ($q) {
                    return $q->enabled();
                },
                'to_attributes_primary.attribute.translates',
                'to_attributes_primary.values' => function ($q) {
                    return $q->enabled();
                },
                'primary_variant' => function ($q) {
                    $q->without('translates');
                    $q->select('id', 'image', 'type', 'quantity', 'sku', 'slug', 'products.main_id');
                },
                'price_unit' => function ($q) {
                    $q->select('id');
                    $q->where('status', true)->where('display', true);
                },
                'stock_status' => function ($q) {
                    $q->enabled();
                    $q->select('id');
                },
            ]);

        if ($order_column === 'price' || $request->input('price')) {
            $products->withPriceInfo();
            $products->addSelect('pp.price_min');
        }

        $products->filter($filter);

        switch ($order_column) {
            case 'price':
                $products->orderBy('pp.price_min', $order_type);
                break;
            default:
                $products->orderBy('stock_status_id', $order_type ?? 'asc');
                break;
        }

        $products = $products->orderBy('products.quantity', 'DESC')
            ->select(
                'products.id', 'image', 'type', 'price_unit_id', 'stock_status_id',
                'quantity', 'multiplicity', 'minimum', 'sku', 'slug',
                'products.currency_code',
                'products.main_id', 'products.primary_variant_id'
            )->paginate($request->input('limit') ?? 14);

        if ($request_attributes) {
            $products->load([
                'variants' => function ($q) {
                    $q->without('translates');
                    $q->select('products.id', 'type', 'image', 'quantity', 'multiplicity', 'sku', 'slug', 'main_id');
                },
                'variants.to_variant_attribute_values',
                'variants.to_variant_attribute_values.attribute_value',
            ]);
        }

        foreach ($products as $k => $product) {

            $products[$k]->to_attributes_primary = $products[$k]->to_attributes_primary->filter(function ($to_attribute_primary) {
                return ($to_attribute_primary->attribute && $to_attribute_primary->values->count());
            });

            $products[$k]->to_attributes_primary->each(function ($to_attribute_primary) {
                $to_attribute_primary->attribute->append('translate');
                $to_attribute_primary->values->each(function ($value) {
                    $value->append('translate');
                });
            });

            $specifications = [];

            foreach ($product->to_attributes_primary as $to_attribute_primary) {

                if ($to_attribute_primary->attribute->translate) {

                    $specification = [
                        'name' => $to_attribute_primary->attribute->translate->name,
                        'values' => []
                    ];

                    foreach ($to_attribute_primary->values as $value) {
                        $specification['values'][] = $value->translate->value;
                    }

                    $specifications[] = $specification;
                }
            }

            if ( $request_attributes && $product->type == 2 ) {

                $attributes = $filter->getGeneratedAttributes();

                $old_id = $product->id;

                $replacedVariant = $product->variants->first(function ($variant) use ($request_attributes, $attributes) {

                    $result = null;

                    foreach ($request_attributes as $attribute_slug => $param) {

                        if ((is_null($result) || $result !== false) && isset($attributes[$attribute_slug])) {

                            $attribute_value_slug = \Arr::first($param['values']);

                            if ($variant->to_variant_attribute_values->unique('attribute_value_id')->contains(function ($to_variant_attribute_value) use ($attribute_value_slug) {
                                return $to_variant_attribute_value->attribute_value->slug == $attribute_value_slug;
                            })) {
                                $result = true;
                                break;
                            } else {
                                $result = false;
                            };
                        } else {
                            $result = false;
                        }
                    }

                    return (bool)$result;
                });

                if ($replacedVariant) {

                    $oldProduct = $products[$k];

                    $products[$k] = $replacedVariant;

                    unset($oldProduct['variants'], $oldProduct['primary_variant']);

                    $products[$k]->main_product = $oldProduct;

                    $products[$k]->replaced_id = $old_id;

                    $products[$k]->prices = $oldProduct->prices;

                }
            } elseif ($products[$k]->type === 2) {

                $oldProduct = $products[$k];

                $products[$k] = $oldProduct->primary_variant;

                unset($oldProduct->primary_variant);

                $products[$k]->main_product = $oldProduct;

                $products[$k]->replaced_id = $oldProduct->id;

                $products[$k]->prices = $oldProduct->prices;


            }

            $products[$k]->specification = $specifications;

        }

        $products->load([
            'secondImage',
            'translates' => function ($q) {
                $q->select('product_id', 'locale', 'name');
            },
            'special' => function ($q) {
                $q->select('product_id', 'price');
            },
            'discount' => function ($q) {
                $q->select('product_id', 'price');
            },
        ]);

        foreach ($products as $k => $product) {
            $products[$k]->thumb = $products[$k]->CatalogThumb;

            $products[$k]->thumb_second = $products[$k]->secondCatalogThumb;
        }

        $products->getCollection()->transform(function ($product) {

            $product->append('href', 'hit', 'translate', 'priceFormat', 'pricesFormat',
                'specialFormat', 'available', 'special_diff', 'stock_title');

            return $product;
        });

        $request->input('price', []);

        $filteredProductsBuilder = Product::enabledAndDelivered()->notVariant()
            ->with('prices')->without('translates');

        if ($request_attributes) {
            $filteredProductsBuilder = $filteredProductsBuilder->with([
                'variants' => function ($q) {
                    $q->without('translates');
                },
                'variants.to_variant_attribute_values.attribute_value'
            ]);

            $filteredProductsBuilder = $filteredProductsBuilder->without('primary_variant');
        }

        $filteredProductsBuilder = $filteredProductsBuilder->filter($filter->setExcludeParams(['price']))->select('type', 'id')->get();

        $filteredProductsBuilder->each->appends = [];

        if( $request_attributes ){

            $filteredProductsBuilder = $filteredProductsBuilder->map(function ($filteredProductBuilder) use ($products, $main_currency, $user_currency) {

                if ($filteredProductBuilder->type == 2 ) {
                    $newVariantOffilteredProductBuilder = $products->firstWhere('replaced_id', $filteredProductBuilder->id);
                } else {
                    $newVariantOffilteredProductBuilder = false;
                }

                if ($newVariantOffilteredProductBuilder) $filteredProductBuilder = $newVariantOffilteredProductBuilder;

                if (!$filteredProductBuilder->price_info) return;

                $filteredProductBuilder->price = currency($filteredProductBuilder->price_info->price_min, $main_currency, $user_currency, false);

                return $filteredProductBuilder;
            });

        }else{
            $filteredProductsBuilder = $filteredProductsBuilder->map(function ($filteredProductBuilder) use ($main_currency, $user_currency) {

                $filteredProductBuilder->price = currency($filteredProductBuilder->price_info->price_min, $main_currency, $user_currency, false);

                return $filteredProductBuilder;
            });
        }

        $minPrice = floor($filteredProductsBuilder->min('price'));
        $maxPrice = ceil($filteredProductsBuilder->max('price'));

        return [
            'products' => $products,
            'price_slider' => [
                'from' => $minPrice ?? $request->input('price')[0] ?? null,
                'to' => $maxPrice ?? $request->input('price')[1] ?? null
            ]
        ];
    }

    public function getVariantParams($main_id, $attribute_id, Request $request)
    {

        $attribute_values_groups = ProductVariantAttributeValue::where('main_id', $main_id)->get()->groupBy('product_id');

        foreach ($request->input() as $attribute_value_id) {
            $attribute_values_groups = $attribute_values_groups->filter(function ($attribute_values_group) use ($attribute_value_id) {
                return $attribute_values_group->where('attribute_value_id', $attribute_value_id)->count();
            });
        }

        $filtered_attribute_value_ids = [];

        $attribute_values_groups->each(function ($attribute_values_group) use ($attribute_id, &$filtered_attribute_value_ids) {

            if ($filtered_attribute_value = $attribute_values_group->first(function ($attribute_value) use ($attribute_id) {
                return $attribute_value->attribute_id == $attribute_id;
            })) {
                $filtered_attribute_value_ids[] = $filtered_attribute_value->attribute_value_id;
            }
        });
        return array_unique($filtered_attribute_value_ids);
    }

    public function getVariantInfo($main_id, Request $request)
    {
        $attribute_values_groups = ProductVariantAttributeValue::where('main_id', $main_id)->get()->groupBy('product_id');

        foreach ($request->input() as $attribute_value_id) {
            $attribute_values_groups = $attribute_values_groups->filter(function ($attribute_values_group) use ($attribute_value_id) {
                return $attribute_values_group->where('attribute_value_id', $attribute_value_id)->count();
            });
        }

        if (count($attribute_values_groups)) {
            $product_variant_id = $attribute_values_groups->first()->first()->product_id;

            return Product::select('id', 'slug')->find($product_variant_id)->append('href');
        }
    }

    public function changeCurrency()
    {
        return back();
    }

    public function changeViewType($type)
    {
        if ($type) {
            Session::put('view.type', $type);
        }
    }
}
