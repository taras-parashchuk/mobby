@extends('layouts.standart')

@section('appData')
    @parent

    @if($main_location && $main_location->latitude && $main_location->longitude)
        <script>
            function initMap() {
                var place = {
                    lat: +'{{$main_location->latitude}}',
                    lng: +'{{$main_location->longitude}}'
                };
                var map = new google.maps.Map(document.getElementById('contactsMap'), {
                    zoom: 16,
                    center: place
                });
                var marker = new google.maps.Marker({
                    position: place,
                    map: map
                });
            }
        </script>
        <script async defer
                src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDE45YeC2wzs_u_2oVyl5eVUsjRfMf5h_M&language={{app()->getLocale()}}&region=ru-RU&callback=initMap"></script>
    @endif
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if ($(window).width() < 970) {
                $('.js-messanger-viber').each(function () {
                    $(this).attr('href', 'viber://add?number=' + $(this).data('telephone'));
                });
            } else {
                $('.js-messanger-viber').each(function () {
                    $(this).attr('href', 'viber://chat?number=' + $(this).data('telephone'));
                });
            }
        });
    </script>

@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('left-column') @endsection

@section('content')
    <div class="contactInfo">
        <h1 class="pageTitle">{{trans('pages.contacts.heading')}}</h1>
        <div class="contactInfo__content contactInfo__content--top">
            @if($main_location)
                @if($main_location->translate->address)
                    <div class="contactInfo__column">
                        <h4 class="contactInfo__subtitle">{{trans('pages.contacts.columns.address.heading')}}</h4>
                        <div class="contactInfo__inner">
                            <p>
                                @if( $main_location->latitude && $main_location->longitude)
                                    <a href="https://www.google.com/maps/place/{{$main_location->latitude}},{{$main_location->longitude}}"
                                       class="contactInfo__text">{{$main_location->translate->address}}</a>
                                @else
                                    <a href="javascript:void(0)"
                                       class="contactInfo__text">{{$main_location->translate->address}}</a>
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
                @if(count($main_location->TelephonesWithOperators))
                    <div class="contactInfo__column">
                        <h4 class="contactInfo__subtitle">{{trans('pages.contacts.columns.telephones.heading')}}</h4>
                        <div class="contactInfo__inner">
                            @foreach ($main_location->TelephonesWithOperators as $telephone)
                                <p class="c-contactInfo__phone">
                                    <a href="tel:{{$telephone['number'] }}" class="contactInfo__phone">
                                        {{ $telephone['tel'] }}
                                    </a>
                                    @if(count($main_location->messangers))
                                        <?php foreach ($messangers as $messanger) {
                                        foreach ($messanger['telephones'] as $messanger_telephone) {
                                        if ($telephone != $messanger_telephone) continue;
                                        switch ($messanger['name']) {
                                        case 'viber':
                                        ?>
                                        <a href="javascript:void(0)"
                                           class="contactInfo__messanger sb-icon-viber js-messanger-viber"
                                           data-telephone="<?php echo $messanger_telephone; ?>">
                                            <span class="path1"></span><span class="path2"></span><span
                                                    class="path3"></span><span class="path4"></span><span
                                                    class="path5"></span><span class="path6"></span>
                                        </a>
                                        <?php break;
                                        case 'whatsapp': ?>
                                        <a href="whatsapp://send?phone=<?php echo $messanger_telephone; ?>"
                                           class="contactInfo__messanger sb-icon-whatsapp">
                                            <span class="path1"></span><span class="path2"></span><span
                                                    class="path3"></span>
                                        </a>
                                        <?php break;
                                        }
                                        ?>
                                        <?php }
                                        } ?>
                                    @endif
                                </p>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (count($main_location->emails))
                    <div class="contactInfo__column">
                        <h4 class="contactInfo__subtitle">{{trans('pages.contacts.columns.emails.heading')}}</h4>
                        <div class="contactInfo__inner">
                            @foreach ($main_location->emails as $email)
                                <p><a href="mailto:{{$email}}"
                                      class="contactInfo__text">{{$email}}</a></p>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($main_location->translate->schedule)
                    <div class="contactInfo__column">
                        <h4 class="contactInfo__subtitle">{{trans('pages.contacts.columns.schedule.heading')}}</h4>
                        <div class="contactInfo__inner contactInfo__inner--table">
                            {!! $main_location->translate->schedule !!}
                        </div>
                    </div>
                @endif
            @endif
        </div>
        <div class="contactInfo__content contactInfo__content--between">
            <div class="contactInfo__map" id="contactsMap"></div>
            <form action="{{route('message.send')}}" method="POST"
                  class="form form--message contactInfo__form js-form js-form-validate" id="contactForm">
                <div class="form__title">
                    {{trans('common.text.write_shop')}}
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold" for="contact__name">
                        {{trans('form.entry.customer-name')}}
                    </label>
                    <input type="text" name="firstname"
                           class="form__control js-control form__control--maxw js-field-validate js-input-name"
                           id="contact__name" value="{{Auth::user()->firstname ?? ''}}" data-field="name">
                    <div class="form__validation js-validation">
                        {{trans('validation.between.string', ['attribute' => trans('validation.attributes.firstname'), 'min' => 2, 'max' => 32])}}
                    </div>
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold"
                           for="contact__email">{{trans('form.entry.customer-email')}}</label>
                    <input type="text" name="email" value="{{Auth::user()->email ?? ''}}"
                           class="form__control js-control form__control--maxw js-field-validate js-input-email"
                           id="contact__email" data-field="email">
                    <div class="form__validation js-validation">
                        {{trans('validation.email')}}
                    </div>
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold"
                           for="contact__message">{{trans('form.entry.message')}}</label>
                    <textarea
                            class="form__control js-control form__control--maxw form__control--textarea
                             form__control--textarea--message js-field-validate js-input-text"
                            name="text" id="contact__message" data-field="text" data-min="10"></textarea>
                    <div class="form__validation js-validation">
                        {{trans('validation.between.string', ['attribute' => trans('validation.attributes.message'), 'min' => 10, 'max' => 1000])}}
                    </div>
                </div>
                <div class="form__set form__set--left">
                    <input type="hidden" name="type" value="{{trans('common.text.write_shop')}}">
                    <input type="submit" class="form__btn form__btn--second btn btn--primary"
                           value="{{trans('form.button.send')}}"/>
                </div>
                <div class="loading js-loading">
                    <div class="loading__wrap">
                        <span class="icon icon--spin sb-icon-loading">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                    class="path4"></span><span class="path5"></span><span class="path6"></span><span
                                    class="path7"></span><span class="path8"></span><span class="path9"></span><span
                                    class="path10"></span><span class="path11"></span><span class="path12"></span>
                        </span>
                    </div>
                </div>
                <div class="form__result js-result"></div>
            </form>
        </div>
    </div>
@endsection