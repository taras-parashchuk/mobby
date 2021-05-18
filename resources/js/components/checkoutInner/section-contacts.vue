<template>
    <div>
        <div class="checkout__header">
            <div class="l-flex">
                <div class="checkout__subtitle">{{trans.heading}}</div>
                <template v-if="!accountInfo">
                    <span class="checkoutContacts__quest">{{trans.has_account}}</span>
                    <a href="#checkoutLogin" class="checkoutContacts__link js-open-modal">{{trans.login}}</a>
                </template>
            </div>
        </div>
        <div class="checkout__content">
            <div class="form__set js-field-set form__set--inline">
                <div class="form__group form__group--left">
                    <label class="form__label form__label--required form__label--inline form__label--bold"
                           for="checkout-field-name">{{ trans.full_name }}</label>
                </div>
                <div class="form__group form__group--inBig">
                    <input type="text" name="customer[full_name]"
                           class="form__control js-control form__control--maxw js-need-validate js-field-validate"
                           id="checkout-field-name"
                           v-model="customer.full_name"
                           data-field="name"
                           data-max="64"
                           autocomplete="false" autocorrect="false" aria-autocomplete="off"
                           v-on:input="isValidField">
                    <div class="form__validation form__validation--inline js-validation">
                        {{ trans.errors.full_name }}
                    </div>
                </div>
            </div>
            <div class="form__set js-field-set form__set--inline">
                <div class="form__group form__group--left">
                    <label class="form__label form__label--required form__label--inline form__label--bold"
                           for="checkout-field-tel">{{ trans.telephone }}</label>
                </div>
                <div class="form__group form__group--inBig">
                    <!-- <input type="text" name="customer[tel]"
                           v-on:change="isValidField"
                           class="form__control checkoutContacts__telfield js-telMask js-control form__control--maxw js-field-validate js-need-validate"
                           id="checkout-field-tel" v-model="customer.tel" data-field="tel"> -->
                    <masked-input name="customer[tel]"
                           @input="isValidField"  mask="\+38\ (111) 111-11-11"
                        class="form__control checkoutContacts__telfield js-telMask js-control form__control--maxw js-field-validate js-need-validate"
                        id="checkout-field-tel" v-model="customer.tel" data-field="tel"/>
                    <a href="javascript:void(0)" class="btn btn--checkoutHelp" v-on:click="fastCheckout">
                        {{ trans.fast_order }}
                    </a>

                    <v-popover offset="6" placement="right" popoverClass="checkoutContactsHelp">
                        <span class="checkoutContacts__help">?</span>

                        <template slot="popover">
                            <p class="checkoutContactsHelp__text">
                            {{ trans.fast_order_help }}
                            </p>
                            <a class="checkoutContactsHelp__close sb-icon-cancel" v-close-popover></a>
                        </template>
                    </v-popover>

                    <div v-bind:class="'form__validation form__validation--inline js-validation ' + (other.logged ? 'form__validation--completed' : '')">
                        {{ trans.errors.telephone }}
                    </div>
                    <p class="checkoutContactsHelp__text checkoutContactsHelp__text--mobile">
                        {{ trans.fast_order_help }}</p>
                </div>
            </div>
            <div class="form__set js-field-set form__set--inline">
                <div class="form__group form__group--left">
                    <label class="form__label form__label--inline form__label--bold"
                           for="checkout-field-email">{{ trans.email }}</label>
                </div>
                <div class="form__group form__group--inBig">
                    <input type="text" name="customer[email]" v-model="customer['email']"
                           class="form__control form__control--cell js-control form__control--maxw js-field-validate"
                           id="checkout-field-email" data-field="email"
                           v-on:input="isValidField">
                    <div v-bind:class="'form__validation form__validation--inline js-validation' + (other.logged ? 'form__validation--completed' : '')">
                        {{ trans.errors.email }}
                    </div>
                </div>
            </div>
        </div>

        <div class="checkout__popup" v-if="!accountInfo">
            <form action="/account/login" method="POST" id="checkoutLogin"
                  class="form form--login js-form js-form-validate">
                <div class="form__title form__title--medium">
                    {{ other.heading_login }}
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold">{{ other.entry_email }}</label>
                    <input type="text" name="email"
                           class="form__control js-control form__control--maxw js-field-validate js-input-email"
                           data-field="email">
                    <div class="form__validation js-validation">{{ other.error_email }}</div>
                </div>
                <div class="form__set">
                    <label class="form__label form__label--bold">{{ other.entry_password }}</label>
                    <input type="password" name="password"
                           class="form__control js-control form__control--maxw">
                    <div class="form__validation js-validation"></div>
                </div>
                <div class="form__set form__set--left">
                    <input type="submit" class="form__btn form__btn--second btn btn--primary"
                           v-model="other.button_login"/>
                </div>
                <div class="loading js-loading">
                    <div class="loading__wrap">
                <span class="icon icon--spin sb-icon-loading">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                        class="path4"></span><span class="path5"></span><span class="path6"></span><span
                        class="path7"></span><span class="path8"></span><span class="path9"></span><span
                        class="path10"></span><span class="path11"></span><span class="path12"></span>
                </span>
                    </div>
                </div>
                <div class="form__result js-result"></div>
            </form>
        </div>
    </div>
</template>

<script>

    import {checkField, Validate} from './../../common';

    export default {
        name: "section-contacts",
        props: ['trans', 'accountInfo'],
        data() {
            return {
                other: {},
                customer: {},
                valid: {
                    name: false,
                    tel: false,
                    email: false
                }
            }
        },
        created() {
            var selfContacts = this;

            /*
            $.get('/checkout/customer/getJsonData', function (json) {
                selfContacts.other = json.other;
                selfContacts.customer = json.customer;
            }).done(function (json) {
                if (json.other.logged) {

                    selfContacts.valid.name = true;
                    selfContacts.valid.tel = true;

                    self.$emit('change-section-status', 'contact', selfContacts.isValidStep);
                }
            });
            */

            if(Object.keys(this.accountInfo).length){
                this.$set(this.customer, 'full_name', this.accountInfo.lastname + ' ' + this.accountInfo.firstname);
                this.$set(this.customer, 'tel', this.setTelephone());
                this.$set(this.customer, 'email', this.accountInfo.email);
            }

            this.checkDebounce = _.debounce(this.checkSectionStatus, 2000);
        },
        mounted: function () {

            self = this;

            $('.js-telMask').on('change', function () {
                self.saveTel($(this).val());
            });


        },
        methods: {
            checkSectionStatus: function () {

                self = this;

                $('.js-section-contacts .checkout__content .js-field-validate').each(function () {

                    $(this).next('.form__validation').removeClass('form__validation--error');

                    if ($(this).val().length > 0) {
                        self.valid[$(this).data('field')] = !checkField($(this), false, new Validate());
                    }
                });

                self.$emit('change-section-status', 'contact', this.isValidStep);
            },
            isValidField: function () {
                this.checkDebounce();
            },
            saveTel(value) {
                this.customer.tel = value;
                this.isValidField();
            },
            fastCheckout() {

                var self = this;

                for (let field of $('.js-section-contacts').find('.js-need-validate')) {
                    if (!checkField($(field), false, new Validate())) {
                        self.$parent.toggleLoader(true);
                        axios.post('/checkout/save/user', {customer: self.customer})
                            .then((httpResponse) => {
                                if (httpResponse.data.success) {
                                    axios.post('/checkout/confirm', {type: 'fast_order'})
                                        .then((httpResponse) => {
                                            if (httpResponse.data['redirect']) location = httpResponse.data['redirect'];
                                        });
                                }
                            })
                            .catch()
                            .finally(() => {
                                self.$parent.toggleLoader(false);
                            });

                        break;
                    }
                }
            },
            setTelephone(masked = false){
                if (this.accountInfo.telephone !== null && this.accountInfo.telephone.length) {
                    let tel = this.accountInfo.telephone;
                    let tel_masked
                    let mask = "";
                    if (masked) {
                        mask = "+38 "
                    }
                    if (tel.length === 10) {
                       tel_masked = mask + "("+ tel.substr(0, 3) + ") " +  tel.substr(3, 3)+ "-" + tel.substr(6, 2) + "-" + tel.substr(8, 2);
                    }else{
                       tel_masked = mask + "("+ tel.substr(2, 3) + ") " +  tel.substr(5, 3)+ "-" + tel.substr(8, 2) + "-" + tel.substr(10, 2);
                    }                
                    return tel_masked
                }                
            }

        },
        computed: {
            isValidStep() {
                return (this.valid.name && this.valid.tel) ? true : false;
            },
            isAccountDataChanged(){
                if (this.$root.accountInfo.email || this.$root.accountInfo.telephone) {
                    return true; 
                }
            }
        },
        watch:{
            isAccountDataChanged: function (value) {
                if (value) {
                    this.$set(this.customer, 'full_name', this.accountInfo.lastname + ' ' + this.accountInfo.firstname);
                    this.$set(this.customer, 'tel', this.setTelephone(true));
                    this.$set(this.customer, 'email', this.accountInfo.email);
                }
            },
        }
    }
</script>
