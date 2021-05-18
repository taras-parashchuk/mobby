@extends('layouts.standart')

@section('appData')
    @parent
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName(), $article) }}
@endsection

@section('left-column') @endsection

@section('content')

    <div class="article article--single" id="content">
        <h1 class="pageTitle">
            {{$article->translate->name}}
        </h1>
        <div class="article__date">
            <span>
                {{$article->date_added}}
            </span>
        </div>
        <div class="article__content html html--default">
            {!! $article->translate->description !!}
        </div>
        <div class="share share--inArticle">
            <span class="share__text share__text--top share__text--inArticle">
                {{trans('common.text.share')}}
            </span>
            <a href="javascript:void(0)" class="icon sb-icon-facebook-round share__link share__link--medium"
               onclick="this.href='http://www.facebook.com/share.php?u='+encodeURIComponent(window.location.href)"
               target="_blank">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                        class="path4"></span>
            </a>
            <a href="javascript:void(0)" class="icon sb-icon-twitter share__link share__link--medium"
               onclick="this.href='http://twitter.com/share?text='+document.title+'&url='+encodeURIComponent(window.location.href)"
               target="_blank">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                        class="path4"></span>
            </a>
        </div>
    </div>

@endsection