@extends('layouts.standart')

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('content')
    <div class="l-flex PageSuccess__content">
        <div class="service__inner">
            <div class="service__icon service__icon--checkout"><i class="icon sb-icon-tick"></i></div>
            <div class="service__title service__title--checkout">{{trans('pages.checkout.success.heading')}}</div>

            @if(Auth::guest())
                <div class="service__text">{!! trans('pages.checkout.success.text.guest') !!}</div>
            @else
                <div class="service__text">{!! trans('pages.checkout.success.text.registered', ['account' => route('account'), 'order-history'=> route('account', ['path' => 'orders'])]) !!}</div>
            @endif

            <a href="{{route('home')}}"
               class="btn btn--primary service__btn">{{trans('common.button.home')}}</a>
        </div>
    </div>
@endsection