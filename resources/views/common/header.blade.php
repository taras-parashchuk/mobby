<nav class="mobileMenu">

    <div class="l-d-none">
        <div id="mobileFormLogin"></div>
    </div>
    <div class="mobileMenu__head" id="mobileMenu__close">
        <i class="icon sb-icon-cancel"></i>
    </div>

    <div class="mobileMenu__content">
        <search-field :is-mobile="true" text-more="{{trans('search.text.more')}}"
                      placeholder="{{trans('common.text.search')}}"></search-field>

        <div class="js-m-container-for-scroll">
            <vue-scroll :ops="ops">

                <div class="mobileMenuSection__wrapper">

                    <div class="mobileMenuSection" v-if="menuCategoriesGroups">
                        <div class="mobileMenuSection__header mobileMenuSection__header--offset_middle">{{trans('common.text.category')}}</div>
                        <ul class="mobileMenu__section mobileMenu__section--first mobileMenu__section--open mobileMenu__section--offset_middle js-mobile-section"
                            data-level="1" id="mobile-categories-1">
                            <li v-for="category in menuCategoriesGroups[0]"
                                class="mobileMenuSection__item mobileMenuSection__item--offset_middle">
                                <a class="mobileMenuSection__link js-menu-link"
                                   :class="{'js-menu-link-with-child mobileMenuSection__link--hasMore' : category.descendants_count > 0}"
                                   :href="category.href"
                                   :data-id="category.id"
                                   :data-parent="category.parent_id">
                                    <span>@{{category.translate.name}}</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="mobileMenuSection">
                        <div class="mobileMenuSection__header">{{trans('common.text.menu')}}</div>
                        <ul class="mobileMenuSection__content">
                            @isset($informations)
                                @foreach ($informations as $information)
                                    <li class="mobileMenuSection__item">
                                        <a href="{{ $information['href']}}"
                                           class="mobileMenuSection__link">{{ $information->translate->name}}</a>
                                    </li>
                                @endforeach
                            @endif
                            <li class="mobileMenuSection__item">
                                <a href="{{route('contacts')}}"
                                   class="mobileMenuSection__link">{{trans('common.text.contact')}}</a>
                            </li>
                            <li class="mobileMenuSection__item">
                                <a href="{{route('comparelist')}}"
                                   class="mobileMenuSection__link">{{trans('common.text.compare')}}</a>
                            </li>

                            <li class="mobileMenuSection__item">
                                @if (Auth::check())
                                    <a href="{{route('account')}}"
                                       class="mobileMenuSection__link mobileMenuSection__link--user"><i
                                                class="icon sb-icon-user"></i>{{trans('common.text.account')}}</a>
                                @else
                                    <a href="#mobileFormLogin"
                                       class="mobileMenuSection__link mobileMenuSection__link--user js-open-modal"><i
                                                class="icon sb-icon-user"></i>{{trans('common.text.login')}}</a>
                                @endif
                            </li>

                        </ul>
                    </div>

                    <div class="mobileMenuSection">
                        <ul class="mobileMenuSection__content">
                            <li class="mobileMenuSection__item js-m-languages-container" id="mLanguages"></li>
                        </ul>
                    </div>

                </div>

            </vue-scroll>
        </div>

    </div>
    <template v-if="menuCategoriesGroups[1]">
        <ul class="mobileMenu__section js-mobile-section" data-level="2" id="mobile-categories-2">
            <li class="mobileMenuSection__item--inner mobileMenu__head mobileMenu__head--left js-mobile-category-return">
                <span class="icon sb-icon-down-arrow"></span>
                <span class="mobileMenu__return--text">{{ trans('common.text.category')}}</span>
            </li>
            <li v-for="category in menuCategoriesGroups[1]"
                class="mobileMenuSection__item mobileMenuSection__item--inner">
                <a class="mobileMenuSection__link js-menu-link"
                   :class="{'js-menu-link-with-child mobileMenuSection__link--hasMore' : category.descendants_count > 0}"
                   :href="category.href"
                   :data-id="category.id"
                   :data-parent="category.parent_id">
                    <span>@{{category.translate.name}}</span>
                </a>
            </li>
        </ul>
        <template v-if="menuCategoriesGroups[2]">
            <ul class="mobileMenu__section js-mobile-section" data-level="3" id="mobile-categories-3">
                <li class="mobileMenuSection__item--inner mobileMenu__head mobileMenu__head--left js-mobile-category-return">
                    <span class="icon sb-icon-down-arrow"></span>
                    <span class="mobileMenu__return--text">{{ trans('common.text.category')}}</span>
                </li>

                <li v-for="category in menuCategoriesGroups[2]"
                    class="mobileMenuSection__item mobileMenuSection__item--inner">
                    <a class="mobileMenuSection__link js-menu-link"
                       :class="{'js-menu-link-with-child mobileMenuSection__link--hasMore' : category.descendants_count > 0}"
                       :href="category.href"
                       :data-id="category.id"
                       :data-parent="category.parent_id">
                        <span>@{{category.translate.name}}</span>
                    </a>
                </li>

            </ul>
            <template v-if="menuCategoriesGroups[3]">
                <ul class="mobileMenu__section js-mobile-section" data-level="4" id="mobile-categories-4">
                    <li class="mobileMenuSection__item--inner mobileMenu__head mobileMenu__head--left js-mobile-category-return">
                        <span class="icon sb-icon-down-arrow"></span>
                        <span class="mobileMenu__return--text">{{ trans('common.text.category')}}</span>
                    </li>
                    <li v-for="category in menuCategoriesGroups[3]"
                        class="mobileMenuSection__item mobileMenuSection__item--inner">
                        <a class="mobileMenuSection__link js-menu-link"
                           :href="category.href"
                           :data-id="category.id"
                           :data-parent="category.parent_id">
                            <span>@{{category.translate.name}}</span>
                        </a>
                    </li>
                </ul>
            </template>
        </template>
    </template>
</nav>
<nav class="top-container">
    <div class="container">
        <div class="row">
            <div class="topMenu">
                @isset($informations)
                    @foreach($informations as $information)
                        <a href="{{ $information->href}}" class="topMenu__link">
                            {{ $information->translate->name}}
                        </a>
                    @endforeach
                @endif
                <a class="topMenu__link" href="{{route('articles')}}">
                    {{trans('pages.blog.heading')}}
                </a>
                <a class="topMenu__link" href="{{route('contacts')}}">
                    {{trans('common.text.contact')}}
                </a>
                <a class="topMenu__link" href="{{route('testimonials')}}">
                    {{trans('common.text.testimonials')}}
                </a>
            </div>
            <div class="topTools">
                @include('common.currency')
                @include('common.language')
                <div class="topCustomerInfo">
                    @if (!Auth::check())
                        <a href="javascript:void(0)"
                           class="dropdownMenu__link dropdownMenu__link--login topCustomerInfo__link js-dropdown-toggle js-topform-login">{{trans('common.text.login')}}</a>
                        <div class="dropdownMenu__drop dropdownMenu__drop--login dropdownMenu__drop--under js-dropdown-content"
                             id="formTopLoginContainer">
                            @include('form.login')
                        </div>
                    @else
                        <a class="topCustomerInfo__link" href="/account">
                            {{Auth::user()->firstname}}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</nav>
<div class="container">
    <header class="row">
        <div id="logo">
            @if ($logo)
                <a href="{{route('home')}}">
                    <img src="{{ $logo}}" title="{{ SEO::getTitle()}}"
                         alt="{{ SEO::getTitle() }}" class="img-responsive"/></a>
            @else
                <h1><a href="{{route('home')}}">{{ $company_name }}</a></h1>
            @endif
        </div>
        <search-field :is-mobile="false" text-more="{{trans('search.text.more')}}"
                      placeholder="{{trans('common.text.search')}}"></search-field>
        <div class="headerTools js-container-header-tools">
            @if($main_location && count($main_location->telephonesWithOperators))
                <div class="headerTools__item headerTools__item--phone">
                    <div class="headerTools--left">
                        <i class="icon sb-icon-headphones"></i>
                    </div>
                    <div class="headerTools--right headerTools--withDrop">
                        <div class="dropdownMenu dropdownMenu--dropParent">
                            <span class="dropdownMenu__link js-dropdown-toggle">{{ $main_location->telephonesWithOperators['0']['tel']}}</span>
                            <div class="dropdownMenu__drop dropdownMenu__drop--under js-dropdown-content">
                                @foreach ($main_location->telephonesWithOperators as $telephone)
                                    <div>
                                        <a href="tel:{{ $telephone['number']}}"
                                           class="dropdownMenu__link dropdownMenu__link--tel dropdownMenu__link--O{{ $telephone['operator']}}">{{ $telephone['tel']}}</a>
                                    </div>
                                @endforeach
                                <div style="margin-top: 10px; color: #000">
                                    {!! $main_location->translate->schedule !!}
                                </div>
                            </div>
                        </div>
                        <a href="#callbackFormcall" class="headerTools__call js-open-modal">
                            {{ trans('common.text.call_me')}}
                        </a>
                    </div>
                </div>
            @endif
            <a href="{{route('comparelist')}}"
               class="headerTools__item"
               :class="{'headerTools__item--active':$store.getters.countProductsList('comparelist')}"
               id="compare">
                <i class="icon sb-icon-libra"></i>
                <span class="headerTools__title">
                        {{trans('common.text.compare')}}
                    </span>
                <span class="headerTools__badge headerTools__badge--compare"
                      id="compareTotal">
                    @{{ $store.getters.countProductsList('comparelist') }}
                </span>
            </a>
            @if(Auth::check())
                <a href="{{ route('account') . '/wishlist' }}"
                   class="headerTools__item"
                   :class="{'headerTools__item--active':$store.getters.countProductsList('wishlist')}"
                   id="wishlist">
                    <i class="icon sb-icon-like"></i>
                    <span class="headerTools__title">
                    {{trans('common.text.wishlist')}}
                    </span>
                    <span class="headerTools__badge" id="wishlistTotal">@{{ $store.getters.countProductsList('wishlist')}}</span>
                </a>
            @else
                <a href="javascript:void(0)"
                   class="js-open-login-form headerTools__item {{ ($wishlistCount > 0) ? 'headerTools__item--active' : ''}}"
                   id="wishlist">
                    <i class="icon sb-icon-like"></i>
                    <span class="headerTools__title">
                    {{trans('common.text.wishlist')}}
                    </span>
                    <span class="headerTools__badge" id="wishlistTotal">{{ $wishlistCount}}</span>
                </a>
            @endif
            <a href="#cart_modal_content"
               class="headerTools__item js-open-modal-cart"
               :class="'headerTools__item js-open-modal-cart' + ($store.getters.getCartProducts.length ? ' headerTools__item--active' : '')">
                <i class="icon sb-icon-shopping-cart"></i>
                <span class="headerTools__title">
                    {{trans('cart.text.heading')}}
                </span>
                <span class="headerTools__badge" id="cart-items">
                    @{{ $store.getters.getCartProducts.length }}
                </span>
            </a>
        </div>
    </header>
</div>
<nav class="mNav js-mobile-navigation">
    <div class="container">
        <div class="l-flex row--between">
            <a href="javascript:void(0)" class="mNav__openMenu" id="js-open-mobile-menu">
                <i class="icon sb-icon-mMenu"></i>
                {{trans('common.text.menu')}}
            </a>
            <div class="js-container-mobile-cart">
                <a href="#cart_modal_content"
                   class="headerTools__item js-open-modal-cart"
                   :class="'headerTools__item js-open-modal-cart' + ($store.getters.getCartProducts.length ? ' headerTools__item--active' : '')">
                    <i class="icon sb-icon-shopping-cart"></i>
                    <span class="headerTools__title">
                    {{trans('cart.text.heading')}}
                </span>
                    <span class="headerTools__badge" id="cart-items">
                    @{{ $store.getters.getCartProducts.length }}
                </span>
                </a>
            </div>
        </div>
    </div>
</nav>