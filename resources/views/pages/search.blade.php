@extends('layouts.standart')

@section('appData')

    @parent

    <script>

        document.addEventListener("DOMContentLoaded", function () {
            // $(document)
            //     .on('open', '.vs__dropdown-menu', function () {
            //         console.log('ddd');
            //         $(this).mCustomScrollbar();
            //     })


        });
        window.dataPage = {
            type: 'search',
            api_link: '{{$link_json_get_search_products}}',
            page: parseInt('{{$fixParams['page']['value'] ?? 1}}'),
            catalog_link: '{{  route('search') }}',
            phrase: '{{$search_phrase}}',
            category_id: parseInt('{{$selected_category}}'),
            categories: {!! $search_categories !!},
            with_description: '{{$include_descriptions}}',
            sort: '{{ $fixParams['sort']['value'] ?? 'sort_order.asc'}}',
            limit: parseInt('{{$fixParams['limit']['value'] ?? 14}}'),
            view: '{{$view_type}}',
            query_params: '{!! $query !!}'
        };

    </script>

@endsection

@section('breadcrumbs')
    {{ Breadcrumbs::render(Route::currentRouteName()) }}
@endsection

@section('content')

    <div class="search">
        <div class="search__top">
            <h1 class="pageTitle">
                @if($search_phrase)
                    {{trans('pages.search.heading.with_results', ['search_phrase'=>$search_phrase])}}
                @else
                    {{trans('pages.search.heading.single')}}
                @endif
            </h1>
            <div class="l-flex l-flex-top">
                @section('left-column') @endsection
                <div class="search__content" id="content">
                    <div class="l-flex">
                        <form method="get" action="" class="form form--oneLine search__form">
                            <div class="form__set form__set--inRow search__row">
                                <input class="form__control form__control--sPrimary" name="phrase"
                                       value="{{$search_phrase}}" placeholder="{{trans('common.text.search')}}"/>
                            </div>
                            <div class="form__set form__set--inRow search__row">
                                <v-select
                                        v-model="catalogFilter.category_id"
                                        label="name"
                                        :reduce="category => category.id"
                                        :options="searchCategories"
                                        :searchable='false'
                                        :clearable="false"
                                        class="select select--special js-select-scroll">
                                </v-select>
                                <input type="hidden" :value="catalogFilter.category_id" name="category_id">
                            </div>
                            <div class="form__set form__set--inRow search__row">
                                <input id="button-search" class="btn btn--primary search__btn" type="submit"
                                       value="{{trans('common.button.search')}}">
                                <div class="radioNew">
                                    <input type="checkbox"
                                           name="with_description"
                                           value="1"
                                           id="description"
                                           class="radioNew__value"
                                           @if($include_descriptions)
                                           checked
                                            @endif/>
                                    <label for="description"
                                           class="radioNew__label">{{trans('pages.search.filter.include_descriptions')}}</label>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="l-pos-r">
                        <template v-if="loadedProducts">

                            <template v-if="products.length">
                                <div class="searchInfo">
                                    <div class="l-flex js-products-filter-container">
                                        <div class="js-products-sort products__sort contentDrop">
                                    <span class="contentDrop__label js-change-ln-text"
                                          data-xs-text="{{trans('catalog.components.sort.heading_xs')}}">{{trans('catalog.components.sort.heading')}}</span>

                                            <v-select v-model="catalogFilter.sort"
                                                      label="label"
                                                      :reduce="sort => sort.value"
                                                      :options="[
                                                {label: '{{trans('catalog.components.sort.sort_order-asc')}}', value: 'sort_order.asc'},
                                                {label: '{{trans('catalog.components.sort.price-asc')}}', value: 'price.asc'},
                                                {label: '{{trans('catalog.components.sort.price-desc')}}', value: 'price.desc'}
                                                ]"
                                                      :searchable='false'
                                                      :clearable="false"
                                                      @click="addCustomScroll"
                                                      class="select select--default js-select-scroll">
                                            </v-select>

                                        </div>
                                        <div class="products__limit contentDrop">
                                            <span class="contentDrop__label">{{trans('catalog.components.limit.heading')}}</span>

                                            <v-select
                                                    v-model="catalogFilter.limit"
                                                    :options="[14,24,44]"
                                                    :searchable='false'
                                                    :clearable="false"
                                                    class="select select--default select--mini">
                                            </v-select>
                                            <!-- <select v-model="catalogFilter.limit"
                                                    class="contentDrop__menu js-style-select">
                                                <option class="contentDrop__link"
                                                        :class="{'contentDrop__link--active': (catalogFilter.limit == 14)}"
                                                        value="14">14
                                                </option>
                                                <option class="contentDrop__link"
                                                        :class="{'contentDrop__link--active': (catalogFilter.limit == 24)}"
                                                        value="24">24
                                                </option>
                                                <option class="contentDrop__link"
                                                        :class="{'contentDrop__link--active': (catalogFilter.limit == 34)}"
                                                        value="44">44
                                                </option>
                                            </select> -->
                                        </div>
                                    </div>
                                    <div class="products__view contentView">
                                        <span class="contentView__label">{{trans('catalog.components.view.heading')}}</span>
                                        <a href="javascript:void(0)"
                                           @click.prevent="setView('grid')"
                                           class="contentView__link js-view-link"
                                           :class="{'contentView__link--active' : (view == 'grid')}"
                                           title="{{trans('catalog.components.view.grid')}}">
                                            <i class="icon sb-icon-grid"></i>
                                        </a>
                                        <a href="javascript:void(0)" data-page="category"
                                           @click.prevent="setView('list')"
                                           class="contentView__link js-view-link"
                                           :class="{'contentView__link--active' : (view == 'list')}"
                                           title="{{trans('catalog.components.view.list')}}">
                                            <i class="icon sb-icon-list"></i>
                                        </a>
                                    </div>
                                    <div class="products js-view">

                                        <template v-if="view === 'grid'">
                                            <catalog-grid-product :product="product" v-for="product in products"
                                                                  :key="product.id"></catalog-grid-product>
                                            <more-pages-loader view="grid" :api-link="api_link" page-container="products"
                                                               :active-page="paginationInfo.currentPage"></more-pages-loader>
                                        </template>
                                        <template v-else-if="view === 'list'">
                                            <catalog-list-product :product="product" v-for="product in products"
                                                                  :key="product.id"></catalog-list-product>
                                            <more-pages-loader view="list" :api-link="api_link" page-container="products"
                                                               :active-page="paginationInfo.currentPage"></more-pages-loader>
                                        </template>

                                    </div>
                                    <pagination :active-page="paginationInfo.currentPage"
                                                :count-pages="paginationInfo.countPages"></pagination>

                                </div>
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

                </div>
            </div>
        </div>
    </div>

@endsection
