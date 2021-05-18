<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base" content="{{route('home')}}">

    <title>Wizor CMS</title>

    <link rel="stylesheet" href="{{asset('css/app-admin.css?v0.0.42')}}"><!--27-->

    <script>
        window.languages = @json($languages);
        window.adminLanguage = '{{app()->getLocale()}}';
        window.currency_code = '{{App\Models\Setting::get('currency')}}';
        window.translate = @json(trans('admin'));
    </script>

    <script src="{{asset('js/libraries/ckeditor/ckeditor.js')}}"></script>

</head>
<body>
    <div id="app">
        <app ref="app"></app>
    </div>
    <script src="{{asset('js/app-admin.js?v0.0.50')}}" type="text/javascript"></script>
    <script src="{{asset('vendor/laravel-filemanager/js/stand-alone-button.js')}}" type="text/javascript"></script>
</body>
</html>
