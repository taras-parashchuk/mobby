<template>
    <div class="checkout__container js-checkout-container">
        <div class="checkout__column" id="js-checkout">
            <div v-if="readyTrans">
                <div class="js-section-contacts-container checkout__section">
                    <div v-bind:class="'checkout__status js-checkout-status checkout__status--'+getStatusClass('contact')">
                        1
                    </div>
                    <div v-bind:class="'checkout__inner js-section-contacts checkout__inner--'+getStatusClass('contact')">
                        <section-contacts ref="sectionContacts"
                                          v-on:change-section-status="checkSectionsStatus"
                                          :trans="trans.sections.contact"
                                          :account-info="accountInfo"></section-contacts>
                    </div>
                </div>

                <div class="js-section-shipping-container checkout__section">
                    <div v-bind:class="'checkout__status js-checkout-status checkout__status--'+getStatusClass('delivery')">
                        2
                    </div>
                    <div v-bind:class="'js-section-shipping checkout__inner checkout__inner--'+getStatusClass('delivery')">
                        <section-delivery ref="sectionDelivery"
                                          v-on:change-section-status="checkSectionsStatus"
                                          :trans="trans.sections.shipping"></section-delivery>
                    </div>
                </div>

                <div class="js-section-payment-container checkout__section checkout__section--lastItem">
                    <div v-bind:class="'checkout__status js-checkout-status checkout__status--'+getStatusClass('payment')">
                        3
                    </div>
                    <div v-bind:class="'js-section-payment checkout__inner checkout__inner--'+getStatusClass('payment')">
                        <section-payment ref="sectionPayment"
                                         v-on:change-section-status="checkSectionsStatus"
                                         :trans="trans.sections.payment"
                                         :statuses="sections"></section-payment>
                    </div>
                </div>
                <div class="loading loading--noBg js-loading">
                    <div class="loading__wrap"><span class="icon icon--spin sb-icon-loading"><span
                            class="path1"></span><span class="path2"></span><span class="path3"></span><span
                            class="path4"></span><span class="path5"></span><span class="path6"></span><span
                            class="path7"></span><span class="path8"></span><span class="path9"></span><span
                            class="path10"></span><span class="path11"></span><span
                            class="path12"></span></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="checkout__column">
            <div class="checkout__cart js-cart">
                <div class="checkout__header">
                    <div class="l-flex">
                        <div class="checkout__subtitle">
                            {{$root.trans['cart.text.heading']}}
                        </div>
                    </div>
                </div>
                <div class="checkout__content js-section-cart">
                    <div class="cart__top">
                        <div v-for="(product,i) in $store.getters.getCartProducts" class="cart__product cartProduct">
                            <div class="cartProduct__img">
                                <a :href="product.href">
                                    <img :src="product.thumb"
                                         :alt="product.name"
                                         :title="product.name"
                                         class="img-responsive"/>
                                </a>
                            </div>

                            <div class="cart__nameWrapper">
                                <div class="cart__col">
                                    <a :href="product.href"
                                       class="cartProduct__name cartProduct__name--inCheckout">
                                        {{product.name}}
                                    </a>
                                    <div class="cartProduct__specification" v-for="(specification_value, specification_name) in product.specification">
                                        {{specification_name}} - {{specification_value}}
                                    </div>
                                    <div class="cartProduct__price cartProduct__price--mobile cartProduct__price--inCheckout price">
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
                                    <div class="cartProduct__quantity cartProduct__quantity--inCheckout">
                                        <div class="cartProduct__quantityHeader">
                                            <template v-if="product.price_postprefix">
                                                {{ $parent.trans['common.text.quantity'] }} {{
                                                product.price_postprefix}}
                                            </template>
                                            <template v-else>
                                                {{ $parent.trans['common.text.quantity'] }}
                                            </template>
                                            <a href="javascript:void(0)" v-if="!product.stock"
                                               class="cartProduct__quantityNotification js-cart-quantity-popover-trigger"><i
                                                    class="icon sb-icon-information"></i></a>
                                        </div>
                                        <div v-if="!product.stock" class="js-cart-quantity-popover-content"
                                             style="display: none">
                                            <div class="popover">
                                                <div class="popover__text"><?php echo $product['about_stock']['heading']; ?></div>
                                            </div>
                                        </div>
                                        <div class="cartProduct__quantityInner">
                                            <input type="hidden" name="product_id"
                                                   v-model="product.id"/>
                                            <button class="cartProduct__quantityBtn"
                                                    @click="$store.dispatch('changeCartProductQuantity', {i: i, qty: product.qty-1})">
                                                -
                                            </button>
                                            <input name="product_quantity"
                                                   class="cartProduct__quantityField js-cart-quantity-field js-quantity"
                                                   type="text"
                                                   v-model="product.qty"
                                                   v-on:keypress="isNumber($event)"
                                                   @input="updateDebounce(product.qty, i)">
                                            <button class="cartProduct__quantityBtn"
                                                    @click="$store.dispatch('changeCartProductQuantity', {i: i, qty: product.qty+1})">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cartProduct__end">
                                <div class="cartProduct__price cartProduct__price--inCheckout price">
                                    <template v-if="product.special">
                                        <span class="price__default price__default--inCart price__default--withShare">{{product.price}}</span>
                                        <span class="price__share">{{ product.special}}</span>
                                    </template>
                                    <template v-else>
                                        <span class="price__default">{{ product.price}}</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cart__bottom cart__bottom--inCheckout">
                        <div class="l-cart__right">
                            <div class="c-cart__subtotal" v-if="Object.keys($store.getters.getConditionals).length">
                                <template v-for="conditional in $store.getters.getConditionals">
                                    <template v-for="conditionalSingle in conditional">
                                        <div v-html="conditionalSingle.attributes.template"></div>
                                    </template>
                                </template>
                            </div>

                            <div class="c-cart__total">
                                <div class="cart__total cart__total--popup">
                                    <span>{{ $parent.trans['cart.text.total']}}</span>
                                    <strong>{{ $store.getters.getTotal }}</strong>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="loading loading--noBg js-loading">
                    <div class="loading__wrap">
                            <span class="icon icon--spin sb-icon-loading">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                    class="path4"></span><span class="path5"></span><span class="path6"></span><span
                                    class="path7"></span><span class="path8"></span><span class="path9"></span><span
                                    class="path10"></span><span class="path11"></span><span class="path12"></span>
                            </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import store from './../store'

    export default {
        name: "checkout",
        props: ['accountInfo'],
        store,
        data() {
            return {
                sections: {
                    contact: {
                        status: 0,
                    },
                    delivery: {
                        status: -1,
                    },
                    payment: {
                        status: -1,
                    }
                },
                readyForLine: false,
                trans: {}
            }
        },
        created() {
            this.toggleLoader(1);

            let self = this;

            axios.get(window.routePrefix+'/checkout/translation')
                .then((httpResponse) => {
                    self.trans = httpResponse.data;
                });
        },
        mounted() {
            this.toggleLoader(0);
        },
        components: {
            'section-contacts': require('./checkoutInner/section-contacts').default,
            'section-delivery': require('./checkoutInner/section-delivery').default,
            'section-payment': require('./checkoutInner/section-payment').default,
        },
        methods: {
            getStatusClass: function (checkout__section) {
                status = this.sections[checkout__section].status;

                var status_class = '';

                switch (status) {
                    case '-1':
                        status_class = 'inactive';
                        break;
                    case '0':
                        status_class = 'proccess';
                        break;
                    case '1':
                        status_class = 'completed';
                        break;
                }

                return status_class;
            },
            checkSectionsStatus: function (section_name, status) {
                console.log('start');
                let self = this;
                switch (section_name) {
                    case 'contact':
                        if (status) {
                            console.log('contact');
                            this.sections.contact.status = 0;
                            this.sections.delivery.status = -1;
                            this.sections.payment.status = -1;

                            $.ajax({
                                url: '/checkout/save/user',
                                method: 'post',
                                data: {
                                    customer: self.$refs.sectionContacts.customer
                                },
                                dataType: 'json',
                                beforeSend() {
                                    self.toggleLoader(1);
                                },
                            }).done(function (json) {
                                if (json.success) {
                                    self.sections.contact.status = 1;

                                    if (self.$refs.sectionDelivery.isValidStep) {
                                        self.sections.delivery.status = 1;
                                        if (self.$refs.sectionPayment.isValidStep) {
                                            self.sections.payment.status = 1;
                                        } else {
                                            self.sections.payment.status = 0;
                                        }
                                    } else {
                                        self.sections.delivery.status = 0;
                                        self.sections.payment.status = -1;
                                    }
                                }
                            }).always(function () {
                                self.toggleLoader(0);
                            });
                        } else {
                            //console.log('contact');
                            this.sections.contact.status = 0;
                            this.sections.delivery.status = -1;
                            this.sections.payment.status = -1;
                        }
                        break;
                    case 'delivery':
                        if (status) {
                            console.log('delivery');
                            this.sections.contact.status = 1;
                            this.sections.delivery.status = 0;
                            this.sections.payment.status = -1;

                            var shipping_data = {};

                            let shippingData = self.$refs.sectionDelivery;

                            let shipping_methods = shippingData.shipping_methods;
                            let shipping_method = shippingData.shipping_method;
                            let fields;

                            if (shipping_methods[shipping_method].has_api) {

                                fields = shippingData.$refs.delivery_method_component.fields;

                                if (typeof fields !== 'undefined') {
                                    for(let key in fields){
                                        shipping_data[key] = fields[key];
                                    }
                                }

                            } else {

                                fields = shipping_methods[shipping_method].decoded_fields;

                                if (typeof fields !== 'undefined') {
                                    for(let field_key in fields){

                                        let tmp = {};

                                        for(let key in fields[field_key]){

                                            if(key === 'key' || key === 'value'){
                                                tmp[key] = fields[field_key][key];
                                            }
                                        }

                                        if(tmp) shipping_data[field_key] = tmp;

                                    }
                                }

                            }

                            shipping_data['shipping_method'] = shipping_method;

                            $.ajax({
                                url: 'checkout/save/shipping',
                                method: 'post',
                                data: {
                                    shipping: shipping_data
                                },
                                dataType: 'json',
                                beforeSend() {
                                    self.toggleLoader(1);
                                },
                            }).done(function (json) {
                                if (json.success) {
                                    console.log(1);
                                    self.sections.contact.status = 1;
                                    self.sections.delivery.status = 1;

                                    console.log(2);

                                    if (self.$refs.sectionPayment.isValidStep) {
                                        console.log(3);
                                        self.sections.payment.status = 1;
                                    } else {
                                        console.log(4);
                                        self.sections.payment.status = 0;
                                    }

                                }
                            }).always(function () {
                                self.toggleLoader(0);
                            });
                        } else {
                            //console.log('delivery');
                            this.sections.contact.status = 1;
                            this.sections.delivery.status = 0;
                            this.sections.payment.status = -1;
                        }
                        break;
                    case 'payment':
                        if (status) {
                            console.log('payment');
                            $.ajax({
                                url: 'checkout/save/payment',
                                method: 'post',
                                data: {
                                    payment: {
                                        payment_method: self.$refs.sectionPayment.payment_method,
                                        comment: self.$refs.sectionPayment.comment
                                    },

                                },
                                dataType: 'json',
                                beforeSend() {
                                    self.toggleLoader(1);
                                },
                            }).done(function (json) {
                                if (json.success) {
                                    self.sections.contact.status = 1;
                                    self.sections.delivery.status = 1;
                                    self.sections.payment.status = 1;
                                }
                            }).always(function () {
                                self.toggleLoader(0);
                            });
                        } else {
                            this.sections.contact.status = 1;
                            this.sections.delivery.status = 1;
                            this.sections.payment.status = 0;
                        }
                        break;
                }
            },
            addOrder() {

                let self = this;

                $.post('checkout/save/payment', {
                    payment: {
                        payment_method: self.$refs.sectionPayment.payment_method,
                        comment: self.$refs.sectionPayment.comment
                    }
                }, 'json').done(function (json) {
                    if (json.success) {
                        $.ajax({
                            method: 'post',
                            url: '/checkout/confirm',
                            dataType: 'json',
                            beforeSend() {
                                self.toggleLoader(1);
                            },
                        }).done(function (json) {
                            if (json.success === 1) {

                                let paymentData = self.$refs.sectionPayment;

                                let payment_methods = paymentData.payment_methods;
                                let payment_method = paymentData.payment_method;

                                if(payment_methods[payment_method].has_api){
                                    paymentData.$refs.paymentMethodComponent.confirmOrder();
                                }else{
                                    location = json.redirect;
                                }
                            } else if (json.error !== 1) {
                                self.toggleLoader(0, false);

                                getNotification(json['error']);
                            }
                        }).always(function (json) {
                            if (json.error === 1 || json.success === 1) {
                                self.toggleLoader(0);
                            }
                        });
                    }
                });
            },
            toggleLoader(status, hasBg = true) {
                if (status) {
                    _.debounce(function () {
                        if (hasBg) $('body').find('.bg-loading').css('display', 'flex');
                        $('.js-checkout-container')
                            .css('z-index', '5')
                            .find('.js-loading')
                            .addClass('loading--show');
                    }, 100)();
                } else {
                    _.debounce(function () {
                        if (hasBg) $('body').find('.bg-loading').css('display', 'none');
                        $('.js-checkout-container')
                            .css('z-index', '0')
                            .find('.js-loading').removeClass('loading--show');
                    }, 300)();
                }
            },
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
        },
        computed: {
            readyTrans() {
                return Object.keys(this.trans).length;
            }
        }
    }
</script>

<style scoped>

</style>
