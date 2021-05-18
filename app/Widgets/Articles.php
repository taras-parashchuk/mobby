<?php

namespace App\Widgets;

use App\Helpers\Image;
use App\Models\Article;
use App\Models\ArticleRelated;
use App\Models\Setting;
use Arrilot\Widgets\AbstractWidget;

class Articles extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        if (is_string($this->config['article'])) {
            switch ($this->config['article']) {
                case 'related':
                    if (\Route::currentRouteName() == 'article') {

                        $recipient = Article::find(\Request::route()->parameters['id']);

                        $articles = Article::enabled()
                            ->whereIn('id', ArticleRelated::where('recipient_id', $recipient->id )->get('source_id'))
                            ->limit($this->config['limit'] ?: 4)
                            ->get();
                    }
                    break;
            }
        }elseif (is_array($this->config['article']) && count($this->config['article'])) {
            $articles = Article::enabled()->whereIn('id', array_column($this->config['article'], 'id'))
                ->orderByRaw('FIELD(id,' . implode(',', array_column($this->config['article'], 'id')) . ')')->get();
        }else{
            $articles = collect();
        }

        if (is_array($this->config['article']) && Article::count() > count($this->config['article'])) {
            $this->config['has_more_articles'] = true;
        }else{
            $this->config['has_more_articles'] = false;
        }

        if($this->config['title-xs']){
            if(isset($this->config['title-xs']->{\App::getLocale()})){
                $this->config['title-xs'] = $this->config['title-xs']->{\App::getLocale()};
            }elseif(isset($this->config['title-xs']->{Setting::get('site_language')})){
                $this->config['title-xs'] = $this->config['title-xs']->{Setting::get('site_language')};
            }else{
                $this->config['title-xs'] = '';
            }
        }else{
            $this->config['title-xs'] = '';
        }

        if($this->config['title-lg']){
            if(isset($this->config['title-lg']->{\App::getLocale()})){
                $this->config['title-lg'] = $this->config['title-lg']->{\App::getLocale()};
            }elseif(isset($this->config['title-lg']->{Setting::get('site_language')})){
                $this->config['title-lg'] = $this->config['title-lg']->{Setting::get('site_language')};
            }else{
                $this->config['title-lg'] = '';
            }
        }else{
            $this->config['title-lg'] = '';
        }

        $articles->each->append('translate');

        $articles->each(function ($article) {
            $article['image'] = Image::resize("articles/$article->id/$article->image", $this->config['width'], $this->config['height']);
        });

        if($articles->count()){
            return [
                'view' => 'widgets.articles',
                'data' => [
                    'has_container' => true,
                    'config'   => $this->config,
                    'articles' => $articles
                ]
            ];
        }
    }
}
