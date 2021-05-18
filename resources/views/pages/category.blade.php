@extends('layouts.standart')

@section('appData')

    @parent

    <script>
        window.dataPage = {
            type: 'catalog',
            sort: '{{ $fixParams['sort']['value'] ?? 'sort_order.asc'}}',
            limit: parseInt('{{$fixParams['limit']['value'] ?? 14}}'),
            category_id: parseInt('{{$category->id}}'),
            page: parseInt('{{$fixParams['page']['value'] ?? 1}}'),
            <?php if(count($dynamicParams)){ ?>
            attributes: '@json($dynamicParams)',
            <?php } ?>
            price: '@json($fixParams['price']['values'] ?? [])',
            catalog_link: '{{$catalog_link}}',
            api_link: '{{$api_link}}',
            view: '{{$view_type}}',
            query_params: '{!! $query !!}'
        };

        window.trans['text_short_attr'] = "{{trans('catalog.text.short_attr')}}";
        window.trans['filter.heading'] = "{{trans('catalog.components.filter.heading')}}";
        window.trans['filter.checked.heading'] = "{{trans('catalog.components.filter.checked.heading')}}";
        window.trans['filter.price.heading'] = "{{trans('catalog.components.filter.price.heading')}}";
        window.trans['filter.price.from'] = "{{trans('catalog.components.filter.price.from')}}";
        window.trans['filter.price.to'] = "{{trans('catalog.components.filter.price.to')}}";

        document.addEventListener("DOMContentLoaded", function () {

            if ($('.categoryInfo__description').height() > 349) {
                $('.categoryInfo__description').mCustomScrollbar();
            }
        });

    </script>

@endsection

@section('breadcrumbs')
    @if ($category->translate->description)
        <div class="l-flex l-flex-order">
            <div class="categoryInfo__description html html--default">
                {!! $category->translate->description !!}
            </div>
        </div>
    @endif
    {{ Breadcrumbs::render(Route::currentRouteName(), $category) }}
@endsection

@section('content')

    <div class="categoryInfo__top">
        <h1 class="categoryInfo__title">{{$category->name}}</h1>
    </div>
    <div class="l-flex l-flex-top l-pos-r">
        <template v-if="loadedProducts">
            @section('left-column')
                @include('common.left_column')
            @show
            <div class="categoryInfo__content">

                <template v-if="products.length">
                    <div class="l-flex js-products-filter-container">
                        <div class="js-products-sort products__sort contentDrop">
                        <span class="contentDrop__label js-change-ln-text"
                              data-xs-text="{{trans('catalog.components.sort.heading_xs')}}">{{trans('catalog.components.sort.heading')}}</span>
                            <template>
                                <v-select
                                        v-model="catalogFilter.sort"
                                        label="label"
                                        :reduce="sort => sort.value"
                                        :options="[
                                    {label: '{{trans('catalog.components.sort.sort_order-asc')}}', value: 'sort_order.asc'},
                                    {label: '{{trans('catalog.components.sort.price-asc')}}', value: 'price.asc'},
                                    {label: '{{trans('catalog.components.sort.price-desc')}}', value: 'price.desc'}
                                ]"
                                        :searchable='false'
                                        :clearable="false"
                                        class="select select--default">
                                </v-select>
                            </template>

                        </div>
                        <div class="products__limit contentDrop">
                            <span class="contentDrop__label">{{trans('catalog.components.limit.heading')}}</span>

                            <v-select
                                    v-model="catalogFilter.limit"
                                    :options="[14,24,44]"
                                    :searchable='false'
                                    :clearable="false"
                                    class="select select--default select--mini"></v-select>
                        </div>
                    </div>
                    <div class="products js-view">

                        <catalog-grid-product :product="product" v-for="product in products"
                                              :key="product.id"></catalog-grid-product>
                        <more-pages-loader view="grid" :api-link="api_link" page-container="products"
                                           :active-page="paginationInfo.currentPage"></more-pages-loader>

                    </div>

                    <pagination :active-page="paginationInfo.currentPage"
                                :count-pages="paginationInfo.countPages"></pagination>
                </template>
                <template v-else>
                    <div class="empty">
                        <div class="empty__text">
                            {{trans('catalog.text.empty')}}
                        </div>
                        <div class="empty__btns">
                            <a href="{{route('home')}}"
                               class="btn btn--primary empty__btn">{{trans('common.button.home')}}</a>
                        </div>
                    </div>
                </template>

            </div>
        </template>
        <template v-else>
            <div class="loading js-loading loading--show">
                <div class="loading__wrap"><span class="icon icon--spin sb-icon-loading"><span
                                class="path1"></span><span
                                class="path2"></span><span class="path3"></span><span
                                class="path4"></span><span class="path5"></span><span class="path6"></span><span
                                class="path7"></span><span class="path8"></span><span class="path9"></span><span
                                class="path10"></span><span class="path11"></span><span
                                class="path12"></span></span>
                </div>
            </div>
        </template>
    </div>
@endsection
