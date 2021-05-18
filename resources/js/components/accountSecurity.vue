<template>
    <form :action="saveLink" method="POST"
          class="form form--account account__form js-form js-form-validate" id="password_form">

        <div class="form__set form__set--inline">
            <div class="form__group form__group--left">
                <label for=""
                       class="form__label form__label--inline form__label--bold">{{$root.trans['account.entry.password']}}</label>
            </div>
            <div class="form__group form__group--inBig">
                <input type="password" name="password"
                       class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                       id="password" data-field="password">
                <div class="form__validation js-validation">{{$root.trans['account.error.password']}}</div>
            </div>
        </div>

        <div class="form__set form__set--inline">
            <div class="form__group form__group--left">
                <label for=""
                       class="form__label form__label--inline form__label--bold">{{$root.trans['account.entry.confirm']}}</label>
            </div>
            <div class="form__group form__group--inBig">
                <input type="password" name="password_confirmation"
                       class="form__control form__control--sPrimary form__control--maxw js-control js-field-validate"
                       data-sameCheck="#password" data-field="same">
                <div class="form__validation js-validation">{{$root.trans['account.error.password']}}</div>
            </div>
        </div>

        <div class="form__set form__set--left">
            <input type="submit" class="form__btn form__btn--account btn btn--primary"
                   :value="$root.trans['common.button.save']"/>
        </div>

        <div class="loading js-loading" id="password_loading">
            <div class="loading__wrap">
                                <span class="icon icon--spin sb-icon-loading">
                                    <span class="path1"></span><span class="path2"></span><span
                                        class="path3"></span><span class="path4"></span><span class="path5"></span><span
                                        class="path6"></span><span class="path7"></span><span class="path8"></span><span
                                        class="path9"></span><span class="path10"></span><span
                                        class="path11"></span><span class="path12"></span>
                                </span>
            </div>
        </div>

        <div class="form__result js-result"></div>
    </form>
</template>

<script>
    export default {
        name: "accountSecurity",
        props: ['saveLink']
    }
</script>

<style scoped>

</style>