<template>
    <div>
        <div class="moduleProducts__item moduleProducts__item--inSlider moduleProducts__item--column">
            <div class="moduleProducts__img moduleProducts__img--inContent">
                <a :href="product.href">
                    <img :src="product.thumb" :alt="product.translate.name">
                </a>
                <div class="moduleProducts__labels">
                    <div v-if="product.type === 1 && product.special" class="label label--share">
                        {{product.special_diff}}
                    </div>
                    <div v-if="product.hit" class="label label--hit">
                        {{$root.trans['common.labels.hit']}}
                    </div>
                </div>

                <div class="action action_pos_img" v-if="$root.theme === 'beauty'">
                    <a v-if="Object.keys($root.accountInfo).length" href="javascript:void(0)"
                       class="icon sb-icon-like action__add action__add--like js-wishlist-add"
                       :class="{'action__add--active': $store.getters.hasProductsListProduct('wishlist', product.id)}"
                       @click="!$store.getters.hasProductsListProduct('wishlist', product.id) ? $store.dispatch('addToProductsList', {type:'wishlist', param:product.id}) : ''"></a>
                    <a v-else class="icon sb-icon-like action__add action__add--like js-open-login-form"
                       href="javascript:void(0)">
                    </a>
                </div>
            </div>
            <div class="moduleProducts__info moduleProducts__info--inContent">
                <a :href="product.href"
                   class="moduleProducts__title">{{ product.translate.name }}</a>

                <template v-if="$root.theme !== 'beauty'">
                    <div :class="'moduleProducts__available available available--'+(product.available ? 'true' : 'false')">
                        {{product.stock_title}}
                    </div>
                    <div class="moduleProducts__model model">
                        {{$root.trans['common.text.model'] + ': ' + product.id }}
                    </div>

                    <div class="moduleProducts__rating rating" v-if="product.rating !== false">
                        <span v-html="$parent.showProductRating(product.rating)"></span>
                        <a :href="product.href_rating"
                           class="rating__link">{{product.rating_total}}</a>
                    </div>
                </template>

                <div class="moduleProducts__price price" v-if="product.type === 1">
                    <template v-if="product.special">
                        <span class="price__default price__default--withShare">
                            {{ product.priceFormat}}
                        </span>
                        <span class="price__share">
                            {{ product.specialFormat}}
                        </span>
                    </template>
                    <template v-else>
                        <span class="price__default">
                            {{ product.priceFormat}}
                        </span>
                    </template>
                </div>
                <div class="moduleProducts__price price" v-else>
                    <span class="price__default">
                        {{ product.pricesFormat}}
                    </span>
                </div>
                <div class="action" v-if="$root.theme !== 'beauty'">
                    <div class="action--left">
                        <a href="javascript:void(0)"
                           v-if="product.available"
                           @click="product.available ? (!$store.getters.hasCartProduct(product.id) ? $store.dispatch('addToCart', product.id) : $root.$refs.cart.show()) : ''"
                           class="btn btn--primary"
                           :class="{'btn--disabled' : !product.available}">
                            {{ $root.trans['common.button.cart']}}
                        </a>
                        <a href="javascript:void(0)"
                           v-else
                           class="btn btn--primary btn--disabled">
                            {{ $root.trans['common.button.cart']}}
                        </a>
                    </div>
                    <div class="action--right">
                        <a href="javascript:void(0)"
                           class="icon sb-icon-like action__add action__add--like js-wishlist-add"
                           :class="{'action__add--active': $store.getters.hasProductsListProduct('wishlist',product.id)}"
                           @click="!$store.getters.hasProductsListProduct('wishlist',product.id) ? $store.dispatch('addToProductsList', {type:'wishlist', param:product.id}) : ''"></a>
                        <a href="javascript:void(0)"
                           class="icon sb-icon-libra action__add action__add--compare js-comparelist-add"
                           :class="{'action__add--active': $store.getters.hasProductsListProduct('comparelist',product.id)}"
                           @click="!$store.getters.hasProductsListProduct('comparelist',product.id) ? $store.dispatch('addToProductsList', {type:'comparelist', param:product.id}) : ''"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import store from './../store'

    export default {
        name: "slider-product",
        props: ['product'],
        store
    }
</script>

<style scoped>

</style>