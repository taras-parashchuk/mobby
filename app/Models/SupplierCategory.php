<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SupplierCategory extends Model
{
    //

    protected $casts = [
        'category_id' => 'integer',
        'supplier_uuid' => 'string',
        'parent_id' => 'string'
    ];

    protected $fillable = [
        'name',
        'supplier_code',
        'supplier_uuid'
    ];

    private static $buffer_categories;

    public function attributes()
    {
        return $this->hasManyThrough(
            Attribute::class,
            AttributeToSupplierCategory::class,
            'category_id',
            'id',
            'id',
            'attribute_id'
        );
    }

    public function to_attributes()
    {
        return $this->hasMany(AttributeToSupplierCategory::class, 'category_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(SupplierProduct::class, 'category_id', 'supplier_uuid');
    }

    public static function getParentCollection(array $categories, int $category_id)
    {

        $parent_id = self::where('category_id', $category_id)->value('parent_id');

        if($parent_id){

            $categories[] = $parent_id;

            self::getParentCollection($categories, $parent_id);

        }

        return $categories;
    }

    public static function clearBufferCategoriesList()
    {
        self::$buffer_categories = [];
    }
}
