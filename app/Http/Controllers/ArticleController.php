<?php

namespace App\Http\Controllers;

use App\Helpers\HelperFunction;
use App\Helpers\Image;
use App\Models\Article;
use SEO;
use SEOMeta;

class ArticleController extends Controller
{
    //
    public function index()
    {
        $articles = Article::where('status',1)->get();

        $articles->each(function ($article) {
            $article['image'] = $article->resizeImage(370, 288);
        });

        return view('pages.articles', compact('articles'));
    }

    public function show($slug, $id)
    {
        $article = Article::enabled()->where('slug', $slug)->findOrFail($id);

        SEO::setTitle($article->translate->meta_title ?: $article->translate->name);
        SEO::setDescription($article->translate->meta_description ?: $article->translate->name);
        SEOMeta::setKeywords($article->translate->meta_keywords);

        return view('pages.article', compact('article'));
    }
}
