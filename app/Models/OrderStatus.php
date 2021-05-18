<?php

namespace App\Models;

use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    //

    use ModelHelper;

    public $timestamps = false;

    protected $casts = [
        'status' => 'boolean'
    ];

    protected $appends = [
        'translate'
    ];

    public function translates()
    {
        return $this->hasMany('App\Models\OrderStatusTranslation')
            ->whereIn('locale', Language::where('status', 1)->pluck('locale'));
    }
}
