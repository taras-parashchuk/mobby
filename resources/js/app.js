/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

'use strict';

require('./bootstrap');
require('./libraries/jquery.maskedinput.min');
require('./libraries/jquery.magnific-popup.min');
require('./libraries/jquery.zoom');
export let helpers = require('./common');
require('./libraries/jquery.owl.carousel');
import vSelect from 'vue-select'
import VueSlider from 'vue-slider-component'

require('./libraries/jquery.zoom');
import {VTooltip, VPopover, VClosePopover} from 'v-tooltip'
import MaskedInput from 'vue-masked-input'


import 'vue-select/dist/vue-select.css';


window.updateRating = helpers.updateRating;

window.Vue = require('vue');

window.eventBus = new Vue({});

// Set the components prop default to return our fresh components

vSelect.props.components.default = () => ({
    OpenIndicator: {
        render(createElement) {
            return createElement('span', {
                class: {
                    'icon': true,
                    'sb-icon-down-arrow': true,
                },
            })
        }
    },
});

Vue.component('v-select', vSelect);
Vue.component('v-slider', VueSlider);
Vue.directive('tooltip', VTooltip);
Vue.directive('close-popover', VClosePopover);
Vue.component('v-popover', VPopover);
Vue.component('masked-input', MaskedInput);


import store from './store.js'


export function copy(o) {
    if (o === null) return null;

    var output, v, key;
    output = Array.isArray(o) ? [] : {};
    for (key in o) {
        v = o[key];
        output[key] = (typeof v === "object") ? copy(v) : v;
    }
    return output;
}

/*
new VueW3CValid({
    el: '#wizorStore'
});
*/
new Vue({
    el: '#wizorStore',
    store,
    data: {
        routes: window.routes,
        trans: window.trans,
        theme: window.theme,
        catalogFilterDefault: {
            sort: 'sort_order.asc',
            limit: 14,
            page: 1
        },
        api_link: '',
        catalog_link: '',
        catalogFilter: {
            sort: null,
            limit: null,
            attributes: {},
            brand: null,
            price: null,
            page: null,
            category_id: 0
        },
        notApiParams: [],
        notCatalogParams: [],
        paginationInfo: {
            currentPage: '',
            countPages: ''
        },
        price_slider: {
            from: null,
            to: null
        },
        view: 'grid',
        products: [],
        searchCategories: [],
        loadedProducts: true,
        accountInfo: {},
        menuCategoriesGroups: [],
        locale: document.getElementsByTagName('html')[0].getAttribute('lang'),
        ops: {
            vuescroll: {
                mode: 'native',
                sizeStrategy: 'percent',
                detectResize: true
            },
            scrollPanel: {
                initialScrollY: false,
                initialScrollX: false,
                scrollingX: true,
                scrollingY: true,
                speed: 300,
                easing: undefined,
                verticalNativeBarPos: 'right'
            },
            rail: {
                background: '#e9e9eb',
                opacity: 1,
                size: '2px',
                specifyBorderRadius: false,
                gutterOfEnds: null,
                gutterOfSide: '2px',
                keepShow: false
            },
            bar: {
                showDelay: 10000000,
                onlyShowBarOnScroll: true,
                keepShow: true,
                background: '#f81a20',
                opacity: 1,
                hoverStyle: false,
                specifyBorderRadius: false,
                minSize: 0,
                size: '4px',
                disable: false
            }
        },
        searchCategoryIdForForm: 0,
        mounted: false,
        window_width: window.innerWidth
    },
    provide() {
        return {
            save: this.saveFormData,
        }
    },
    mounted(){
      this.mounted = true;
    },
    created() {

        let self = this;

        if (window.dataPage) {
            switch (window.dataPage.type) {
                case 'catalog':

                    this.catalogFilter.sort = window.dataPage.sort;

                    this.catalogFilter.limit = window.dataPage.limit;

                    if (window.dataPage.view) this.view = window.dataPage.view;

                    if (window.dataPage.page) this.catalogFilter.page = window.dataPage.page;

                    if (window.dataPage.category_id) {
                        this.catalogFilterDefault.category_id = window.dataPage.category_id;
                        this.catalogFilter.category_id = window.dataPage.category_id;
                    }

                    if (JSON.parse(window.dataPage.price).length === 2) {
                        this.catalogFilter.price = JSON.parse(window.dataPage.price);
                    }

                    if (window.dataPage.attributes) this.catalogFilter.attributes = JSON.parse(window.dataPage.attributes);

                    this.api_link = window.dataPage.api_link;

                    this.catalog_link = window.dataPage.catalog_link;

                    break;
                case 'search':

                    this.catalogFilter.sort = window.dataPage.sort;

                    this.catalogFilter.limit = window.dataPage.limit;

                    if (window.dataPage.category_id !== undefined) this.catalogFilter.category_id = window.dataPage.category_id;

                    this.notCatalogParams.push('category_id', 'phrase', 'with_description');


                    this.catalogFilter.with_description = window.dataPage.with_description;

                    this.catalogFilter.phrase = window.dataPage.phrase;

                    if (window.dataPage.view) this.view = window.dataPage.view;

                    this.catalog_link = window.dataPage.catalog_link;

                    this.api_link = window.dataPage.api_link;

                    if (window.dataPage.page) this.catalogFilter.page = window.dataPage.page;

                    if (window.dataPage.categories) this.searchCategories = window.dataPage.categories;

                    break;
                case 'product':
                    break;
            }
        }

        axios.get('/account/info').then(function (httpResponse) {
            self.accountInfo = httpResponse.data;

            let wishlistProducts = [];

            if (httpResponse.data.wishlist) {
                for (let product of httpResponse.data.wishlist) {
                    wishlistProducts.push(product.id);
                }
            }

            store.commit('addToProductsList', {type: 'wishlist', param: wishlistProducts});

        }).catch(function () {
        });

        store.dispatch('getProductsList', 'comparelist');

        $(window).resize(function () {
            if ($(window).width() < 1200) {
                self.view = 'grid';
            }
        });

    },
    components: {
        'list-slider-banners': require('./components/ListSliderBanners.vue').default,
        'search-field': require('./components/searchField.vue').default,
        'modal-cart': require('./components/cart.vue').default,
        'owl-slider': require('./components/owlSlider.vue').default,
        'menu-vertical': require('./components/menuVertical.vue').default,
        'menu-horizontal': require('./components/menuHorizontal.vue').default,
        'catalog-grid-product': require('./components/catalogGridProduct.vue').default,
        'catalog-list-product': require('./components/catalogListProduct.vue').default,
        'pagination': require('./components/pagination').default,
        'more-pages-loader': require('./components/morePagesLoader').default,
        'catalog-column-filter': require('./components/catalogColumnFilter').default,
        'btn-add-to-cart': require('./components/btnAddToCart').default,
        'app-account': require('./components/account').default,
        'checkout': require('./components/checkout').default,
        'compare-table': require('./components/compare').default,
        'variations': require('./components/variations').default,
        'product-images-slider': require('./components/ProductImagesSlider').default
    },
    computed: {
        params() {
            return this.paramsToString(this.catalogFilter, this.catalogFilterDefault);
        },
        url() {
            let url = '';

            if (this.params.length) {
                url = this.catalog_link + '/' + this.params + '/';
            } else {
                url = this.catalog_link;
            }

            if (Object(window.dataPage).hasOwnProperty('query_params')) {
                url += window.dataPage.query_params;
            }

            return url;
        },

        hasWishlistProduct() {
            return (id) => {
                return store.getters.hasWishlistProduct(id);
            }
        },
    },
    mounted(){
        window.addEventListener('resize', () => {
            this.window_width = window.innerWidth;
        });
        this.window_width = window.innerWidth;
    },
    methods: {
        setView(type) {

            let self = this;

            // if ($(window).width() < 1200) {
            //     axios.post('/set-view-type/grid')
            //     .then(function (response) {
            //         if (response.status === 200) {
            //             self.view = 'grid';
            //         }
            //     });
            // } else {

            //     axios.post('/set-view-type/' + type)
            //     .then(function (response) {
            //         if (response.status === 200) {
            //             self.view = type;
            //         }
            //     });
            // }

            axios.post('/set-view-type/' + type)
                .then(function (response) {
                    if (response.status === 200) {
                        self.view = type;
                    }
                });
        },

        loadCatalogProducts() {

            let self = this;

            this.loadedProducts = false;

            if (this.api_link) {

                $.get(this.api_link, self.catalogFilter, function(responseBody){

                    let productsInfoContainer;

                    productsInfoContainer = responseBody.products;

                    if (window.dataPage.type === 'catalog') {
                        self.$set(self, 'price_slider', responseBody.price_slider);
                    }

                    self.$set(self, 'products', productsInfoContainer.data);

                    self.$set(self.paginationInfo, 'currentPage', productsInfoContainer.current_page);
                    self.$set(self.paginationInfo, 'countPages', productsInfoContainer.last_page);

                    helpers.change_pos_mob('.js-products-sort', '.js-products-filter-container', '.js-m-container-for-sort', 971);

                    self.loadedProducts = true;
                });

            }

        },

        makeLinkWithNewParams(params) {
            let newPageParams = Object.assign({}, this.catalogFilter);

            for (let param_name in params) {
                newPageParams[param_name] = params[param_name];
            }

            let stringParams = this.paramsToString(newPageParams, this.catalogFilterDefault);

            if (stringParams.length) {
                return this.catalog_link + '/' + stringParams + '/';
            } else {
                return this.catalog_link;
            }
        },

        paramsToString(params, exclude) {
            let result_params = [];

            for (let f_key in params) {

                if (this.notCatalogParams.length && this.notCatalogParams.indexOf(f_key) !== -1) continue;

                if (typeof params[f_key] != 'object') {
                    let value = params[f_key];

                    if (value && value != exclude[f_key]) {
                        result_params.push(f_key + '=' + value);
                    }
                } else {
                    if (f_key === 'price' && params[f_key] != exclude[f_key]) {
                        result_params.push(f_key + '=' + params[f_key].join(','));
                    } else {
                        for (let attr_name in params[f_key]) {

                            let values = [];

                            if (params[f_key][attr_name].multiply) {
                                for (let value of params[f_key][attr_name].values) {
                                    values.push(value);
                                }
                            } else {
                                values.push(params[f_key][attr_name].value);
                            }

                            result_params.push(attr_name + '=' + values.join(','));
                        }
                    }

                }
            }

            return result_params.join(';');
        },

        removeAttributeParam(params) {

            let attributes = this.catalogFilter.attributes;

            for (let attribute_slug in attributes) {
                if (attribute_slug === params.attr_slug) {
                    for (let index in attributes[attribute_slug]['values']) {
                        if (attributes[attribute_slug]['values'][index] === params.value_slug) {
                            attributes[attribute_slug].values.splice(index, 1);
                        }
                    }
                    if (!attributes[attribute_slug].values.length) {
                        delete this.catalogFilter.attributes[attribute_slug];
                    }
                }
            }
        },

        setAttributes(params, page) {
            this.catalogFilter.attributes = params || {};
            this.catalogFilter.page = page;
        },

        setPrices(prices) {
            this.$set(this.catalogFilter, 'price', prices || []);
        },

        onShowCart() {
            this.$refs.cart.show();
        },

        showLoginForm() {
            $('.js-open-login-form').click();
        },

        addProductToWishlist(id) {
            store.dispatch('setWishlistProduct', id);
        },

        addProductToComparelist(id) {
            store.dispatch('setComparelistProduct', id);
        },

        saveFormData() {
            console.log('we save form');
            console.log(event.target);
        },
        fastOrder(id) {

            let product_data = {
                id: id,
                telephone: ''
            };

            let $target = $(event.target);

            product_data.telephone = $target.prev().val();

            axios.post('/checkout/confirm-fast-order', product_data)
                .then((httpResponse) => {
                    $target.next('.js-result').html(httpResponse.data.success);
                }).catch(error => {

                var errors = error.response.data;

                $target.next('.js-result').html(helpers.getFirstError(errors.errors));

            }).finally(() => {

                $target.prev().val('');

                setTimeout(() => {

                    $target.next().html('');

                }, 3000);
            });
        },
        addCustomScroll() {
            let $target = $(event.target);
            $target.find('.vs__dropdown-menu').mCustomScrollbar();
        }
    },
    watch: {
        url(value) {
            history.pushState(null, null, value);

            this.loadCatalogProducts();
        },
        window_width(value){
            this.window_width = value;
        }
    }
});
