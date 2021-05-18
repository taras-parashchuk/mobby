<?php

namespace App\Models;

use App\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\Model;

class AttributeGroup extends Model
{
    public $timestamps = false;

    use ModelHelper;

    protected $appends = ['translate'];

    //
    public function translates()
    {
        return $this->hasMany('App\Models\AttributeGroupTranslation', 'group_id', 'id')
            ->whereIn('locale', Language::where('status', 1)->pluck('locale'));
    }
}
