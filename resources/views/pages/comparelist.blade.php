@extends('layouts.standart')

@section('appData')

    @parent

    <script>
        window.dataPage = {};
    </script>

@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('left-column') @endsection

@section('content')

    <div class="compare">
        <h1 class="pageTitle">{{trans('pages.comparelist.heading')}}</h1>
        <div id="content" class="compareInfo">
            @if ($compare_list_categories->count())
                <ul>
                    @foreach ($compare_list_categories as $category)
                        <li class="compare__category">
                            <a href="{{$category['href']}}" class="text-link">
                                {{$category['name']}} ({{$category['count']}})
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty">
                    <div class="empty__text">
                        {{trans('common.text.empty')}}
                    </div>
                    <div class="empty__btns">
                        <a href="{{route('home')}}"
                           class="btn btn--primary empty__btn">
                            {{trans('common.button.home')}}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection