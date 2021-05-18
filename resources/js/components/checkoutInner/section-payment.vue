<template>
    <div id="section_payment_component">
        <div class="checkout__header">
            <div class="l-flex">
                <div class="checkout__subtitle">{{trans.heading}}</div>
            </div>
        </div>
        <div class="checkout__content">
            <div class="form__set js-field-set form__set--inline">
                <div class="form__group form__group--left">
                    <label class="form__label form__label--required form__label--inline form__label--bold"
                           for="checkout-field-payment-method">{{trans.method}}</label>
                </div>
                <div class="form__group form__group--inBig">

                    <v-select
                            @input="changePaymentMethod"
                            :placeholder="trans.choose_method"
                            v-model="payment_method"
                            :reduce="method => method.code"
                            label="decoded_method_name"
                            :close-on-select="true"
                            :options="toArrayPaymentsFields"
                            :searchable='false'
                            :clearable="false"
                            class="select select--special">
                    </v-select>

                    <div class="form__validation form__validation--inline js-validation">{{trans.errors.method}}</div>
                </div>
            </div>
            <div class="form__set form__set--inline">
                <div class="form__group form__group--left">
                    <label class="form__label form__label--inline form__label--bold checkoutPayment__fieldText"
                           for="checkout-field-payment-enquire">{{trans.comment}}</label>
                </div>
                <div class="form__group form__group--inBig">
                    <textarea name="payment[comment]" id="checkout-field-payment-enquire" rows="10"
                              v-model="comment"
                              class="form__control js-control form__control--inline form__control--textarea"></textarea>
                </div>
            </div>
            <div id="check-payment">
                <template v-if="payment_method">
                    <component v-on:payment-add-order="$parent.addOrder"
                               v-if="payment_methods[payment_method].has_api"
                               :is="payment_method_component"
                               ref="paymentMethodComponent"></component>
                    <template v-else>
                        <a href="javascript:void(0)" id="button-confirm"
                           @click="$parent.addOrder()"
                           class="checkout__confirm btn btn--primary">
                            <span>{{ trans.button_confirm }}</span>
                        </a>
                    </template>
                </template>
                <template v-else>
                    <a href="javascript:void(0)" class="checkout__confirm btn btn--primary">
                        <span>
                            {{trans.button_confirm}}
                        </span>
                    </a>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "section-payment",
        props: ['statuses', 'trans'],
        data() {
            return {
                other: {},
                payment_methods: {},
                payment_method: '',
                comment: '',
                payment_method_component: {
                    name: 'Null',
                    template: '',
                    render(h) {
                        return '';
                    }
                }
            }
        },
        created() {
            var paymentSectionComponent = this;
            axios.get('/checkout/methods/payment')
                .then((httpResponse) => {
                    paymentSectionComponent.payment_methods = httpResponse.data;
                });

        },
        methods: {
            changePaymentMethod() {

                if (this.payment_method) {
                    if (this.payment_methods[this.payment_method].has_api) {
                        this.payment_method_component = require(`./payment/${this.payment_method}.vue`).default;
                    } else {
                        this.toNullPaymentComponent();
                    }
                } else {
                    this.toNullPaymentComponent();
                }

                this.$emit('change-section-status', 'payment', this.isValidStep);
            },
            toNullPaymentComponent() {
                this.payment_method_component = {
                    template: '<a href="javascript:void(0)" :class="getBtnClass"><span>{{$parent.trans.button_confirm}}</span></a>',
                    computed: {
                        getBtnClass() {
                            var btn_class = 'checkout__confirm btn btn--primary';

                            if (this.$parent.statuses.delivery.status === 1 && !this.$parent.isValidStep) {
                                btn_class += ' btn--disabled';
                            }

                            return btn_class;
                        }
                    }
                };
            }
        },
        computed: {
            isValidStep() {
                return !!(this.payment_method);
            },
            toArrayPaymentsFields(){
                let items = [];

                for(let method in this.payment_methods ){
                    items.push(this.payment_methods[method]);
                }

                return items;
            }
        }
    }
</script>

<style scoped>

</style>
