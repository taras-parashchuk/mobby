@extends('layouts.standart')

@section('appData')

    @parent

    <script>
        window.dataPage = {};

        window.trans['pages.comparelist.button.all_attr'] = "{!! trans('pages.comparelist.button.all_attr') !!}";
        window.trans['pages.comparelist.button.different_attr'] = "{!! trans('pages.comparelist.button.different_attr') !!}";
        window.trans['pages.comparelist.button.same_attr'] = "{!! trans('pages.comparelist.button.same_attr') !!}";
        window.trans['catalog.text.model'] = "{!! trans('catalog.text.model') !!}";

    </script>

@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName(), $category) }}
@endsection

@section('left-column') @endsection

@section('content')

    <div class="compare">
        <h1 class="pageTitle">{{trans('pages.comparelist.heading')}}</h1>
        <compare-table category_id="{{$category->id}}"></compare-table>
    </div>

@endsection