<template>
    <form :action="saveLink" method="POST"
          class="form form--account account__form js-form js-form-validate" id="account_edit_form">

        <div class="form__set form__set--inline">
            <div class="form__group form__group--left">
                <label for=""
                       class="form__label form__label--inline form__label--bold">{{$root.trans['account.entry.firstname']}}</label>
            </div>
            <div class="form__group form__group--inBig">
                <input type="text" name="firstname"
                       class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                       data-field="name" v-model="accountInfo.firstname">
                <div class="form__validation js-validation">{{$root.trans['account.error.firstname']}}</div>
            </div>
        </div>

        <div class="form__set form__set--inline">
            <div class="form__group form__group--left">
                <label for=""
                       class="form__label form__label--inline form__label--bold">{{$root.trans['account.entry.lastname']}}</label>
            </div>
            <div class="form__group form__group--inBig">
                <input type="text" name="lastname"
                       class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                       data-field="name" v-model="accountInfo.lastname">
                <div class="form__validation js-validation">{{$root.trans['account.error.lastname']}}</div>
            </div>
        </div>

        <div class="form__set form__set--inline">
            <div class="form__group form__group--left">
                <label for=""
                       class="form__label form__label--inline form__label--bold">{{$root.trans['account.entry.telephone']}}</label>
            </div>
            <div class="form__group form__group--inBig">
                <input type="text" placeholder="+38 (0__) ___-__-__" name="telephone"
                       class="form__control form__control--sPrimary form__control--maxw js-telMask js-control"
                       data-field="tel" v-model="accountInfo.telephone">
                <div class="form__validation js-validation">{{$root.trans['account.entry.telephone']}}</div>
            </div>
        </div>

        <div class="form__set form__set--inline">
            <div class="form__group form__group--left">
                <label for=""
                       class="form__label form__label--inline form__label--bold">{{$root.trans['account.entry.email']}}</label>
            </div>
            <div class="form__group form__group--inBig">
                <input type="email" name="email"
                       class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                       data-field="email" v-model="accountInfo.email">
                <div class="form__validation js-validation">{{$root.trans['account.entry.email']}}</div>
            </div>
        </div>

        <div class="form__set form__set--left">
            <input type="submit" class="form__btn form__btn--account btn btn--primary"
                   :value="$root.trans['common.button.save']"/>
        </div>

        <div class="loading js-loading" id="edit_acc_loading">
            <div class="loading__wrap">
                                <span class="icon icon--spin sb-icon-loading">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span><span class="path11"></span><span class="path12"></span>
                                </span>
            </div>
        </div>

        <div class="form__result js-result"></div>

    </form>
</template>

<script>
    export default {
        name: "accountEdit",
        props: ['accountInfo', 'saveLink'],
        inject: ['save'],
    }
</script>

<style scoped>

</style>