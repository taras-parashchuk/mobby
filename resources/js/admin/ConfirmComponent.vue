<template>
    <div class="confirm">

        <template v-if="messageHasTitle">
            <div class="confirm__header" v-if="options.html" v-html="messageTitle"></div>
            <div class="confirm__header" v-else>{{ messageTitle }}</div>
        </template>

        <div class="confirm__body">

            <div class="confirm__content" v-if="options.html"  v-html="messageBody"></div>
            <div class="confirm__content" v-else>{{ messageBody }}</div>

            <div class="flex flex--justify-end">
                <a class="btn btn--cancel confirm__btn" href="javascript:void(0)" @click="cancel()">{{options.cancelText}}</a>
                <a class="btn btn--confirm confirm__btn" href="javascript:void(0)" @click="proceed()">{{options.okText}}</a>
            </div>

        </div>
    </div>
</template>

<script>
    import DialogMixin from 'vuejs-dialog/dist/vuejs-dialog-mixin.min.js';

    // include the default style
    import 'vuejs-dialog/dist/vuejs-dialog.min.css';

    export default {
        name: "ConfirmComponent",
        mixins: [DialogMixin]
    }
</script>

<style scoped>

</style>