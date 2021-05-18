<?php

namespace App\Models;

use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class PriceUnit extends Model
{
    //
    use ModelHelper;

    public $timestamps = false;

    protected $casts = [
        'status' => 'boolean',
        'display' => 'boolean'
    ];

    public function translates()
    {
        return $this->hasMany('App\Models\PriceUnitTranslation')
            ->whereIn('locale', Language::getOnlyActive()->pluck('locale'));
    }
}
