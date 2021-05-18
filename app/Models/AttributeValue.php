<?php

namespace App\Models;

use App\Helpers\Image;
use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    public $timestamps = false;

    protected $casts = [
        'attribute_id' => 'integer',
        'sort_order' => 'integer',
        'status' => 'boolean',
    ];

    use ModelHelper;

    protected $appends = ['translate'];

    protected $with = ['translates'];

    //

    public function translates()
    {
        return $this->hasMany('App\Models\AttributeValueTranslation', 'attribute_value_id')
            ->whereIn('locale', Language::getOnlyActive()->pluck('locale'));
    }

    public function attribute()
    {
        return $this->hasOne('App\Models\Attribute', 'id', 'attribute_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\AttributeToCategory', 'id', 'attribute_value_id');
    }

    public function to_products()
    {
        return $this->hasMany('App\Models\AttributeValueToProduct', 'attribute_value_id', 'id');
    }

    public function scopeEnabled($query)
    {
        $query->where('status', 1);
    }

    public function getFilemanagerThumbAttribute()
    {
        if (isset($this->image) && $this->image) {
            return Image::getFileManagerThumb('attributes', $this->attribute_id, $this->image) ?? null;
        }else{
            return null;
        }
    }
}
