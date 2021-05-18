<template>
    <a href="javascript:void(0)"
       @click="available ? (!$store.getters.hasCartProduct(id) ? $store.dispatch('addToCart', id) : $emit('trigger-show-cart')) : ''"
       :class="defaultClass + ' ' + (!available ? 'btn--disabled' : '')">
        <template v-if="$store.getters.hasCartProduct(id)">
            {{$root.trans['common.button.in_cart']}}
        </template>
        <template v-else>
            <template v-if="available">
                {{$root.trans['common.button.cart']}}
            </template>
            <template v-else>
                {{$root.trans['common.text.outstock']}}
            </template>
        </template>
    </a>
</template>

<script>

    import store from './../store'

    export default {
        name: "btnAddToCart",
        store,
        props: {
            available: Number,
            id: Number,
            defaultClass: String,
        },
    }
</script>

<style scoped>

</style>