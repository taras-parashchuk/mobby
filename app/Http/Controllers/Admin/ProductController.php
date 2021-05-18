<?php

namespace App\Http\Controllers\Admin;

use App\Events\NewProductEvent;
use App\Events\UpdateProductEvent;
use App\Helpers\CustomValidation;
use App\Helpers\FileManager;
use App\Helpers\Image;
use App\Models\Attribute;
use App\Models\AttributeToProduct;
use App\Models\AttributeValue;
use App\Models\AttributeValueToProduct;
use App\Models\ImageToProduct;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductPrice;
use App\Models\ProductRelated;
use App\Models\ProductSpecial;
use App\Models\ProductToCategory;
use App\Models\ProductToCompareCategory;
use App\Models\ProductTranslation;
use App\Models\ProductVariantAttributeValue;
use App\Models\Setting;
use App\Models\UserGroup;
use function foo\func;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use UniSharp\LaravelFilemanager\Lfm;
use UniSharp\LaravelFilemanager\LfmItem;

class ProductController extends Controller
{
    private $rules;

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'translates.*.name' => ['required', 'max:200'],
            'translates.*.locale' => ['required', 'exists:languages'],
            'image' => ['nullable', 'string', 'max:200'],
            'warehouse_price' => ['required', 'numeric', function ($input) {
                return $input > 0;
            }],
            'quantity' => ['required', 'numeric', function ($input) {
                return $input >= 0;
            }],
            'sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
            'relateds.*.id' => ['exists:products,id']
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if ($request->has('autocomplete')) {

            $products = Product::enabled()->withTranslate()->where('type', '<>', 3);

            if ($request->has('exclude')) {
                $products = $products->where('id', '<>', $request->input('exclude'));
            }

            if ($request->has('phrase')) {
                $products = $products->where('name', 'like', '%' . $request->input('phrase') . '%')->get();
            } else {
                $products = $products->orderByDesc('products.id')->limit(25)->get();
            }

            return $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'image' => $product->resizeImage(50, 50),
                ];
            });

        } else {

            $products_query = Product::with([
                'export_lists',
                'translates',
                'primary_variant',
                'variants',
                'variants.variant_attribute_values',
                'to_categories',
                'to_attributes.to_values',
                'suppliers' => function($q){
                    $q->select('product_id', 'supplier_code', 'quantity', 'price', 'rrc_price')->orderBy('supplier_code', 'asc');
                }]
            )
                ->withCount('variants')
                ->orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'));

            if ($request->has('old')) {
                $products = $products_query->get();
            } else {

                $filters = json_decode($request->input('columnFilters', ''));

                if ($request->has('name_sku') && mb_strlen($request->input('name_sku'))) {

                    $products_query = $products_query->where(function ($q) use ($request) {
                        $q->where(function ($q) use ($request) {
                            $q->whereHas('translates', function ($q) use ($request) {
                                $q->where('name', 'like', '%' . $request->input('name_sku') . '%');
                            })->orWhereHas('variants.translates', function ($q) use ($request) {
                                $q->where('name', 'like', '%' . $request->input('name_sku') . '%');
                            });
                        })->orWhere(function ($q) use ($request) {
                            $q->where('sku', 'like', $request->input('name_sku') . '%')
                                ->orWhereHas('variants', function ($q) use ($request) {
                                    $q->where('sku', 'like', $request->input('name_sku') . '%');
                                });
                        })->orWhere(function ($q) use ($request) {
                            $q->where('id', $request->input('name_sku') )
                                ->orWhereHas('variants', function ($q) use ($request) {
                                    $q->where('id', $request->input('name_sku') );
                                });
                        });
                    });
                }

                if (isset($filters->status)) {
                    $products_query = $products_query->where('status', $filters->status === 'true' ? true : false);
                }

                if (isset($filters->categories) && count($filters->categories)) {
                    $products_query = $products_query->whereHas('to_categories', function ($q) use ($filters) {
                        $q->whereIn('category_id', $filters->categories);
                    });
                }

                if (isset($filters->price)) {

                    $priceObj = $filters->price;

                    $products_query = $products_query->where('currency_code', $priceObj->currency_code);

                    if ($priceObj->from !== null) {
                        $products_query = $products_query
                            ->where(function ($q) use ($priceObj) {
                                $q->where('vendor_price', '>=', $priceObj->from)->orWhereHas('variants', function ($q) use ($priceObj) {
                                    $q->where('vendor_price', '>=', $priceObj->from);
                                });
                            });

                    }

                    if ($priceObj->to !== null) {
                        $products_query = $products_query
                            ->where(function ($q) use ($priceObj) {
                                $q->where('vendor_price', '<=', $priceObj->to)->orWhereHas('variants', function ($q) use ($priceObj) {
                                    $q->where('vendor_price', '<=', $priceObj->to);
                                });
                            });
                    }
                }

                if (isset($filters->quantity)) {

                    $quantityObj = $filters->quantity;

                    switch ($quantityObj->code) {
                        case '<':
                        case '<=':
                        case '>':
                        case '>=':
                        case '=':
                            if ($quantityObj->from !== null) {
                                $products_query = $products_query
                                    ->where(function ($q) use ($quantityObj) {
                                        $q->where('quantity', $quantityObj->code, $quantityObj->from)->orWhereHas('variants', function ($q) use ($quantityObj) {
                                            $q->where('quantity', $quantityObj->code, $quantityObj->from);
                                        });
                                    });
                            }
                            break;
                        case 'from_to':
                            if ($quantityObj->from !== null && $quantityObj->to !== null) {

                                $products_query = $products_query
                                    ->where(function ($q) use ($quantityObj) {
                                        $q->where(function($q) use ($quantityObj){
                                            $q->where('quantity', '>=', $quantityObj->from);
                                            $q->where('quantity', '<=', $quantityObj->to);
                                        });
                                    });
                            }
                            break;
                    }
                }

                if (isset($filters->attributes)) {

                    foreach ($filters->attributes as $attribute) {

                        $products_query = $products_query->whereHas('to_attributes', function ($q) use ($attribute) {
                            $q->where('attribute_id', $attribute->id)
                                ->whereHas('to_values', function ($q) use ($attribute) {
                                    $q->whereIn('attribute_value_id', $attribute->values);
                                });
                        });
                    }
                }

                if (isset($filters->export_lists) && count($filters->export_lists)) {
                    $products_query = $products_query->whereHas('to_export_lists', function ($q) use ($filters) {
                        $q->whereIn('products_list_id', $filters->export_lists);
                    });
                }

                $products = $products_query->notVariant()->paginate($request->input('perPage'));
            }
        }

        $products->getCollection()->transform(function ($product) {

            if ($product->type === 2) {

                if (!$product->primary_variant) {

                    $product->primary_variant_id = $product->variants->first()->id;

                    $product->save();

                    $product->load('primary_variant');

                }

                $count_variants = $product->variants_count ?? 0;

                $product = $product->primary_variant;

                $product->type = 2;

                try {
                    $product->variants_count = $count_variants;
                } catch (\Exception $e) {

                    throw ValidationException::withMessages([
                        $e->getMessage()
                    ]);

                }

                $product->append('filemanager_thumb', 'href');

            } elseif ($product->type === 3) {


                $variant_attribute_values = $product->variant_attribute_values->map(function ($variant_attribute_value) {
                    return [
                        'id' => $variant_attribute_value->id,
                        'attribute_id' => $variant_attribute_value->attribute_id,
                        'value' => $variant_attribute_value->translate->value
                    ];
                });

                unset($product->variant_attribute_values);

                $product->variant_attribute_values = $variant_attribute_values;

                $product->append('filemanager_thumb', 'href');

            } else {
                $product->append('filemanager_thumb', 'href', 'visual_labels', 'date_available_day');
            }

            return $product;
        });

        return response()->json(compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate($this->rules);

        $product = new Product();

        $product->sku = $request->input('sku');
        $product->image = $request->input('image');
        $product->warehouse_price = $request->input('warehouse_price');
        $product->warehouse_quantity = $request->input('warehouse_quantity');
        $product->sort_order = $request->input('sort_order');
        $product->currency_code = Setting::get('currency');
        $product->price_source = $request->input('price_source');

        $default_trans_index = array_search(app()->getLocale(), array_column($request->input('translates'), 'locale'));

        $product->slug = Str::slug($request->input('translates.' . $default_trans_index . '.name'), '-', app()->getLocale());

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new ProductTranslation(
                [
                    'name' => $translate['name'],
                    'locale' => $translate['locale']
                ]
            );
        }

        $prices = [];

        foreach (UserGroup::all() as $user_group) {

            $price = new ProductPrice();

            $price->currency_code = $product->currency_code;

            $price->price = $product->vendor_price;
            $price->price_min = $product->vendor_price;
            $price->price_max = $product->vendor_price;
            $price->user_group_id = $user_group->id;

            $prices[] = $price;
        }

        try{
            \DB::transaction(function ()use ($product, $translations, $prices){
                $product->save();

                $product->translates()->saveMany($translations);

                $product->prices()->saveMany($prices);
            });

            event(new NewProductEvent($product));

        }catch (\Exception $exception){
            throw ValidationException::withMessages([$exception->getMessage()]);
        }

        return response()->json([
            'id' => $product->id,
            'href' => $product->href,
            'sku' => $product->sku,
            'vendor_price' => $product->vendor_price,
            'quantity' => $product->quantity,
            'text' => trans('form.result.success-created'),
            'visual_labels' => $product->visual_labels
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        //
        $product = Product::with([
            'translates',
            'specials',
            'discounts',
            'images',
            'to_attributes' => function ($q) {
                $q->whereHas('attribute', function ($q) {
                    $q->enabled();
                });
                $q->whereHas('values', function ($q) {
                    $q->enabled();
                });
                $q->with('attribute', 'values');
            },
            'variants.discounts',
            'variants.specials',
            'variants.images',
            'variants.translates',
            'variants.variant_attribute_values',
            'relateds' => function ($q) {
                $q->select('products.id', 'type', 'sku', 'primary_variant_id', 'image');
            },
            'relateds.primary_variant' => function ($q) {
                $q->select('products.id', 'type', 'sku', 'primary_variant_id', 'image');
            },
            'categories' => function ($query) {
                $query->withMainTranslate()->select('ct.name', 'categories.id');
            },
            'compare_categories' => function ($query) {
                $query->withMainTranslate()->select('ct.name', 'categories.id');
            },
            'suppliers' => function($query){
                $query->select('supplier_code', 'product_id');
            }
        ])->without('special', 'discount')->findOrFail($id);

        $to_attributes = $product->to_attributes->map(function ($to_attribute) {

            return [
                'id' => $to_attribute->id,
                'has_variant' => $to_attribute->has_variant,
                'main' => $to_attribute->main,
                'attribute' => [
                    'id' => $to_attribute->attribute->id,
                    'name' => $to_attribute->attribute->translate->name
                ],
                'values' => $to_attribute->values->map(function ($attribute_value) {
                    return [
                        'id' => $attribute_value->id,
                        'attribute_id' => $attribute_value->attribute_id,
                        'value' => $attribute_value->translate->value
                    ];
                })
            ];
        });

        unset($product->to_attributes);

        $product->to_attributes = $to_attributes;

        $this->repairTranslates($product);

        $relateds = $product->relateds->map(function ($related) {
            return [
                'id' => $related->id,
                'name' => $related->translate->name,
                'sku' => $related->sku,
                'image' => $related->resizeImage(50, 50)
            ];
        });

        unset($product->relateds);

        $product->relateds = $relateds;

        $product->translates->each(function ($translate) {
            $translate->description = html_entity_decode($translate->description);
        });

        $product->append('visual_labels', 'date_available_day', 'filemanager_thumb', 'href', 'date_available_js');

        $product->images->map(function ($to_image) use ($product) {

            $to_image->filemanager_thumb = $to_image->getFilemanagerThumb($product, $to_image);

            return $to_image;

        });

        $product->variants->each(function ($variant) use ($product) {

            $variant->append('filemanager_thumb');

            $variant->images->map(function ($to_image) use ($product) {

                $to_image->filemanager_thumb = $to_image->getFilemanagerThumb($product, $to_image);

                return $to_image;

            });

            $variant_attribute_values = $variant->variant_attribute_values->map(function ($variant_attribute_value) {
                return [
                    'id' => $variant_attribute_value->id,
                    'attribute_id' => $variant_attribute_value->attribute_id,
                    'value' => $variant_attribute_value->translate->value
                ];
            });

            unset($variant->variant_attribute_values);

            $variant->variant_attribute_values = $variant_attribute_values;

            $variant->discounts->each->append('date_start_js', 'date_end_js');

            $variant->specials->each->append('date_start_js', 'date_end_js');

        });

        $product->discounts->each->append('date_start_js', 'date_end_js');

        $product->specials->each->append('date_start_js', 'date_end_js');

        return response()->json(compact('product'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        if ($request->input('fast')) {

            $product = Product::with('prices', 'specials', 'discounts')->findOrFail($id);

            $request->validate($this->rules, $this->messages());

            $product->image = $request->input('image', '');
            $product->warehouse_price = $request->input('warehouse_price');
            $product->warehouse_quantity = $request->input('warehouse_quantity');
            $product->sort_order = $request->input('sort_order');
            $product->sku = $request->input('sku');

            try{
                \DB::transaction(function ()use ($product, $request){
                    $product->translates()->delete();

                    foreach ($request->input('translates') as $translate) {
                        $product->translates()->save(new ProductTranslation([
                            'locale' => $translate['locale'],
                            'name' => $translate['name'],
                            'description' => $translate['description'] ?? '',
                            'meta_keywords' => $translate['meta_keywords'] ?? '',
                            'meta_title' => $translate['meta_title'] ?? '',
                            'meta_description' => $translate['meta_description'] ?? '',
                        ]));
                    }

                    $product->save();
                });

                event(new UpdateProductEvent($product));

            }catch (\Exception $exception){
                throw ValidationException::withMessages([$exception->getMessage()]);
            }

        } else {
            $product = Product::with([
                'variants.discounts',
                'variants.specials',
                'variants.images',
                'variants.translates',
                'variants.variant_attribute_values',
            ])->findOrFail($id);

            $this->rules['type'] = ['in:1,2', function ($attribute, $value, $fail) use ($product, $request) {
                if ($value == 2 && $request->input('status') && (!$product->variants->count() && !count($request->input('variants')))) {
                    $fail(trans('validation.custom.product_type'));
                }
            }];
            $this->rules['sku'] = ['nullable', 'string', 'max:100'];
            $this->rules['vendor'] = ['nullable', 'string', 'max:20'];
            $this->rules['stock_status_id'] = ['nullable', 'exists:stock_statuses,id'];
            $this->rules['price_unit_id'] = ['nullable', 'exists:price_units,id'];

            $this->rules['date_available'] = ['nullable', 'date'];
            $this->rules['minimum'] = ['nullable', 'integer', 'digits_between:1,6'];
            $this->rules['slug'] = ['required', 'string', 'max:200'];
            $this->rules['category_id'] = ['nullable', 'exists:categories,id'];
            $this->rules['currency_code'] = ['exists:currencies,code'];
            $this->rules['price_source'] = ['nullable', Rule::in($product->suppliers->map(function($supplier){
                return $supplier->supplier_code;
            }))];

            $this->rules['translates.*.description'] = ['nullable'];
            $this->rules['translates.*.meta_h1'] = ['nullable', 'string', 'max:250'];
            $this->rules['translates.*.meta_title'] = ['nullable', 'string', 'max:250'];
            $this->rules['translates.*.meta_description'] = ['nullable', 'string', 'max:250'];
            $this->rules['translates.*.meta_keywords'] = ['nullable', 'string', 'max:250'];
            $this->rules['translates.*.warranty'] = ['nullable', 'string', 'max:200'];

            $this->rules['images.*.src'] = ['nullable', 'string', 'max:240'];

            $this->rules['categories.*.id'] = ['exists:categories'];

            $this->rules['compare_categories.*.id'] = ['exists:categories'];
            $this->rules['extra_1'] = ['nullable', 'string', 'max:240'];
            $this->rules['extra_2'] = ['nullable', 'string', 'max:240'];

            $this->rules['specials.*.user_group_id'] = ['required', 'exists:user_groups,id'];
            $this->rules['specials.*.price'] = ['required', 'numeric'];
            $this->rules['specials.*.date_start'] = ['nullable', 'date'];
            $this->rules['specials.*.date_end'] = ['nullable', 'date', 'after_or_equal:date_start'];

            $this->rules['discounts.*.user_group_id'] = ['required', 'exists:user_groups,id'];
            $this->rules['discounts.*.quantity'] = ['numeric', function ($input) {
                return $input > 0;
            }];

            $this->rules['discounts.*.user_group_id'] = ['required', 'exists:user_groups,id'];
            $this->rules['discounts.*.price'] = ['numeric'];
            $this->rules['discounts.*.date_start'] = ['nullable', 'date'];
            $this->rules['discounts.*.date_end'] = ['nullable', 'date', 'after_or_equal:date_start'];

            $this->rules['images.*.sort_order'] = ['nullable', 'integer', 'digits_between:1,6'];

            $this->rules['to_attributes.*.attribute_id'] = ['exists:attributes,id'];
            $this->rules['to_attributes.*.values.*.id'] = ['exists:attribute_values'];

            $validator = Validator::make($request->all(), $this->rules, $this->messages(), $this->attributes());

            if ($request->input('status')) {
                $validator->after(function ($validator) use ($request) {

                    $has_attribute_or_value_translation_error = false;

                    foreach ($request->input('to_attributes') as $to_attribute) {

                        if ($to_attribute['has_variant']) {
                            if (!Attribute::whereHas('translates', function ($q) {
                                $q->where('locale', Setting::get('site_language'));
                            })->find($to_attribute['attribute']['id'])) {
                                $has_attribute_or_value_translation_error = true;
                                break;
                            } else {
                                foreach ($to_attribute['values'] as $value) {
                                    if (!AttributeValue::whereHas('translates', function ($q) {
                                        $q->where('locale', Setting::get('site_language'));
                                    })->find($value['id'])) {
                                        $has_attribute_or_value_translation_error = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    if ($has_attribute_or_value_translation_error) {
                        $validator->errors()->add('attribute_or_value_variation_main_translate_not_found', trans('validation.custom.attribute_or_value_variation_main_translate_not_found'));
                    }
                });
            }

            $validator->validate();

            $product->sku = $request->input('sku');
            $product->vendor = $request->input('vendor');
            $product->stock_status_id = $request->input('stock_status_id');
            $product->price_unit_id = $request->input('price_unit_id');
            $product->date_available = $request->input('date_available_js');
            $product->minimum = $request->input('minimum', 1);
            $product->slug = $request->input('slug');
            $product->category_id = $request->input('category_id');
            $product->currency_code = $request->input('currency_code');
            $product->warehouse_price = $request->input('warehouse_price');
            $product->warehouse_quantity = $request->input('warehouse_quantity');
            $product->type = $request->input('type');
            $product->status = $request->input('status');
            $product->sort_order = $request->input('sort_order');
            $product->extra_1 = $request->input('extra_1') ?: null;
            $product->extra_2 = $request->input('extra_2') ?: null;
            $product->price_source = $request->input('price_source', null);
            $product->is_zamanyha = (bool)$request->input('is_zamanyha', false);

            $variants_info = [];

            $translations = [];

            foreach ($request->input('translates') as $translate) {
                $translations[] = new ProductTranslation(
                    [
                        'name' => $translate['name'],
                        'description' => $translate['description'],
                        'meta_h1' => $translate['meta_h1'],
                        'meta_title' => $translate['meta_title'],
                        'meta_description' => $translate['meta_description'],
                        'meta_keywords' => $translate['meta_keywords'],
                        'warranty' => $translate['warranty'],
                        'locale' => $translate['locale']
                    ]
                );
            }

            $specials = collect();

            foreach ($request->input('specials') as $special) {
                $specials->push(new ProductSpecial(
                    [
                        'user_group_id' => $special['user_group_id'],
                        'price' => $special['price'],
                        'date_start' => $special['date_start_js'] ?: null,
                        'date_end' => $special['date_end_js'] ?: null,
                    ]
                ));
            }

            $discounts = collect();

            foreach ($request->input('discounts') as $discount) {

                $discounts->push(new ProductDiscount(
                    [
                        'user_group_id' => $discount['user_group_id'],
                        'quantity' => $discount['quantity'],
                        'price' => $discount['price'],
                        'date_start' => $discount['date_start_js'] ?: null,
                        'date_end' => $discount['date_end_js'] ?: null,
                    ]
                ));

            }

            $images = [];

            $product->image = count($request->input('images')) ? $request->input('images')[0]['src'] : '';

            foreach ($request->input('images') as $sort_order => $image) {

                if ($sort_order != 0) {
                    $images[] = new ImageToProduct(
                        [
                            'src' => $image['src'],
                            'sort_order' => $sort_order
                        ]
                    );
                }
            }

            $product->specials()->delete();
            $product->discounts()->delete();
            $product->images()->delete();

            $product->specials()->saveMany($specials);
            $product->discounts()->saveMany($discounts);
            $product->images()->saveMany($images);

            $categories = [];

            foreach ($request->input('categories') as $category) {
                $categories[] = new ProductToCategory(
                    [
                        'category_id' => $category['id'],
                    ]
                );
            }

            $compare_categories = [];

            foreach ($request->input('compare_categories') as $category) {
                $compare_categories[] = new ProductToCompareCategory(
                    [
                        'category_id' => $category['id'],
                    ]
                );
            }

            $relateds_products = [];

            foreach ($request->input('relateds') as $related_product) {
                $relateds_products[] = new ProductRelated(
                    [
                        'source_id' => $related_product['id'],
                    ]
                );
            }

            $product->to_attributes()->delete();

            foreach ($request->input('to_attributes', []) as $to_attribute) {

                if (count($to_attribute['values'])) {
                    $attribute = new AttributeToProduct();

                    $attribute->attribute_id = $to_attribute['attribute']['id'];
                    $attribute->main = $to_attribute['main'];
                    $attribute->has_variant = $to_attribute['has_variant'];

                    $product->to_attributes()->save($attribute);

                    $values = [];

                    foreach (array_column($to_attribute['values'], 'id') as $attribute_value_id) {
                        $attribute_value_to_product = new AttributeValueToProduct();
                        $attribute_value_to_product->attribute_value_id = $attribute_value_id;

                        $values[] = $attribute_value_to_product;
                    }

                    $attribute->to_values()->saveMany($values);
                }

            }

            try{
                \DB::transaction(function ()use ($product, $translations){
                    $product->save();

                    $product->translates()->delete();

                    $product->translates()->saveMany($translations);
                });

            }catch (\Exception $exception){
                throw ValidationException::withMessages([$exception->getMessage()]);
            }

            $product->to_categories()->delete();

            $product->to_categories()->saveMany($categories);

            $product->to_compare_categories()->delete();

            $product->to_compare_categories()->saveMany($compare_categories);

            $product->to_related()->delete();

            $product->to_related()->saveMany($relateds_products);

            event(new UpdateProductEvent($product));

        }

        return response()->json([
            'text' => trans('form.result.success-updated'),
            'variants_info' => $variants_info ?? [],
            'primary_variant_id' => $product->primary_variant_id,
            'vendor_price' => $product->vendor_price,
            'quantity' => $product->quantity,
            'href' => $product->href,
            'visual_labels' => $product->visual_labels,
            'date_available_day' => $product->date_available_day
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $product = Product::with('main_product.variants')->findOrFail($id);

        if ($product->type === 3 && $product->main_product->variants->count() === 1) {
            $product->main_product()->delete();
        } else {
            $product->delete();
        }

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    public function destroyVariants($id)
    {
        $product = Product::findOrFail($id);

        $product->variants()->delete();
        $product->primary_variant_id = null;
        $product->status = false;
        $product->save();
    }

    public function refreshPrices()
    {
        $user_groups = UserGroup::all();

        foreach (Product::notVariant()->with('variants', 'specials', 'discounts', 'prices')->where('currency_code', '<>', Setting::get('currency'))->get() as $product) {

            $prices = collect();

            if ($product->type === 2) {

                $product->variants->each(function ($variant) use ($product, $user_groups, $prices) {

                    $variant_prices = collect();

                    $new_variant = Product::with('specials', 'discounts', 'prices')->find($variant['id']);

                    $variant_specials = $new_variant->specials;

                    $variant_discounts = $new_variant->discounts;

                    $user_groups->each(function ($group) use ($product, $new_variant, $variant_prices, $variant_discounts, $variant_specials, $prices) {
                        $price_info = new ProductPrice();

                        $price_info->currency_code = $product->currency_code;
                        $price_info->user_group_id = $group->id;

                        $price_info->price = $new_variant->vendor_price;

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
                            $new_variant->vendor_price,
                            $special->price ?? $discount->price ?? $new_variant->vendor_price
                        );

                        $price_info->price_max = max(
                            $new_variant->vendor_price,
                            $special->price ?? $discount->price ?? $new_variant->vendor_price
                        );

                        if ($c_price = $prices->firstWhere('user_group_id', $price_info->user_group_id)) {
                            if ($price_info->price_min < $c_price->price_min) {
                                $c_price->price_min = $price_info->price_min;
                            }
                            if ($price_info->price_max > $c_price->price_max) {
                                $c_price->price_max = $price_info->price_max;
                            }
                        } else {
                            $new_global_price = new ProductPrice();

                            $new_global_price->price = $price_info->price;
                            $new_global_price->price_min = $price_info->price_min;
                            $new_global_price->price_max = $price_info->price_max;
                            $new_global_price->user_group_id = $price_info->user_group_id;

                            $prices->push($new_global_price);
                        }

                        $variant_prices->push($price_info);

                    });
                });

            } else {

                $specials = $product->specials;

                $discounts = $product->discounts;

                $user_groups->each(function ($group) use ($product, $prices, $discounts, $specials) {
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
            }
        };

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function deleteProducts(Request $request)
    {
        if ($request->has('selecteds')) {
            $products = Product::with('main_product')->findOrFail($request->input('selecteds'));


            $products->each(function ($product) {
                if ($product->type === 3) {
                    $product->main_product->delete();
                } else {
                    $product->delete();
                }
            });

            return response()->json([
                'text' => trans('form.result.success-deleted')
            ]);
        }
    }

    public function copyProducts(Request $request)
    {
        if ($request->has('selecteds')) {

            foreach ($request->input('selecteds') as $product_id) {

                $old_product = Product::with('main_product')->find($product_id);

                if ($old_product->type === 3) $old_product = $old_product->main_product;

                $new_product = $old_product->replicate();

                $new_product->status = 0;
                $new_product->extra_1 = null;
                $new_product->primary_variant_id = null;

                $old_product->relations = [];

                $old_product->load(
                    'translates',
                    'specials',
                    'discounts',
                    'images',
                    'to_categories',
                    'to_compare_categories',
                    'to_attributes.to_values',
                    'prices',
                    'variants',
                    'to_variant_attribute_values',
                    'relateds'
                );

                try{

                    \DB::transaction(function () use ($new_product, $old_product){
                        $new_product->save();

                        foreach ($old_product->translates as $item) {
                            $new_product->translates()->create($item->toArray());
                        }
                    });

                }catch (\Exception $exception){
                    throw ValidationException::withMessages([$exception->getMessage()]);
                }

                foreach ($old_product->to_categories as $item) {
                    $new_product->to_categories()->create($item->toArray());
                }

                foreach ($old_product->to_compare_categories as $item) {
                    $new_product->to_compare_categories()->create($item->toArray());
                }

                foreach ($old_product->to_attributes as $i => $item) {

                    unset($item['id']);

                    $new_product->to_attributes()->create($item->toArray());

                    $new_product->refresh();

                    foreach ($item->to_values as $to_value) {

                        unset($to_value['id']);

                        $new_product->to_attributes[$i]->to_values()->create($to_value->toArray());
                    }
                }

                foreach ($old_product->relateds as $item) {
                    $new_product->relateds()->create($item->toArray());
                }

                foreach ($old_product->specials as $item) {
                    $new_product->specials()->create($item->toArray());
                }

                foreach ($old_product->discounts as $item) {
                    $new_product->discounts()->create($item->toArray());
                }

                foreach ($old_product->images as $item) {
                    $new_product->images()->create($item->toArray());
                }

                $source = "/uploads/images/products/$old_product->id/";

                $target = "/uploads/images/products/$new_product->id/";

                foreach (\Storage::disk(config('lfm.disk'))->files($source) as $item) {

                    $file_name = str_replace($source, '', "/$item");

                    \Storage::disk(config('lfm.disk'))->copy($item, $target . $file_name);
                }

                event(new UpdateProductEvent($new_product));
            }

            return response()->json([
                'text' => trans('form.result.success-copied')
            ]);
        }
    }

    public function massEdit(Request $request)
    {
        switch ($request->input('type')) {
            case 'name':
                foreach ($request->input('products') as $product_id) {
                    foreach ($request->input('settings') as $locale => $value) {
                        $translate = ProductTranslation::where([
                            'product_id' => $product_id,
                            'locale' => $locale
                        ])->first();

                        $translate->name = $value;
                        $translate->save();
                    }
                };
                break;
        }
    }

    private function repairTranslates(&$model)
    {
        foreach (config('settings.languages') as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new ProductTranslation([
                        'name' => '',
                        'description' => '',
                        'meta_h1' => '',
                        'meta_title' => '',
                        'meta_description' => '',
                        'meta_keywords' => '',
                        'warranty' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }
}
