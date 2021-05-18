<template>
    <div style="display: none;">
        <div class="cart cart--popup js-cart" id="cart_modal_content">
            <div class="cart__header">
                {{$parent.trans['cart.text.heading']}}
            </div>

            <div class="js-cart-content">
                <template v-if="products.length">
                    <div class="cart__top">
                        <div class="cart__product cartProduct" v-for="(product, i) in products">
                            <div class="cartProduct__img cartProduct__img--popup">
                                <a :href="product.href">
                                    <img :src="product.thumb"
                                         :alt="product.name"
                                         :title="product.name"
                                         class="img-responsive"/>
                                </a>
                            </div>
                            <div class="cart__nameWrapper cart__nameWrapper--popup">
                                <div class="cart__col">
                                    <a :href="product.href"
                                       class="cartProduct__name">{{product.name}}</a>

                                    <div class="cartProduct__specification" v-for="(specification_value, specification_name) in product.specification">
                                        {{specification_name}} - {{specification_value}}
                                    </div>

                                    <div class="cartProduct__price cartProduct__price--mobile price">
                                        <template v-if="product.special">
                                            <span class="price__default price__default--inCart price__default--withShare">{{product.price}}</span>
                                            <span class="price__share">{{ product.special}}</span>
                                        </template>
                                        <template v-else>
                                            <span class="price__default">{{  product.price}}</span>
                                        </template>
                                    </div>

                                </div>
                                <div class="cart__col">
                                    <div class="cartProduct__quantity">

                                        <div class="cartProduct__quantityHeader">
                                            <template v-if="product.price_postprefix">
                                                {{ $parent.trans['common.text.quantity'] }} {{
                                                product.price_postprefix}}
                                            </template>
                                            <template v-else>
                                                {{ $parent.trans['common.text.quantity'] }}
                                            </template>
                                        </div>

                                        <div class="cartProduct__quantityInner">
                                            <input type="hidden" name="product_id"
                                                   v-model="product.id"/>
                                            <button class="cartProduct__quantityBtn"
                                                    @click="$store.dispatch('changeCartProductQuantity', {i: i, qty: product.qty - 1})">
                                                -
                                            </button>
                                            <input name="product_quantity"
                                                   class="cartProduct__quantityField js-cart-quantity-field js-quantity"
                                                   type="text"
                                                   v-model.number="product.qty"
                                                   v-on:keypress="isNumber($event)"
                                                   @input="updateDebounce(product.qty, i)">
                                            <button class="cartProduct__quantityBtn"
                                                    @click="$store.dispatch('changeCartProductQuantity', {i: i, qty: product.qty + 1})">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cartProduct__end">

                                <div class="cartProduct__price price">
                                    <template v-if="product.special">
                                        <span class="price__default price__default--inCart price__default--withShare">{{product.price}}</span>
                                        <span class="price__share">{{ product.special}}</span>
                                    </template>
                                    <template v-else>
                                        <span class="price__default">{{ product.price}}</span>
                                    </template>
                                </div>

                                <span class="cartProduct__remove icon sb-icon-cancel-round"
                                      @click="$store.dispatch('removeCartProduct',i)"></span>
                            </div>

                        </div>
                    </div>

                    <div class="cart__bottom cart__bottom--popup">

                        <div class="l-cart__left">
                            <button onclick="$.magnificPopup.close();"
                                    class="cart__return btn-back"><i
                                    class="icon sb-icon-down-arrow"></i>{{ $parent.trans['cart.button.return']}}
                            </button>
                        </div>
                        <div class="l-cart__right">

                            <div class="c-cart__subtotal" v-if="Object.keys(conditionals).length">
                                <template v-for="conditional in conditionals">
                                    <template v-for="conditionalSingle in conditional">
                                        <div v-html="conditionalSingle.attributes.template"></div>
                                    </template>
                                </template>
                            </div>

                            <div class="c-cart__total">
                                <div class="cart__total cart__total--popup">
                                    <span>{{ $parent.trans['cart.text.total']}}</span>
                                    <strong>{{ total }}</strong>
                                </div>
                                <div class="cart__checkout cart__checkout--popup">
                                    <a :href="$parent.routes.checkout"
                                       class="btn btn--primary cart__primary">{{
                                        $parent.trans['cart.button.checkout']}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="loading js-loading">
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
                <template v-else>
                    <div class="service">
                        <div class="service__content">
                            <div class="service__inner">
                                <div class="service__title service__title--small">
                                    {{ $parent.trans['cart.text.empty'] }}
                                </div>
                                <div class="service__text service__text--small">
                                    <span>{{ $parent.trans['cart.text.empty_desc'] }}</span>
                                    <div class="service__icon service__icon--cart service__icon--small"><i
                                            class="icon sb-icon-share"></i></div>
                                </div>
                                <a href="javascript:void(0)" onclick="$.magnificPopup.close();"
                                   class="btn btn--primary service__btn service__btn--small">
                                    {{$parent.trans['cart.button.continue']}}
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>

    import store from './../store.js'

    export default {
        name: "Cart",
        store,
        data() {
            return {}
        },
        created() {
            var Cart = this;

            window.eventBus.$on('add-to-cart', function (product_id) {
                Cart.add(product_id)
            });

            this.$store.dispatch('getCartInfo');
        },
        computed: {
            products() {
                return this.$store.getters.getCartProducts;
            },
            conditionals() {
                return this.$store.getters.getConditionals;
            },
            total() {
                return this.$store.getters.getTotal;
            },
            modules() {
                return [];
            },
            hasProduct() {
                return (id) => this.$store.getters.hasCartProduct(id);
            }
        },
        methods: {
            isNumber(evt) {
                evt = (evt) ? evt : window.event;
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                    evt.preventDefault();
                } else {
                    return true;
                }
            },
            updateDebounce: _.debounce(function (qty, i) {
                this.$store.dispatch('changeCartProductQuantity', {i: i, qty: qty});
            }, 1000),
            show() {
                $('.js-open-modal-cart').first().trigger('click');
            }
        }
    }
</script>

<style scoped>

</style>