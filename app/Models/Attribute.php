<?php

namespace App\Models;

use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    //
    public $timestamps = false;

    protected $casts = [
        'group_id' => 'integer',
        'sort_order' => 'integer',
        'status' => 'boolean',
        'main_info_id' => 'integer'
    ];

    use ModelHelper;

    protected $appends = ['translate'];

    public function translates()
    {
        return $this->hasMany('App\Models\AttributeTranslation')
            ->whereIn('locale', Language::getOnlyActive()->pluck('locale'));
    }

    public function values()
    {
        return $this->hasMany('App\Models\AttributeValue', 'attribute_id');
    }

    public function categories()
    {
        return $this->hasManyThrough(
            'App\Models\Category',
            'App\Models\AttributeToCategory',
            'attribute_id',
            'category_id'
        );
    }

    public function to_categories()
    {
        return $this->hasMany('App\Models\AttributeToCategory', 'attribute_id', 'id');
    }

    public function filtered_in_categories()
    {
        return $this->hasMany('App\Models\CategoryFilterAttribute', 'attribute_id', 'id');
    }

    public function filtered_categories()
    {
        return $this->hasManyThrough(
            'App\Models\Category',
            'App\Models\CategoryFilterAttribute',
            'attribute_id',
            'id',
            'id',
            'category_id');
    }

    public function products()
    {
        return $this->hasManyThrough(
            'App\Models\Product',
            'App\Models\AttributeToProduct',
            'attribute_id',
            'id',
            'id',
            'product_id'
        );
    }

    public function scopeEnabled($query)
    {
        $query->where('status', 1);
    }

    public function helper()
    {
        return $this->belongsTo(Information::class, 'main_info_id');
    }
}
