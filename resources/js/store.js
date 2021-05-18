require('./bootstrap');
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

import {helpers} from './app'

export default new Vuex.Store({
    state: {
        cart: {
            products: [],
            subtotals: [],
            total: '',
            modules: [],
            conditionals: {}
        },
        wishlist: {
            products: []
        },
        comparelist: {
            products: []
        }
    },
    getters: {
        getCartProducts(state) {
            return state.cart.products;
        },
        getConditionals(state) {
            return state.cart.conditionals;
        },
        getTotal(state) {
            return state.cart.total;
        },
        hasCartProduct: (state) => (id) => {
            return state.cart.products.filter(function (product) {
                return (product.id === id);
            }).length;
        },
        hasProductsListProduct: (state) => (list_type, id) => {
            if (state[list_type].products.indexOf(id) !== -1) {
                return true;
            } else {
                return false;
            }
        },
        countProductsList: (state) => (list_type) => {
            return state[list_type].products.length;
        }
    },
    mutations: {
        setCartInfo(state, payload) {
            state.cart = payload;
        },
        setTotalSum(state, sum) {
            state.cart.total = sum;
        },
        updateCartProductInfo(state, payload) {
            let i = payload.i;

            state.cart.products[i].qty = payload.qty;
            state.cart.products[i].price = payload.price;
            state.cart.products[i].special = payload.special;

        },
        removeCartProduct(state, i) {
            state.cart.products.splice(i, 1);
        },
        setTotal(state, total) {
            state.cart.total = total;
        },
        setConditionals(state, conditionals){
            state.cart.conditionals = conditionals;
        },
        addToProductsList(state, payload) {
            switch (payload.type) {
                case 'wishlist':
                    break;
                case 'comparelist':
                    break;
                default:
                    return;
            }

            if (Array.isArray(payload.param)) {
                state[payload.type] = {
                    products: payload.param
                }
            } else {
                state[payload.type].products = payload.items;
            }
        },

        removeFromProductsList(state, payload) {
            switch (payload.type) {
                case 'wishlist':
                    break;
                case 'comparelist':
                    break;
                default:
                    return;
            }

            let pos = state[payload.type].products.indexOf(payload.param);

            if (pos !== -1) {
                state[payload.type].products = payload.items;
            }
        }
    },
    actions: {
        getCartInfo(context, payload) {
            axios.get('/cart')
                .then(function (response) {

                    context.commit('setCartInfo', response.data);

                    if (payload && typeof payload.callback !== undefined) payload.callback();
                });
        },

        getTotal(context) {

            $('.js-cart').find('.js-loading').addClass('loading--show');

            axios.get('/cart/total')
                .then(function (response) {
                    context.commit('setTotal', response.data);
                    $('.js-cart').find('.js-loading').removeClass('loading--show');
                })
        },

        removeCartProduct(context, i) {

            $('.js-cart').find('.js-loading').addClass('loading--show');

            axios.delete('/cart/' + context.state.cart.products[i].id)
                .then(function () {
                    context.commit('removeCartProduct', i);
                })
                .then(function () {
                    $('.js-cart').find('.js-loading').removeClass('loading--show');
                    context.dispatch('getTotal');
                });
        },

        removeFromProductsList(context, payload) {
            return new Promise(function (resolve) {
                axios.delete(`/${payload.type}/` + payload.param)
                    .then(function (httpResponse) {

                        payload.items = httpResponse.data;

                        context.commit('removeFromProductsList', payload);
                        resolve();
                    });

            });
        },

        addToProductsList(context, payload) {
            axios.post(`/${payload.type}/` + payload.param)
                .then(function (httpResponse) {

                    payload.items = httpResponse.data;

                    context.commit('addToProductsList', payload);
                });
        },

        getProductsList(context, list_type) {
            axios.get(`/${list_type}/`)
                .then(function (httpResponse) {
                    context.commit('addToProductsList', {type: list_type, param: httpResponse.data});
                });
        },

        addToCart(context, product_id) {

            $('html, body').animate({scrollTop: 0}, 100);

            helpers.togglePreloader('cartPreloader', '',true);

            axios.post('/cart', {
                id: product_id
            }).then((httpResponse) => {
                if (httpResponse.data['redirect']) {
                    location = httpResponse.data['redirect'];
                }
                if (httpResponse.data['success']) {

                    context.dispatch('getCartInfo', {
                        callback: function () {
                            helpers.togglePreloader('cartPreloader', '', false);

                            $('.js-open-modal-cart').first().trigger('click');
                        }
                    });

                }
            }).catch(() => {
                helpers.togglePreloader('cartPreloader', '', false);
            });
        },

        changeCartProductQuantity(context, payload) {
            let qty = payload.qty;
            let i = payload.i;
            let newQty;

            if (qty >= 1) {
                newQty = qty;
            } else {
                newQty = false;
            }

            let product = context.state.cart.products[i];

            if (newQty) {

                $('.js-cart').find('.js-loading').addClass('loading--show');

                axios.put('/cart/' + product.id, {
                    qty: newQty
                })
                    .then((httpResponse) => {
                        if (httpResponse.data['error']) {
                            helpers.getNotification(httpResponse.data['error']);
                        } else {
                            context.commit('updateCartProductInfo', {
                                i: i,
                                qty: newQty,
                                price: httpResponse.data.price,
                                special: httpResponse.data.special,
                            });
                            context.commit('setTotalSum', httpResponse.data['total']);
                            context.commit('setConditionals', httpResponse.data['conditionals']);
                        }
                    })
                    .catch()
                    .then(() => {
                        $('.js-cart').find('.js-loading').removeClass('loading--show');
                    });
            }
        }
    }

});