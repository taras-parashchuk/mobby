<?php

namespace App\Models;

use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    //
    protected $appends = ['href'];

    protected $table = 'informations';

    protected $casts = [
        'in_bottom' => 'boolean',
        'in_top' => 'boolean',
        'status' => 'boolean',
    ];

    public $timestamps = false;

    use ModelHelper;

    public function translates()
    {
        return $this->hasMany('App\Models\InformationTranslation')
            ->whereIn('locale', Language::getOnlyActive()->pluck('locale'));
    }

    public function getHrefAttribute()
    {
        return route('information', [
            'id' => $this->id,
            'slug' => $this->slug
        ]);
    }

    public function scopeEnabled($query)
    {
        $query->where('status', 1);
    }
}
