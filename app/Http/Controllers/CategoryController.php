<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeToCategory;
use Illuminate\Http\Request;
use App\Models\Category;
use Gregwar\Image\Image;
use Illuminate\Support\Collection;
use SEO;
use SEOMeta;
use Cache;
use App\Helpers\HelperFunction;

class CategoryController extends Controller
{
    private $singleFilters = [
        'sort',
        'limit',
        'page'
    ];

    //
    public function index(Request $request, $slug, $id, string $params = '')
    {

        $category = Category::where('slug', $slug)->enabled()
            ->without('translates')->withTranslate()
            ->select('categories.id', 'categories.slug', 'categories._lft', 'categories._rgt', 'ct.name', 'ct.meta_description', 'ct.meta_title', 'ct.description', 'ct.meta_keywords')
            ->findOrFail($id);

        $category->description = html_entity_decode($category->description);

        $childCategories = $category->ancestors()->without('translates')->withTranslate()
            ->select('categories.id', 'categories.slug', 'ct.name')
            ->get();

        $api_link = route('api.products');

        $catalog_link = route('category', ['id' => $id, 'slug' => $slug]);

        $attributes = Attribute::with(['filtered_in_categories' => function ($query) use ($category) {
            $query->select('category_id');
            $query->where('category_id', $category->id);
        }])->select('slug', 'id')->get();

        $params = $this->toObjectFilterParams($params, $attributes);

        $fixParams = $params['fixParams'];

        $dynamicParams = $params['dynamicParams'];


        if($category->parent_id){
            if($dynamicParams){
                if(count($dynamicParams) === 1 && count(reset($dynamicParams)['values']) === 1){
                    $meta_type = 'filter_in_subcategory';
                }else{
                    $meta_type = 'mix_filter_in_subcategory';
                }
            }else{
                $meta_type = 'subcategory';
            }
        }else{
            if($dynamicParams){
                if(count($dynamicParams) === 1 && count(reset($dynamicParams)['values']) === 1){
                    $meta_type = 'filter_in_category';
                }else{
                    $meta_type = 'mix_filter_in_category';
                }
            }else{
                $meta_type = 'category';
            }
        }

        SEO::setTitle(HelperFunction::get_seo_template_or_default($meta_type, 'meta_title', $category->meta_title ?: $category->name));
        SEO::setDescription(HelperFunction::get_seo_template_or_default($meta_type, 'meta_description', $category->meta_description ?: $category->name));
        SEOMeta::setKeywords($category->meta_keywords);

        $query_params = [];

        foreach ($request->query() as $key => $query_param) {
            $query_params[] = $key . '=' . $query_param;
        }

        if ($query_params) {
            $query = '?' . implode('&', $query_params);
        } else {
            $query = '';
        }

        return view('pages.category', compact('query', 'category', 'childCategories', 'catalog_link', 'api_link', 'fixParams', 'dynamicParams'));
    }

    private function toObjectFilterParams(string $params_str, Collection $attributes)
    {
        $fixParams = [];

        $dynamicParams = [];

        $params = explode(';', $params_str);

        $attr_aliases = $attributes->pluck('slug');

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

                    if ($attr_aliases->contains($name)) {
                        $result['is_attribute'] = true;
                    } else {
                        $result['is_attribute'] = false;
                    }
                }

                if ($result['is_attribute']) {
                    $dynamicParams[$name] = $result;
                } else {
                    $fixParams[$name] = $result;
                }

                unset($result);
            }
        }

        return ['fixParams' => $fixParams, 'dynamicParams' => $dynamicParams];
    }

    public function getAttributes(Request $request)
    {
        $category_id = $request->input('category_id') ?? 0;

        $attributes = Attribute::enabled()->with(['translates' => function($q){
            $q->select('attribute_id', 'locale', 'name');
        },'values' => function ($query) use ($category_id) {
            $query->whereHas('category', function ($query) use ($category_id) {
                $query->select('id');
                $query->where('category_id', $category_id);
            })->enabled();
        }])->whereHas('filtered_in_categories', function ($query) use ($category_id) {
            $query->select('category_id');
            $query->where('category_id', $category_id);
        })->orderBy('attributes.sort_order')->get();

        $attributes->each->append('translate');

        $attributes->each(function ($attribute) {
            $attribute->values->each->append('translate');
        });

        return $attributes->toArray();
    }
}
