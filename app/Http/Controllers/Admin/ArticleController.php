<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CustomValidation;
use App\Models\Article;
use App\Models\ArticleRelated;
use App\Models\ArticleTranslation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    private $rules = [];

    private $request;

    use CustomValidation;

    public function __construct()
    {
        $this->rules = [
            'translates.*.name' => ['required', 'max:200'],
            'translates.*.description' => ['nullable'],
            'translates.*.meta_title' => ['nullable', 'max:200'],
            'translates.*.meta_description' => ['nullable', 'max:200'],
            'translates.*.meta_keywords' => ['nullable', 'max:200'],
            'translates.*.locale' => ['required', 'exists:languages'],
            'image' => ['nullable','string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'digits_between:1,6'],
            'status' => ['boolean'],
        ];
    }


    //
    public function index(Request $request)
    {
        if ($request->input('autocomplete')) {

            if ($request->has('exclude')) {
                $articles = Article::where('id', '<>', $request->input('exclude'))->orderByDesc('articles.id');
            }else{
                $articles = Article::orderByDesc('articles.id');
            }

            $articles = $articles->get()->map(function($article){
               return [
                   'id' => $article->id,
                   'name' => $article->translate->name
               ];
            });

        } else {
            $articles = Article::with('translates')
                ->orderBy($request->input('sort_column', 'id'), $request->input('sort_direction', 'desc'))
                ->paginate($request->input('perPage'));

            $articles->each(function ($article) {
                $this->repairTranslates($article);
            });

            $articles->each(function($article){
                $article->append('filemanager_thumb');
            });
        }

        return response()->json(
            compact('articles')
        );
    }

    private function repairTranslates(&$model)
    {
        foreach (config('settings.languages') as $language) {

            if (!$model->translates->firstWhere('locale', 'like', $language['locale'])) {
                $model->translates->push(
                    new ArticleTranslation([
                        'name' => '',
                        'description' => '',
                        'meta_title' => '',
                        'meta_description' => '',
                        'meta_keywords' => '',
                        'locale' => $language['locale']
                    ])
                );
            }
        }
    }

    public function store(Request $request)
    {
        $this->rules['slug'] = ['nullable'];

        $this->request = $request;

        $request->validate($this->rules, $this->messages());

        $information = new Article();

        $information->status = $request->input('status');
        $information->image = $request->input('image');
        $information->sort_order = $request->input('sort_order');

        $default_trans_index = array_search(app()->getLocale(), array_column($request->input('translates'), 'locale'));

        $information->slug = Str::slug($request->input('translates.' . $default_trans_index . '.name'), '-', app()->getLocale());

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new ArticleTranslation(
                [
                    'name' => $translate['name'],
                    'locale' => $translate['locale']
                ]
            );
        }

        $information->save();

        $information->translates()->saveMany($translations);

        return response()->json([
            'id' => $information->id,
            'text' => trans('form.result.success-created')
        ]);
    }

    public function destroy($id)
    {
        //
        Article::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    public function update(Request $request, $id)
    {
        //
        $this->rules['slug'] = ['required', 'string', 'between:1,30'];

        $this->request = $request;

        $request->validate($this->rules, $this->messages());

        $article = Article::findOrFail($id);

        $article->image = $request->input('image');
        $article->status = $request->input('status');
        $article->slug = $request->input('slug');
        $article->sort_order = $request->input('sort_order');

        $translations = [];

        foreach ($request->input('translates') as $translate) {
            $translations[] = new ArticleTranslation(
                [
                    'name' => $translate['name'],
                    'description' => $translate['description'],
                    'meta_title' => $translate['meta_title'],
                    'meta_description' => $translate['meta_description'],
                    'meta_keywords' => $translate['meta_keywords'],
                    'locale' => $translate['locale']
                ]
            );
        }

        $article->save();

        $article->translates()->delete();

        $article->translates()->saveMany($translations);

        $article->relateds()->delete();

        $relateds_articles = [];

        foreach ($request->input('relateds', []) as $related_article) {
            $relateds_articles[] = new ArticleRelated(
                [
                    'source_id' => $related_article['id'],
                ]
            );
        }

        $article->relateds()->saveMany($relateds_articles);

        return response()->json([
            'id' => $article->id,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function edit($id)
    {
        $article = Article::with('translates', 'relateds')->findOrFail($id);

        $article->append('filemanager_thumb');

        $relateds = [];

        foreach ($article->relateds as $related){
            $relateds[] = [
                'id' => $related->source->id,
                'name' => $related->source->translate->name
            ];
        }

        unset($article->relateds);

        $article->relateds = $relateds;

        $this->repairTranslates($article);

        return $article;
    }
}
