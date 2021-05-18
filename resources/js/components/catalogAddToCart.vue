<template>
    <a href="javascript:void(0)"
       @click="$store.dispatch('addToCart', id)"
       :class="'btn btn--primary' + (available ? '' : ' btn--disabled')">
        {{ text_add_to_cart}}
    </a>
</template>

<script>
    import store from './../store.js'

    export default {
        name: "catalogAddToCart",
        store,
        methods: {
            addToCart(id) {

            }
        },
        props: ['id', 'available', 'text_add_to_cart'],
        mounted() {

        },
        computed: {
            inCart() {
                this.$store.getters.hasProduct(this.id)
            }
        }
    }
</script>

<style scoped>

</style>