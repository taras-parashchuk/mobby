<div class="container">
    <div class="footerRow">
        @if ($categories)
            <div class="footerColumn">
                <div class="footerColumn__header footerColumn__header--withCollapse js-footer-collapse">
                    {{ trans('common.text.category')}}
                </div>
                <div class="footerColumn__content js-footer-collapse-content">
                    @foreach($categories as $category)
                        <div class="footerColumn__linkContainer">
                            <a href="{{ $category->href}}" class="footerColumn__link"
                               title="{{ $category->translate->name}}">
                                {{ $category->translate->name}}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if ($service_links->count())
            <div class="footerColumn">
                <div class="footerColumn__header footerColumn__header--withCollapse js-footer-collapse">
                    {{ trans('common.text.service_link_heading')}}
                </div>
                <div class="footerColumn__content js-footer-collapse-content">
                    @foreach ($service_links as $service_link)
                        <div class="footerColumn__linkContainer">
                            <a href="{{ $service_link['href']}}" class="footerColumn__link">
                                {{ $service_link['name']}}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="footerColumn">
            <div class="footerColumn__header footerColumn__header--withCollapse js-footer-collapse">
                {{ trans('common.text.forCustomer') }}
            </div>
            <div class="footerColumn__content js-footer-collapse-content">
                @if (Auth::check())
                    <div class="footerColumn__linkContainer">
                        <a href="{{ route('account') . '/orders' }}" class="footerColumn__link">
                            {{ trans('common.text.orders_history') }}
                        </a>
                    </div>
                    <div class="footerColumn__linkContainer">
                        <a href="{{ route('account') . '/wishlist' }}" class="footerColumn__link">
                            {{ trans('common.text.wishlist_list') }}
                        </a>
                    </div>
                @else
                    <div class="footerColumn__linkContainer">
                        <a href="{{ route('account.register')}}" class="footerColumn__link">
                            {{trans('common.text.register')}}
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <div class="footerColumn footerColumn--contacts">
            <div class="footerColumn__header footerColumn__header--contacts">{{ trans('common.text.contact') }}</div>
            <div class="js-footer-section-contacts footerColumn__mContacts"></div>
            <div class="js-footer-section-schedule footerColumn__mSchedule"></div>
            <div class="js-footer-section-call footerColumn__mCall"></div>

            @if($main_location && count($main_location->telephonesWithOperators))
                <div class="footerColumn__contactItem js-footer-to-column-first footerTelephones">
                    @foreach ($main_location->telephonesWithOperators as $telephone)
                        <div class="footerColumn__linkContainer">
                            <a href="tel:{{ $telephone['number']}}" class="footerColumn__link">
                                {{ $telephone['tel']}}
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($locations->count() && $locations->first()->translate->address)
                <div class="footerColumn__contactItem js-footer-to-column-first footerAddr">
                    @foreach($locations as $location)
                        @if($location->translate->address)
                            <div class="footerColumn__linkContainer">
                                @if($location->longitude && $location->latitude)
                                    <a href="https://www.google.com/maps/place/{{$location->latitude}},{{$location->longitude}}"
                                       class="footerColumn__link">
                                        {{ $location->translate->address}}
                                    </a>
                                @else
                                    <a href="https://www.google.com/maps/place/{{$location->translate->address}}"
                                       class="footerColumn__link">
                                        {{ $location->translate->address}}
                                    </a>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if ($main_location && count($main_location->emails))
                <div class="footerColumn__contactItem js-footer-to-column-first footerEmail">
                    @foreach($main_location->emails as $email)
                        <div class="footerColumn__linkContainer">
                            <a href="mailto:{{ $email}}" class="footerColumn__link">
                                {{ $email}}
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($socials)
                <div class="socialNetwork js-footer-to-column-first">
                    @foreach($socials as $social)
                        <a href="{{ $social['href']}}" class="socialNetwork__link">
                            <i class="icon {{ $social['icon']}}"></i>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="hr"></div>
    </div>
</div>
<hr>
<div class="container footer--mNone">
    <div class="footerRow js-big-container-for-els-foot">
        <div id="footLogo" class="footerColumn footerColumn--wp footerColumn--withBorder">
            <div>
                <img src="{{ $logo}}" alt="" class="footerColumn__logo img-responsive">
            </div>
            <p class="footerColumn__about">{{ $about}}</p>
        </div>
        @if($main_location)
            <div id="footSchedule" class="footerColumn footerColumn--wp js-footer-to-column-second">
                <div class="footerColumn__header">
                    {{ trans('common.text.schedule')}}
                </div>
                {!! $main_location->translate->schedule !!}
            </div>
        @endif
        <div id="footModals" class="footerColumn footerColumn--end footerColumn--wp js-footer-to-column-third">
            <div class="footerColumn__header">
                {{ trans('common.text.call_shop')}}
            </div>
            <a href="#callbackFormwrite" class="footerColumnBtn js-open-modal">
                {{ trans('common.text.write_shop')}}
            </a>
            <a href="#callbackFormcall" class="footerColumnBtn js-open-modal">
                {{ trans('common.text.call_me')}}
            </a>
        </div>
    </div>
</div>
<hr class="footer--mNone">
<div class="container">
    <div class="powered">
        {{ $powered}}
    </div>
</div>