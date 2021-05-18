<template>
    <div id="section_delivery_component">
        <div class="checkout__header">
            <div class="l-flex">
                <div class="checkout__subtitle">{{ trans.heading }}</div>
            </div>
        </div>
        <div class="checkout__content">
            <div class="form__set js-field-set form__set--inline">
                <div class="form__group form__group--left">
                    <label class="form__label form__label--required form__label--inline form__label--bold"
                           for="checkout-field-shipping-method">{{ trans.method }}</label>
                </div>
                <div class="form__group form__group--inBig">

                    <v-select
                            :placeholder="trans.choose_method"
                            @input="getInfoDelivery"
                            v-model="shipping_method"
                            :reduce="method => method.code"
                            label="decoded_method_name"
                            :close-on-select="true"
                            :options="toArrayShippingFields"
                            :searchable='false'
                            :clearable="false"
                            class="select select--special">
                    </v-select>

                    <div class="form__validation form__validation--inline js-validation">
                        {{ trans.errors.shipping }}
                    </div>
                </div>
            </div>
            <div id="check-shipping">
                <template v-if="shipping_method">
                    <component :is="delivery_component" v-if="shipping_methods[shipping_method].has_api"
                               :fields="shipping_methods[shipping_method].decoded_fields"
                               ref="delivery_method_component"></component>
                    <template v-else>
                        <div v-for="(field, field_index) in shipping_methods[shipping_method].decoded_fields"
                             class="form__set js-field-set form__set--inline">
                            <div class="form__group form__group--left">
                                <label :class="'form__label form__label--inline form__label--bold' + (field.validation.required ? ' form__label--required':'')">{{field.trans.name}}</label>
                            </div>
                            <div class="form__group form__group--inBig">
                                <input
                                        v-if="field.type === 'input'"
                                        v-model="field.value"
                                        :name="field.key"
                                        :data-min="field.validation.min_l"
                                        :data-max="field.validation.max_l"
                                        v-on:input="checkDebounce(field_index, $event)"
                                        :data-field="field.check_type"
                                        type="text"
                                        class="form__control js-control js-need-validate form__control--maxw"
                                >
                                <div class="form__validation form__validation--inline js-validation">
                                    {{trans.errors.input}}
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</template>

<script>

    import {checkField, Validate} from './../../common';

    export default {
        name: "section-delivery",
        props: ['trans'],
        data() {
            return {
                other: {},
                shipping_methods: {},
                shipping_method: '',
                delivery_component: {
                    name: 'Null',
                    template: '',
                    render(h) {
                        return '';
                    }
                },
                fields_inner_status: false
            };
        },
        created() {
            var sectionDelivery = this;

            axios.get('/checkout/methods/shipping')
                .then((httpResponse) => {
                    sectionDelivery.shipping_methods = httpResponse.data;
                });

            this.checkDebounce = _.debounce(this.checkValidFields, 1000);
        },
        updated() {
            var sectionDelivery = this;
            /*
            $('#section_delivery_component .js-style-select').selectric({
                customClass: {
                    prefix: 'select',
                },
                disableOnMobile: false,
                arrowButtonMarkup: '<b class="button"></b>',
                onChange: function (el) {
                    sectionDelivery.shipping_method = $(el).val();
                    sectionDelivery.getInfoDelivery();
                },
            });
            */
        },
        methods: {
            getInfoDelivery() {
                let sectionDelivery = this;

                if (this.shipping_method) {

                    if (this.shipping_methods[this.shipping_method].has_api) {
                        sectionDelivery.fields_inner_status = false;
                        sectionDelivery.delivery_component = require(`./shipping/${this.shipping_method}.vue`).default;

                        this.checkSectionStatus();
                    } else {
                        //sectionDelivery.fields_inner_status = false;
                        this.toNullDeliveryComponent();
                    }
                    this.checkValidFields();
                } else {

                    sectionDelivery.fields_inner_status = false;
                    this.toNullDeliveryComponent();
                    sectionDelivery.checkSectionStatus();
                }
            },
            checkValidFields: function (field_index, event) {

                var sectionDelivery = this;

                let fields;

                let valid_all_fields = true;

                if (this.shipping_methods[this.shipping_method].has_api) {

                    valid_all_fields = false;

                } else {

                    fields = this.shipping_methods[this.shipping_method].decoded_fields;

                    fields.forEach(function (field, field_index) {
                        if (field.value && field.value.length > 0) {
                            if (!checkField($("#section_delivery_component [name=" + field.key + "]"), false, new Validate())) {
                                fields[field_index].valid = true;
                            } else {
                                fields[field_index].valid = false;
                            }
                        }
                    });

                    fields.forEach(function (el) {
                        if (valid_all_fields && !el.valid) valid_all_fields = false;
                    });
                }

                sectionDelivery.changeStatusInnerFields(valid_all_fields);
            },
            checkSectionStatus: function () {
                this.$emit('change-section-status', 'delivery', this.isValidStep);
            },
            changeStatusInnerFields: function (status) {
                this.fields_inner_status = status;
                this.checkSectionStatus();
            },
            toNullDeliveryComponent() {
                this.delivery_component = {
                    name: 'Null',
                    template: '',
                    render(h) {
                        return '';
                    }
                };
            }
        },
        computed: {
            isValidStep() {
                return !!(this.shipping_method && this.fields_inner_status);
            },
            toArrayShippingFields() {
                let items = [];

                for(let method in this.shipping_methods ){
                    items.push(this.shipping_methods[method]);
                }

                return items;
            }
        }
    }
</script>

<style scoped>

</style>
