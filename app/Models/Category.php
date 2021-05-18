<?php

namespace App\Models;

use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Helpers\Image;

class Category extends Model
{
    use NodeTrait, ModelHelper;

    protected $casts = [
        'parent_id' => 'integer',
        'status' => 'boolean'
    ];

    protected $with = ['translates'];

    protected $appends = ['href', 'translate'];

    public function scopeWithTranslate($query, $locale = null)
    {
        $query->leftJoin('category_translations as ct', 'ct.category_id', 'categories.id')
            ->where('ct.locale', $locale ?? app()->getLocale())
            ->orWhere('ct.locale', Setting::get('site_language'));
    }

    public function scopeWithMainTranslate($query)
    {
        return $query->leftJoin('category_translations as ct', 'ct.category_id', 'categories.id')
            ->where('ct.locale', Setting::get('site_language'));
    }

    public function translates()
    {
        return $this->hasMany('App\Models\CategoryTranslation')
            ->whereIn('locale', Language::getOnlyActive()->pluck('locale'));
    }

    public function attributes()
    {
        return $this->hasManyThrough(
            'App\Models\Attribute',
            'App\Models\AttributeToCategory',
            'category_id',
            'id',
            'id',
            'attribute_id'
        );
    }

    public function resizeImage($width, $height)
    {
        return Image::resize("categories/" . $this->id . "/$this->image", $width, $height);
    }

    public function scopeEnabled($query)
    {
        $query->where('status', 1);
    }

    public function getHrefAttribute()
    {
        return route('category', [
            'id' => $this->id,
            'slug' => $this->slug
        ]);
    }

    public function to_compare_category()
    {
        return $this->hasMany(ProductToCompareCategory::class);
    }

    public function category_parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function filtered_attributes()
    {
        return $this->hasMany(CategoryFilterAttribute::class, 'category_id', 'id');
    }

    public function externalSource()
    {
        return $this->hasMany('App\Models\CategoryExternalSource', 'category_id', 'id');
    }

    public static function getGroupCategories(?int $category_id): array
    {
        $ancestors = [];

        if ($category_id) {

            $ancestors = Category::ancestorsOf($category_id)->map(function ($ancestor) {
                return $ancestor->id;
            })->toArray();

            $ancestors[] = $category_id;
        }

        return $ancestors;
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function () {
            self::cacheCategoriesByDepth();
        });

        static::saving(function ($model) {

            if (!$model->extra_1) $model->extra_1 = $model->id;

            self::cacheCategoriesByDepth();
        });

        static::saved(function ($model) {

            if (!$model->extra_1) {
                $model->extra_1 = $model->id;
                $model->update();
            }

        });

    }

    private static function cacheCategoriesByDepth()
    {
        Language::getOnlyActive()->each(function ($language) {

            \Cache::forget("depth.categories-" . $language->locale);

            $categories = Category::enabled()
                ->withCount(['descendants' => function ($q) {
                    $q->enabled();
                }])
                ->with(['translates' => function ($q) {
                    $q->select('category_id', 'locale', 'name');
                }])
                //    ->select('depth','descendants_count', 'id', 'slug', '_lft', '_rgt', 'parent_id')
                ->withDepth()
                ->orderBy('sort_order', 'ASC')
                ->get();

            $categories->each->append('translate');

            \Cache::set("depth.categories-" . $language->locale, $categories);
        });

    }
}