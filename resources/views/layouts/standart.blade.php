<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base" content="{{route('home')}}">

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-PRXRBBF');</script>
    <!-- End Google Tag Manager -->

    {!!  $script_tags['head'] !!}

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}

    <link rel="stylesheet" href="{{mix('css/app.css')}}">
    <link rel="shortcut icon" href="{{$favicon}}" type="image/x-icon">
@section('appData')
        <script>
            global_lang_config = {
                'close': 'Закрити'
            };
            window.routes = {
                home: '{{ route('home') }}',
                setView: "{{url('set-view-type')}}",
                logout: "{{route('account.logout')}}",
                checkout: "{{route('checkout')}}"
            };

            window.theme = 'default';

            @if(app()->getLocale() != config('languages.mainLanguage'))
                window.routePrefix = '/{{app()->getLocale()}}';
            @else
                window.routePrefix = '';
            @endif

                window.trans = {
                'cart.text.heading': '{{trans('cart.text.heading')}}',
                'common.text.quantity': '{{trans('common.text.quantity')}}',
                'cart.button.return': '{{trans('cart.button.return')}}',
                'cart.text.total': '{{trans('cart.text.total')}}',
                'cart.button.checkout': '{{trans('cart.button.checkout')}}',
                'cart.text.empty': '{{trans('cart.text.empty')}}',
                'common.button.in_cart': '{{trans('cart.text.in_cart')}}',
                'cart.button.continue': '{{trans('cart.button.continue')}}',
                'common.text.model': '{{trans('common.text.model')}}',
                'common.button.cart': '{{trans('common.button.cart')}}',
                'common.labels.hit': '{{trans('common.labels.hit')}}',
                'common.text.category': '{{trans('common.text.category')}}',
                'common.text.loading': '{{trans('common.text.loading')}}',
                'common.button.save': '{{trans('common.button.save')}}',
                'common.button.remove': '{{trans('common.button.remove')}}',
                'common.text.empty': '{{trans('common.text.empty')}}',
                'common.button.home': '{{trans('common.button.home')}}',
                'common.text.what_are_you_searching': '{{trans('common.text.what_are_you_searching')}}',
                'common.currency_symbol': '{{currency()->getCurrencies()[currency()->getUserCurrency()]['symbol']}}',
                'common.text.price': '{{trans('common.text.price')}}',
                'common.text.outstock': '{{trans('catalog.text.out_stock')}}',
                'catalog.text.load_more': '{{trans('catalog.text.load_more')}}'
            };
        </script>
    @show()

</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PRXRBBF"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
{!! $script_tags['start_body'] !!}
<div id="wizorStore">
    <header>
        @include('common.header')
    </header>


    @section('content-wrapper')
    @section('bellow-header')
        @include('common.bellow_header')
    @show
    <div class="App container {{Route::currentRouteName()}}Page" id="{{Route::currentRouteName()}}Page">
        @if(Route::currentRouteName() === 'home')
            <div class="row">
                @section('left-column')
                    @include('common.left_column')
                @show
                <div id="content">
                    @yield('content-top')
                    @yield('content')
                    @yield('content-bottom')
                </div>
            </div>
        @else
        @section('breadcrumbs')@show
        <div class="{{Route::currentRouteName()}}Info">
            @section('content-top')
                @include('common.content_top')
            @show
            @yield('content')
            @section('content-bottom')
                @include('common.content_bottom')
            @endsection
        </div>
        @endif
    </div>
    @show


    <footer class="footer @if(config('tenancy.theme') === 'little') footer--little @endif">
        @include('common.footer')
        @include('form.message', [
            'postfix' => 'write',
            'title' => trans('common.text.write_shop'),
            'show_all_form' => true,
        ])
        @include('form.message', [
            'postfix' => 'call',
            'title' => trans('common.text.call_me'),
            'show_all_form' => false,
            'call_time' => false
        ])
        <modal-cart ref="cart"></modal-cart>
    </footer>
</div>

@section('scripts')
    <script src="catalog/view/javascript/jquery/jquery-2.1.1.min.js" type="text/javascript"></script>
    <!-- start-scripts -->
@endsection


<!--<link href="catalog/view/javascript/jquery/magnific/magnific-popup.css" rel="stylesheet" media="screen"/>-->

@foreach ($styles as $style) { ?>
<link href="{{ $style['href']}}" type="text/css" rel="{{ $style['rel']}}"
      media="{{ $style['media']}}"/>
@endforeach
<!--
    <link rel="stylesheet" href="{{asset('sass/libraries/jquery.mCustomScrollbar.min.css')}}">

    <script src="catalog/view/javascript/jquery/jquery-2.1.1.min.js" type="text/javascript"></script>
    <script src="catalog/view/javascript/jquery/jquery-ui.min.js" type="text/javascript"></script>


    <script>
        $("a[href='#form-callback-contact']").click(function (e) {
            e.preventDefault();
            $("#connectModal").modal('show');
        });
        $("a[href='#form-callback-shortform']").click(function (e) {
            e.preventDefault();
            $("#phoneModal").modal('show');
        });
        /* Search */
        $('#mSearch').find('.js-search-submit').on('click', function (e) {
            e.preventDefault();

            var url = $('base').attr('href') + 'search';

            var value = $('#mSearch input[name=\'search\']').val();

            if (value) {
                url += '?search=' + encodeURIComponent(value);
            }
            location = url;
        });

        $('#mSearch input[name=\'search\']').on('keydown', function (e) {
            if (e.keyCode == 13) {
                $('#mSearch').find('.js-search-submit').trigger('click');
            }
        });
    </script>

    <script src="catalog/view/javascript/jquery-maskedinput/jquery.maskedinput.min.js" type="text/javascript"></script>
    <script src="catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js"></script>

    <script src="catalog/view/javascript/lodash-min.js?v.1.0.0"></script>
    -->

<!--dev
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
-->

<script src="{{mix('js/app.js')}}" type="text/javascript"></script>
<script src="{{asset('js/libraries/jquery.mCustomScrollbar.min.js')}}"></script>

<script src="{{asset('js/libraries/vuescroll.js')}}"></script>
<!-- import vuescroll-native -->
<script src="{{asset('js/libraries/vuescroll-native.js')}}"></script>

<!--
    @foreach ($scripts as $script) { ?>
    <script src="{{ $script}}" type="text/javascript"></script>
    @endforeach

        <script src="catalog/view/javascript/jquery/customScroll/jquery.mCustomScrollbar.min.js"></script>

        <script src="catalog/view/javascript/tether/js/tether.min.js" type="text/javascript"></script>

-->
<div class="bg-loading"></div>
</footer>
<script>
    {!! $script_tags['end_body'] !!}
</script>
</body>
</html>
