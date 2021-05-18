<template>
    <form :action="saveLink" method="POST"
          class="form form--account account__form js-form js-form-validate" id="newsletter_form">
        <div class="form__group form__group--inBig">
            <div class="radioNew">
                <input type="checkbox" name="newsletter" class="radioNew__value js-newsletter" id="newsletter"
                       value="1" v-model="accountInfo.newsletter">
                <label for="newsletter"
                       class="radioNew__label radioNew__label--small">{{$root.trans['account.entry.newsletter.text']}}</label>
            </div>

            <div class="form__set form__set--left">
                <input type="submit" class="form__btn form__btn--account btn btn--primary"
                       :value="$root.trans['common.button.save']"/>
            </div>

            <div class="loading js-loading" id="newsletter_loading">
                <div class="loading__wrap">
                                    <span class="icon icon--spin sb-icon-loading">
                                        <span class="path1"></span><span class="path2"></span><span
                                            class="path3"></span><span class="path4"></span><span
                                            class="path5"></span><span class="path6"></span><span
                                            class="path7"></span><span class="path8"></span><span
                                            class="path9"></span><span class="path10"></span><span
                                            class="path11"></span><span class="path12"></span>
                                    </span>
                </div>
            </div>

        </div>
    </form>
</template>

<script>
    export default {
        name: "accountSubscribes",
        props: ['accountInfo','saveLink']
    }
</script>

<style scoped>

</style>