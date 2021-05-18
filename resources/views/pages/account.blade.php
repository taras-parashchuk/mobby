@extends('layouts.standart')

@section('appData')

    @parent

    <script>
        window.dataPage = {};


        window.trans['account.entry.firstname'] = "{!! trans('account.entry.firstname') !!}";
        window.trans['account.entry.lastname'] = "{{trans('account.entry.lastname')}}";
        window.trans['account.entry.telephone'] = "{{trans('account.entry.telephone')}}";
        window.trans['account.entry.email'] = "{{trans('account.entry.email')}}";
        window.trans['account.entry.password'] = "{{trans('account.entry.password')}}";
        window.trans['account.entry.confirm'] = "{{trans('account.entry.confirm')}}";
        window.trans['account.entry.newsletter.text'] = "{{trans('account.entry.newsletter.text')}}";

        window.trans['account.error.firstname'] = "{!! trans('account.error.firstname') !!}";
        window.trans['account.error.lastname'] = "{{trans('account.error.lastname')}}";
        window.trans['account.error.telephone'] = "{{trans('account.error.telephone')}}";
        window.trans['account.error.email'] = "{{trans('account.error.email')}}";
        window.trans['account.error.password'] = "{{trans('account.error.password')}}";
        window.trans['account.error.confirm'] = "{{trans('account.error.confirm')}}";

        window.trans['account.menu.contacts'] = '{{trans('account.text.menu.contacts')}}';
        window.trans['account.menu.wishlist'] = '{{trans('account.text.menu.wishlist')}}';
        window.trans['account.menu.orders'] = '{{trans('account.text.menu.orders')}}';
        window.trans['account.menu.security'] = '{{trans('account.text.menu.security')}}';
        window.trans['account.menu.subscribes'] = '{{trans('account.text.menu.subscribes')}}';

        window.trans['account.button.logout'] = '{{trans('account.button.logout')}}';
        window.trans['account.text.heading.account'] = '{{trans('account.text.heading.account')}}';
        window.trans['common.button.remove'] = '{{trans('common.button.remove')}}';
        window.trans['account.error.empty-wishlist'] = '{{trans('account.error.empty-wishlist')}}';

        window.trans['account.error.empty-orders'] = '{{trans('account.error.empty-orders')}}';
        window.trans['account.text.order_id'] = '{{trans('account.text.order_id')}}';
        window.trans['account.text.customer'] = '{{trans('account.text.customer')}}';
        window.trans['account.text.status'] = '{{trans('account.text.status')}}';
        window.trans['account.text.total'] = '{{trans('account.text.total')}}';
        window.trans['account.text.date_added'] = '{{trans('account.text.date_added')}}';

        window.trans['account.text.product_name'] = '{{trans('account.text.product_name')}}';
        window.trans['account.text.order_detail'] = '{{trans('account.text.order_detail')}}';
        window.trans['account.text.payment'] = '{{trans('account.text.payment')}}';
        window.trans['account.text.shipping'] = '{{trans('account.text.shipping')}}';

    </script>

@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('left-column') @endsection

@section('content')

    <app-account :account-info="accountInfo"></app-account>

@endsection