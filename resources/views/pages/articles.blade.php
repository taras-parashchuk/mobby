@extends('layouts.standart')

@section('appData')
    @parent
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('left-column') @endsection

@section('content')
    <div class="blog" id="content">
        <h1 class="pageTitle">
            {{trans('pages.blog.heading')}}
        </h1>
        @if ($articles)
            <div class="blog__info">
                @foreach ($articles as $article)
                    <div class="blog__article article">
                        <a href="{{$article->href}}" class="article__img">
                            <img src="{{$article->image}}" alt="{{$article->translate->name}}"
                                 class="img-responsive"/>
                        </a>
                        <div class="article__date">
                            <span>
                                {{$article->date_added}}
                            </span>
                        </div>
                        <a href="{{$article->href}}" class="article__name">
                            {{$article->translate->name}}
                        </a>
                    </div>
                @endforeach
            </div>
            <?php //echo $pagination; ?>
        @else
            <p>{{trans('common.text.empty')}}</p>
        @endif
        @section('content-bottom') @endsection
    </div>
@endsection