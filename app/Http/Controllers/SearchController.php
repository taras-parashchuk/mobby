<?php

namespace App\Http\Controllers;

use App\Helpers\HelperFunction;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductToCategory;
use App\Models\ProductTranslation;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private $singleFilters = [
        'sort',
        'limit',
        'page'
    ];

    //
    public function show(Request $request, string $params = '')
    {
        $search_categories = Category::enabled()
            ->without('translates')
            ->withTranslate()
            ->select('categories.id', 'categories.parent_id', 'categories._rgt', 'categories._lft', 'ct.name')
            ->withDepth()
            ->get()->toFlatTree();

        $search_categories = $search_categories->map(function ($category) {

            $i = 0;

            $prefix = '';

            while ($i < $category->depth) {
                $prefix .= '→ ';

                $i++;
            }

            $category->name = $prefix . $category->name;

            return $category;

        });

        $search_categories->prepend([
            'id' => 0,
            'name' => trans('search.text.show_in_all_categories')
        ]);

        $search_phrase = $request->input('phrase', '');
        $selected_category = $request->input('category_id', 0);
        $include_descriptions = $request->input('with_description', false);;

        $fixParams = $this->toObjectFilterParams($params);

        $link_json_get_search_products = route('search.results');

        $query_params = [];

        foreach ($request->query() as $key => $query_param) {
            $query_params[] = $key . '=' . $query_param;
        }

        if ($query_params) {
            $query = '?' . implode('&', $query_params);
        } else {
            $query = '';
        }

        return view('pages.search', compact('fixParams', 'query', 'link_json_get_search_products', 'include_descriptions', 'search_categories', 'search_phrase', 'selected_category'));
    }

    public function autocomplete(Request $request)
    {
        $phrase = $request->input('phrase', '');

        if (mb_strlen($phrase)) {

            $searchBuilder = Product::enabledAndDelivered()->notVariant()
                ->whereHas('translates', function ($query) use ($phrase) {
                    $query->where('name', 'like', '%' . $phrase . '%');
                })
                ->orWhere('id', $phrase)
                ->with('discount', 'special');

            $products = $searchBuilder->limit(6)->select('type', 'minimum', 'main_id', 'primary_variant_id', 'id', 'slug', 'image', 'quantity', 'multiplicity', 'currency_code')->get();

            $products->each->append('translate', 'priceFormat', 'specialFormat', 'available', 'special_diff', 'stock_title', 'href');

            $products->each(function ($item) {

                $item->thumb = $item->resizeImage(85, 85);

                if ($item->special) {
                    $price = $item->special->price;
                } else {
                    $price = $item->getDiscountOrPriceAttribute();
                }

                $item['price'] = currency($price, $item->currency_code);


            });

            if (count($products) === 6) {
                $hasMore = true;

                $products->pop();

                $hasMoreLink = route('search', ['search_phrase' => $phrase]);

            } else {
                $hasMore = false;
            }

            return response()->json([
                'hasMoreResults' => $hasMore,
                'products' => $products,
                'hasMoreLink' => $hasMoreLink ?? ''
            ]);

        }
    }

    public function results(Request $request)
    {
        $phrase = $request->input('phrase', '');

        list($order_column, $order_type) = explode('.', $request->input('sort'));

        $products_builder = Product::enabledAndDelivered()->notVariant()
            ->whereHas('translates', function ($query) use ($phrase, $request) {
                $query->where('name', 'like', '%' . $phrase . '%');

                if ($request->input('with_description')) {
                    $query->orWhere('description', 'like', '%' . html_entity_decode($phrase) . '%');
                }
            })->orWhere('id', $phrase);

        if ($request->input('category_id')) {
            $products_builder = $products_builder->whereIn('products.id', ProductToCategory::where('category_id', $request->input('category_id'))->select('product_id'));
        }

        $products_builder
            ->without('price_info')
            ->select('products.*')
            ->with('special', 'discount', 'to_attributes_primary.attribute', 'to_attributes_primary.values');

        if ($order_column === 'price' || $request->input('price')) {
            $products_builder->withPriceInfo();
            $products_builder->addSelect('pp.price_min');
        }

        switch ($order_column) {
            case 'price':
                $products_builder->orderBy('pp.price_min', $order_type);
                break;
            default:
                $products_builder->orderBy('products.sort_order', $order_type ?? 'asc');
                break;
        }

        $products = $products_builder->paginate($request->input('limit') ?? 14);


        $products->each(function ($product) {

            $product->thumb = $product->CatalogThumb;

            $product->append('translate', 'priceFormat', 'pricesFormat', 'specialFormat', 'available', 'special_diff', 'stock_title', 'href');

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

            $product->specification = $specifications;
        });

        return [
            'products' => $products,
        ];
    }

    private function toObjectFilterParams(string $params_str)
    {
        $fixParams = [];

        $params = explode(';', $params_str);

        foreach ($params as $param_group) {
            $param = explode('=', $param_group);

            if (isset($param[0], $param[1])) {
                $name = $param[0];

                if (in_array($name, $this->singleFilters)) {

                    $result['multiply'] = false;

                    $result['value'] = $param[1];

                    $result['is_attribute'] = false;

                } else {
                    $result['multiply'] = true;

                    $values = explode(',', $param[1]);

                    $result['values'] = $values;

                    $result['is_attribute'] = false;
                }

                $fixParams[$name] = $result;

                unset($result);
            }
        }

        return $fixParams;
    }
}
