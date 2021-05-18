<?php

namespace App\Models;

use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class StockStatus extends Model
{
    //
    public $timestamps = false;

    use ModelHelper;

    protected $appends = ['translate'];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function translates()
    {
        return $this->hasMany('App\Models\StockStatusTranslation')
            ->whereIn('locale', Language::where('status', 1)->pluck('locale'));
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }
}
