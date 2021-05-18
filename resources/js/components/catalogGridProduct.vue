<template>
    <div class="product js-product">

        <div class="product__sh" v-if="$root.theme !== 'beauty'">

            <div class="product__top product__top--grid">
                <a :href="product.href">
                    <img 
                         
                         :src="product.thumb"
                         :alt="product.name"
                         class="img-responsive">
                </a>
            </div>

            <div class="product__labels">
                <div class="label label--share" v-if="product.type === 1 && product.special">
                    {{ product.special_diff }}
                </div>
                <div class="label label--hit" v-if="product.hit">
                    {{$root.trans['common.labels.hit']}}
                </div>
            </div>

            <div class="product__middl">

                <a :href="product.href"
                   class="product__title">
                    {{product.translate.name}}
                </a>

                <div class="product__available available"
                     :class="product.available ? 'available--true' : 'available--false'">
                    {{ product.stock_title }}
                </div>

                <div class="product__model model">
                    {{ $root.trans['common.text.model'] + ': ' + product.id }}
                </div>

                <div class="product__rating rating" v-if="product.rating !== false">
                    <span v-html="showProductRating(product.rating)"></span>
                    <a :href="product.href_rating"
                       class="rating__link">{{product.rating_total}}</a>
                </div>

                <div class="product__price price" v-if="product.type === 1">
                    <template v-if="product.special">
                        <span class="price__default price__default--withShare">
                            {{ product.priceFormat }}
                        </span>
                        <span class="price__share">
                            {{ product.specialFormat }}
                        </span>
                    </template>
                    <template v-else>
                        <span class="price__default">
                            {{ product.priceFormat}}
                        </span>
                    </template>
                </div>
                <div class="product__price price" v-else>
                    <span class="price__default">
                            {{ product.pricesFormat}}
                        </span>
                </div>

                <div class="product__actions action">
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
                        <a v-if="Object.keys($root.accountInfo).length" href="javascript:void(0)"
                           class="icon sb-icon-like action__add action__add--like js-wishlist-add"
                           :class="{'action__add--active': $store.getters.hasProductsListProduct('wishlist', product.id)}"
                           @click="!$store.getters.hasProductsListProduct('wishlist', product.id) ? $store.dispatch('addToProductsList', {type:'wishlist', param:product.id}) : ''"></a>
                        <a v-else class="icon sb-icon-like action__add action__add--like js-open-login-form"
                           href="javascript:void(0)">
                        </a>
                        <a href="javascript:void(0)"
                           class="icon sb-icon-libra action__add action__add--compare js-comparelist-add"
                           :class="{'action__add--active': $store.getters.hasProductsListProduct('comparelist', product.id)}"
                           @click="!$store.getters.hasProductsListProduct('comparelist',product.id) ? $store.dispatch('addToProductsList', {type:'comparelist', param:product.id}) : ''"></a>
                    </div>
                </div>

            </div>

            <div class="product__bottom">

                <div class="product__attr attr" v-if="product.specification.length">
                    <div class="attr__title attr__title--inCategory">
                        {{$root.trans['text_short_attr']}}
                    </div>
                    <div class="attr__table attr__table--inCategory">
                        <div v-for="attribute in product.specification">
                            <span class="attr__name attr__name--inCategory">{{attribute.name + ':'}}</span>
                            <span class="attr__value">
                                <template v-for="attribute_value_name in attribute.values">
                                    {{attribute_value_name}}
                                </template>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <template v-else>

            <div class="product__top product__top--grid">
                <a :href="product.href">
                    <img 
                         
                         :src="product.thumb"
                         :alt="product.name"
                         class="img-responsive">
                    <img 
                         
                         :src="product.thumb_second || product.thumb"
                         :alt="product.name"
                         class="img-responsive">
                </a>
            </div>

            <div class="product__labels">
                <div class="label label--share" v-if="product.type === 1 && product.special">
                    {{ product.special_diff }}
                </div>
                <div class="label label--hit" v-if="product.hit">
                    {{$root.trans['common.labels.hit']}}
                </div>
            </div>

            <div class="action action_pos_img">
                <a v-if="Object.keys($root.accountInfo).length" href="javascript:void(0)"
                   class="icon sb-icon-like action__add action__add--like js-wishlist-add"
                   :class="{'action__add--active': $store.getters.hasProductsListProduct('wishlist', product.id)}"
                   @click="!$store.getters.hasProductsListProduct('wishlist', product.id) ? $store.dispatch('addToProductsList', {type:'wishlist', param:product.id}) : ''"></a>
                <a v-else class="icon sb-icon-like action__add action__add--like js-open-login-form"
                   href="javascript:void(0)">
                </a>
            </div>

            <div class="product__middl">

                <a :href="product.href"
                   class="product__title">
                    {{product.translate.name}}
                </a>

                <div class="product__price price" v-if="product.type === 1">
                    <template v-if="product.special">
                        <span class="price__default price__default--withShare">
                            {{ product.priceFormat }}
                        </span>
                        <span class="price__share">
                            {{ product.specialFormat }}
                        </span>
                    </template>
                    <template v-else>
                        <span class="price__default">
                            {{ product.priceFormat}}
                        </span>
                    </template>
                </div>
                <div class="product__price price" v-else>
                    <span class="price__default">
                            {{ product.pricesFormat}}
                        </span>
                </div>

            </div>
        </template>
    </div>
</template>

<script>

    import store from './../store'

    export default {
        name: "catalogGridProduct",
        props: ['product'],
        store,
        methods: {
            showProductRating(rating) {
                let html = '';

                var i = 0;
                while (i < 5) {
                    if (i < rating) html += '<i class="icon sb-icon-star rating--fill"></i>';
                    else {
                        html += '<i class="icon sb-icon-star rating--null"></i>';
                        i++;
                    }
                }

                return html;
            }
        }
    }
</script>

<style scoped>

</style>