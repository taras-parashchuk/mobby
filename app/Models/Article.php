<?php

namespace App\Models;

use App\Helpers\Image;
use App\Helpers\ModelHelper;
use App\Scopes\DemoAccessScope;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
    protected $appends = ['href', 'date_added'];

    protected $with = ['translates'];

    use ModelHelper;

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function translates()
    {
        return $this->hasMany(ArticleTranslation::class, 'article_id', 'id')
            ->whereIn('locale', Language::getOnlyActive()->pluck('locale'));
    }

    public function getHrefAttribute()
    {
        return route('article', [
            'id' => $this->id,
            'slug' => $this->slug
        ]);
    }

    public function getDateAddedAttribute()
    {
        if($this->created_at) return $this->created_at->format('d.m.Y');
    }

    public function scopeEnabled($query)
    {
        $query->where('status', 1);
    }

    public function relateds()
    {
        return $this->hasMany(ArticleRelated::class, 'recipient_id', 'id');
    }

    public function getFilemanagerThumbAttribute()
    {
        if ($this->image) {

            $id = $this->id;

            if ($thumb = \App\Helpers\Image::getFileManagerThumb('articles', $id, $this->image)) {
                return $thumb;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {

            Image::createFolderIfNotExist($model, 'articles', $model->id);

            $model->save();
        });

        static::deleted(function ($model) {
            Image::deleteFolder('articles', $model->id);
        });
    }
}
