@extends('layouts.standart')

@section('appData')

    @parent

    <script>
        window.dataPage = {};

        document.addEventListener("DOMContentLoaded", function () {

            $stickyCard = $('.js-stickyCard');
            $stickyCard.data('position', $stickyCard.position().top);

            $(window).scroll(function () {

                if ($stickyCard.data('position') <= $(window).scrollTop()) {
                    $stickyCard.addClass('js-stickyCard--true');
                } else {
                    $stickyCard.removeClass('js-stickyCard--true');
                }
            });

            if ($(window).width() > 970) {
                $(".js-popup-image > img").imagezoomsl({
                    zoomrange: [1.5, 1.5],
                    innerzoom: true,
                    magnifierborder: "none",
                    zindex: 0
                });
            } else {
                $('.js-product-gallery').magnificPopup({
                    type: 'image',
                    delegate: "a:not('.productInfo__addt--active, .action__add')",
                    gallery: {
                        enabled: true
                    }
                });
            }

            $('.js-review .form__ratingLabel').on('mouseover click', 'i.icon', function () {
                updateRating($(this));
            });

            _.debounce(function () {
                $('.tab__link').first().trigger('click')
            }, 10)();

        });

    </script>

@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName(), $product) }}
@endsection

@section('left-column') @endsection

@section('content')

    <div class="productInfo__top">
        <h1 class="productInfo__title" ref="productTitle">{{$product->title}}</h1>
        <div class="productInfo__topRight">
            @if ($product->manufacturer)
                <div class="productInfoBrand">
                <span class="productInfoBrand__text">
                    {{trans('catalog.text.manufacturer')}}
                </span>
                    <span class="productInfoBrand__link">
                            {{$product->manufacturer->title}}
                        </span>
                </div>
            @endif
            <div class="productInfoCode">
                    <span class="productInfoCode__text">
                        {{trans('catalog.text.model')}}:
                    </span>
                <span class="productInfoCode__val">
                        {{$product->id}}
                    </span>
            </div>
        </div>
    </div>
    <div class="l-flex">
        <div class="l-flex l-w75">
            <div class="l-flex l-flex-100-pr productInfo__mRating">
                @if ($reviewTotal !== false)
                    <div class="rating">
                        @php $i = 0; @endphp
                        @while ($i < 5)
                            @if ($i < $reviewTotal)
                                <i class="icon sb-icon-star rating--fill"></i>
                            @else
                                <i class="icon sb-icon-star rating--null"></i>
                            @endif
                            @php $i++; @endphp
                        @endwhile
                        <a href="javascript:void(0)"
                           data-scroll-to="tab_review_title"
                           class="rating__link rating__link--inProduct js-scrollTo">{{trans_choice('common.text.count.reviews', $reviewCount, ['count' => $reviewCount])}}</a>
                    </div>
                @endif
                <div class="share share--inProduct share--pLap">
                    <span class="share__text">{{trans('common.text.share')}}:</span>
                    <a href="javascript:void(0)" class="icon sb-icon-facebook-round share__link"
                       onclick="this.href='http://www.facebook.com/share.php?u='+encodeURIComponent(window.location.href)"
                       target="_blank">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                class="path4"></span>
                    </a>
                    <a href="javascript:void(0)" class="icon sb-icon-twitter share__link"
                       onclick="this.href='http://twitter.com/share?text='+document.title+'&url='+encodeURIComponent(window.location.href)"
                       target="_blank">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                class="path4"></span>
                    </a>
                </div>
                <div class="l-flex productInfoBC__container">
                    @if ($product->manufacturer)
                        <div class="productInfoBrand productInfoBrand--m">
                            <span class="productInfoBrand__text">{{trans('catalog.text.manufacturer')}}</span>
                            <a href="{{route('manufacturer', ['id' => $product->manufacturer->id, 'slug' => $product->manufacturer->slug])}}"
                               class="productInfoBrand__link">
                                {{$product->manufacturer->title}}
                            </a>
                        </div>
                    @endif

                    <div class="productInfoCode productInfoCode--m">
                        <span class="productInfoCode__text">{{trans('catalog.text.model')}}</span>
                        <span class="productInfoCode__val">{{$product->id}}</span>
                    </div>

                </div>
            </div>
            <div class="productInfoLeft">
                <div class="productInfo__image js-product-gallery">

                    <a href="{{$product->imageInfo->popup}}" class="productInfo__main js-popup-image"
                       ref="main_product_image_wrapper">
                        <img data-large="{{$product->imageInfo->popup}}" src="{{$product->imageInfo->thumb}}"
                             alt="{{$product->translate->name}}" class="img-responsive"
                             ref="main_product_image_content">
                    </a>

                    @if(count($product->images))

                        <product-images-slider :items='@json($product->images)'></product-images-slider>
                    @endif

                    <div class="productInfo__labels">
                        @if ($product->special)
                            <div class="label label--share">
                                {{$product->special_diff}}
                            </div>
                        @endif
                        @if ($product->hit)
                            <div class="label label--hit label--col">
                                {{trans('common.labels.hit')}}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="productInfoCenter">
                <div class="contentCol">
                    <div class="available l-flex">
                        @if ($product->available)
                            <span class="available__point available__point--inStock"></span>
                        @else
                            <span class="available__point available__point--outStock"></span>
                        @endif
                        <span class="available__text">
                            {{$product->stockTitle}}
                        </span>
                    </div>
                    <div class="action action--inProduct">

                        <a v-if="Object.keys($root.accountInfo).length" href="javascript:void(0)"
                           @click="!$store.getters.hasProductsListProduct('wishlist',{{$product->id}}) ? $store.dispatch('addToProductsList', {type:'wishlist',param: {{$product->id}}}) : ''"
                           class="action__add action__add--like action__add--inProduct"
                           :class="{'action__add--active': $store.getters.hasProductsListProduct('wishlist',{{$product->id}})}">
                            <i class="icon sb-icon-like"></i>
                            <span class="action__text">
                                <template v-if="$store.getters.hasProductsListProduct('wishlist',{{$product->id}})">
                                    {{trans('catalog.button.added.wishlist')}}
                                </template>
                                <template v-else>
                                    {{trans('catalog.button.add.wishlist')}}
                                </template>
                            </span>
                        </a>
                        <a v-else class="action__add action__add--like action__add--inProduct js-open-login-form"
                           href="javascript:void(0)">
                            <i class="icon sb-icon-like"></i>
                            <span class="action__text">
                                {{trans('catalog.button.add.wishlist')}}
                            </span>
                        </a>
                        <a href="javascript:void(0)"
                           class="action__add action__add--compare action__add--inProduct"
                           :class="{'action__add--active': $store.getters.hasProductsListProduct('comparelist',{{$product->id}})}"
                           @click="!$store.getters.hasProductsListProduct('comparelist',{{$product->id}}) ? $store.dispatch('addToProductsList',{type:'comparelist',param: {{$product->id}}}) : ''">
                            <i class="icon sb-icon-libra"></i>
                            <span class="action__text">
                                <template v-if="$store.getters.hasProductsListProduct('comparelist',{{$product->id}})">
                                    {{trans('catalog.button.added.comparelist')}}
                                </template>
                                <template v-else>
                                    {{trans('catalog.button.add.comparelist')}}
                                </template>
                            </span>
                        </a>
                    </div>
                    <div class="price price--inProduct">
                        @if ($product->special)
                            <span class="price__share price__share--inProduct js-price-share">{{$product->specialFormat}}</span>
                            <span class="price__default price__default--inProduct price__default--withShare js-price-default">{{$product->priceFormat}}</span>
                        @else
                            <span class="price__default price__default--noShare js-price-default">{{$product->priceFormat}}</span>
                        @endif
                    </div>

                    @if(count($variant_attributes))

                        <variations :input-options='@json($variant_attributes)'
                                    main-product-id="{{$product->main_product->id}}"></variations>

                    @endif

                    <div class="productInfo__buy">

                        <btn-add-to-cart :available="{{$product->available}}" :id="{{(int)$product->id}}"
                                         default-class="btn btn--primary productInfo__btn"
                                         v-on:trigger-show-cart="onShowCart"></btn-add-to-cart>

                    </div>

                    @if($product->available)
                        <div class="productInfo__fastOrder fastOrder">
                            <div class="fastOrder-l">
                                <input type="text"
                                       value="{{auth()->user()->telephone ?? ''}}"
                                       class="fastOrder__control js-telMask js-fast-order-control"
                                       placeholder="+38 (0__) ___-__-__">
                                <a href="javascript:void(0)"
                                   class="btn btn--red fastOrder__btn"
                                   @click='fastOrder({{$product->id}})'>
                                    {{trans('catalog.button.add.fast_order')}}
                                </a>
                                <div class="form__result form__result--inProduct js-result"></div>
                            </div>
                        </div>
                    @endif

                    <div class="share share--inProduct share--pMobile">
                        <span class="share__text">
                            {{trans('common.text.share')}}
                        </span>
                        <a href="javascript:void(0)" class="icon sb-icon-facebook-round share__link"
                           onclick="this.href='http://www.facebook.com/share.php?u='+encodeURIComponent(window.location.href)"
                           target="_blank">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                    class="path4"></span>
                        </a>
                        <a href="javascript:void(0)" class="icon sb-icon-twitter share__link"
                           onclick="this.href='http://twitter.com/share?text='+document.title+'&url='+encodeURIComponent(window.location.href)"
                           target="_blank">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                    class="path4"></span>
                        </a>
                    </div>

                    @if ($short_attributes->count())
                        <div class="productInfo__attr attr">
                            <div class="attr__title">
                                {{trans('catalog.text.short_attr')}}
                            </div>
                            <table class="attr__table">
                                @foreach ($short_attributes as $attribute_group)
                                    <tr>
                                        <td class="attr__name">{{ $attribute_group->attribute->translate->name }}:</td>
                                        <td class="attr__value">
                                            @foreach($attribute_group->values as $value)
                                                {{$value->translate->value }}
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            <a href="#tab_attribute_title" class="attr__more js-scrollTo"
                               data-scroll-to="tab_attribute_title">
                                {{trans('catalog.button.show.more_attr')}}
                            </a>
                        </div>
                    @endif

                </div>
            </div>

            <div class="productInfoBottom">
                <div class="tab productInfo__tabs">
                    <ul class="tab__list tab__list--top" role="tablist">
                        @if ($product->translate->description)
                            <li>
                                <a href="#tab_description" class="tab__link" role="tab">
                                    {{trans('common.tab.description')}}
                                </a>
                            </li>
                        @endif
                        @if ($to_attributes->count())
                            <li>
                                <a href="#tab_attribute" class="tab__link" id="tab_attribute_title"
                                   data-scroll-target="tab_attribute_title" role="tab">
                                    {{trans('common.tab.attribute')}}
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="#tab_review" class="tab__link" id="tab_review_title"
                               data-scroll-target="tab_review_title" role="tab">
                                {{trans('common.tab.review')}}
                            </a>
                        </li>
                    </ul>
                    <div class="tab__list tab__list--bottom">
                        @if ($product->translate->description)
                            <div class="tab__content tab__content--description" id="tab_description">
                                <div class="productInfo__description html--default">
                                    {!! htmlspecialchars_decode($product->translate->description) !!}
                                </div>
                            </div>
                        @endif
                        @if ($to_attributes->count())
                            <div class="tab__content tab__content--attribute" id="tab_attribute">
                                <table class="attr__table">
                                    @foreach ($to_attributes as  $attribute_group)
                                        <tr class="attr__row">
                                            <td class="attr__name attr__name--inTab">
                                                {{$attribute_group->attribute->translate->name}}:
                                            </td>
                                            <td class="attr__value">
                                                @foreach($attribute_group->values as $k => $value){{($k !== 0 ? ', ':'').$value->translate->value}}@endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        @endif

                        <div class="tab__content tab__content--review" id="tab_review">
                            <form id="form-review"
                                  class="form form--review js-form js-review js-form-validate"
                                  action="{{route('testimonial.send', ['product_id' => $product->type === 3 ? $product->main_id : $product->id])}}"
                                  method="POST">
                                <div id="review">
                                    @if ($product->testimonials->count())
                                        @foreach ($product->testimonials as $review)
                                            <div class="userTestimonial__single userTestimonialSingle">
                                                <div class="userTestimonialSingle__top l-flex">
                                                    @if ($review->date_added && $review->name)
                                                        <span class="userTestimonialSingle__name">{{$review->name}}</span>
                                                        <span class="userTestimonialSingle__date">{{$review->date_added}}</span>
                                                    @endif
                                                </div>

                                                <div class="l-flex">
                                                    <div class="userTestimonialSingle__rating rating">
                                                        <?php $i = 0; while ($i < 5) { if ($i < $review->rating) { ?>
                                                        <i class="icon sb-icon-star rating--fill"></i>
                                                        <?php } else { ?>
                                                        <i class="icon sb-icon-star rating--null"></i>
                                                        <?php } $i++; } ?>
                                                    </div>
                                                </div>

                                                @if ($review->text)
                                                    <div class="l-flex userTestimonialSingle__text">
                                                        <p>{{$review->text}}</p>
                                                    </div>
                                                @endif

                                            </div>
                                        @endforeach
                                    @else
                                        <p>{{trans('catalog.text.empty_reviews')}}</p>
                                    @endif
                                </div>
                                <div class="form__set">
                                    <label class="form__label">{{trans('form.label.rating')}}</label>
                                    <div class="rating">
                                        <input class="form__rating" type="radio" name="rating" id="rating1"
                                               value="1"/>
                                        <label for="rating1" class="form__ratingLabel">
                                            <i class="icon sb-icon-star rating--null"></i>
                                        </label>

                                        <input class="form__rating" type="radio" name="rating" id="rating2"
                                               value="2"/>
                                        <label for="rating2" class="form__ratingLabel">
                                            <i class="icon sb-icon-star rating--null"></i>
                                        </label>

                                        <input class="form__rating" type="radio" name="rating" id="rating3"
                                               value="3"/>
                                        <label for="rating3" class="form__ratingLabel">
                                            <i class="icon sb-icon-star rating--null"></i>
                                        </label>

                                        <input class="form__rating" type="radio" name="rating" id="rating4"
                                               value="4"/>
                                        <label for="rating4" class="form__ratingLabel">
                                            <i class="icon sb-icon-star rating--null"></i>
                                        </label>

                                        <input class="form__rating" type="radio" name="rating" id="rating5"
                                               value="5"/>
                                        <label for="rating5" class="form__ratingLabel">
                                            <i class="icon sb-icon-star rating--null"></i>
                                        </label>
                                    </div>
                                </div>
                                <div class="form__set">
                                    <label class="form__label"
                                           for="input-name">{{trans('form.entry.customer-name')}}</label>
                                    <input type="text" name="firstname" value="{{auth()->user()->firstname ?? ''}}"
                                           id="input-name" class="form__control"/>
                                </div>
                                <div class="form__set">
                                    <label class="form__label"
                                           for="input-email">{{trans('form.entry.customer-email')}}</label>
                                    <input type="email" name="email" value="{{auth()->user()->email ?? ''}}"
                                           id="input-email" class="form__control"/>
                                </div>
                                <div class="form__set">
                                    <label class="form__label"
                                           for="input-review">{{trans('form.entry.review')}}</label>
                                    <textarea name="review" rows="9" id="input-review"
                                              class="form__control form__control--textarea"></textarea>
                                </div>
                                <div class="form__set">
                                    <input type="submit" id="button-review" class="btn btn--red form__btn"
                                           value="{{trans('form.button.send')}}"/>
                                </div>
                                <div class="form__result js-result"></div>
                            </form>
                        </div>

                    </div>
                </div>
                <div class="productInfo__toggle">
                    @if ($product->translate->description)
                        <div class="productInfoToggle" role="tablist">
                            <div class="productInfoToggle__title js-product-toggle-btn">{{trans('common.tab.description')}}</div>
                            <div class="productInfoToggle__content js-product-toggle-content html html--default">{!! $product->translate->description !!}</div>
                        </div>
                    @endif
                    @if ($to_attributes->count())
                        <div class="productInfoToggle" role="tablist">
                            <div class="productInfoToggle__title js-product-toggle-btn"
                                 data-scroll-target="tab_attribute_title"
                                 role="tab">{{trans('common.tab.attribute')}}</div>
                            <div class="productInfoToggle__content js-product-toggle-content">
                                <table class="attr__table">
                                    @foreach ($to_attributes as $attribute_group)
                                        <tr class="attr__row">
                                            <td class="attr__name attr__name--inTab">{{$attribute_group->attribute->translate->name}}
                                                :
                                            </td>
                                            <td class="attr__value">
                                                @foreach($attribute_group->values as $value)
                                                    {{$value->translate->value}}
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    @endif
                    <div class="productInfoToggle" role="tablist">
                        <div class="productInfoToggle__title js-product-toggle-btn"
                             data-scroll-target="tab_review_title" role="tab">{{trans('common.tab.review')}}</div>
                        <div class="productInfoToggle__content js-product-toggle-content" id="toggleReview"></div>
                    </div>
                </div>
                @section('content-bottom')
                    @include('common.content_bottom')
                @show
            </div>

        </div>

        <div class="productInfoRight">
            @include('components.advantages')
            <div class="js-stickyCard">
                @if ($product->available)
                    <div class="productInfoRight__smallCard">
                        <div class="productInfo__image productInfo__image--smallCard">
                            <img src="{{$product->imageInfo->thumb}}" class="js-load-smallCard"
                                 ref="sticky_card_image_content">
                        </div>
                        <div class="productInfo__title productInfo__title--smallCard">
                            {{$product->title}}
                        </div>
                        <div class="price price--inProduct">
                            @if ($product->special)
                                <span class="price__share price__share--inProduct price__share--smallCard">
                                    {{$product->specialFormat}}
                                </span>
                                <span class="price__default price__default--withShare price__default--smallCard">
                                    {{$product->priceFormat}}
                                </span>
                            @else
                                <span class="price__default price__default--noShare price__default--smallCard">
                                    {{$product->priceFormat}}
                                </span>
                            @endif
                        </div>
                        <div class="productInfo__buy productInfo__buy--smallCard">
                            <btn-add-to-cart :available="{{$product->available}}" :id="{{(int)$product->id}}"
                                             default-class="btn btn--primary productInfo__btn productInfo__btn--smallCard js-button-cart"
                                             v-on:trigger-show-cart="onShowCart"></btn-add-to-cart>
                        </div>
                    </div>
                    <div class="productInfo__fastOrder fastOrder">
                        <div class="fastOrder-l">
                            <input type="text"
                                   value="{{auth()->user()->telephone ?? ''}}"
                                   class="fastOrder__control js-fast-order-control fastOrder__control--smallCard js-telMask"
                                   placeholder="+38 (0__) ___-__-__">
                            <a href="javascript:void(0)"
                               class="btn btn--red fastOrder__btn fastOrder__btn--smallCard js-fast-order-btn"
                               @click='fastOrder({{$product->id}})'>
                                {{trans('catalog.button.add.fast_order')}}
                            </a>
                            <div class="form__result form__result--inProduct js-result"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

