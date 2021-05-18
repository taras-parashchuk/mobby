<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeGroupTranslation extends Model
{
    //
    protected $fillable = ['name', 'locale'];
    public $timestamps = false;

    public function attribute_group()
    {
        return $this->belongsTo('App\Models\AttributeGroup', 'id', 'group_id');
    }
}
