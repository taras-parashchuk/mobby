<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ArticleRelated extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['source_id'];

    protected $table = 'article_relateds';



    public function source()
    {
        return $this->belongsTo(Article::class, 'source_id', 'id');
    }
}
