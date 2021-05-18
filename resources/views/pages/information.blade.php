@extends('layouts.standart')

@section('appData')
    @parent
@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName(), $information) }}
@endsection

@section('left-column') @endsection

@section('content')
    <div class="article article--single" id="content">
        <h1 class="pageTitle">
            {{$information->translate->name}}
        </h1>
        <div class="article__content html html--default">
            {!! $information->translate->description !!}
        </div>
    </div>
    @section('content-bottom')@endsection
@endsection