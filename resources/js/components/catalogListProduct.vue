<template>
    <div class="product product--list js-product">

        <div class="product__left product__left--list">

            <a :href="product.href">
                <img
                    
                     :src="product.thumb"
                     :alt="product.name"
                     class="img-responsive">
            </a>

            <div class="product--wrapList">

                <a :href="product.href"
                   class="product__title product__title--list">{{product.translate.name}}</a>

                <div class="product__rating product__rating--list rating" v-if="product.rating !== false">
                    <span v-html="showProductRating(product.rating)"></span>
                    <a :href="product.href_rating"
                       class="rating__link">{{product.rating_total}}</a>
                </div>

                <div class="product__attr product__attr--list attr" v-if="product.specification.length">
                    <div class="attr__title attr__title--inCategory">
                        {{$root.trans['text_short_attr']}}
                    </div>
                    <div class="attr__table attr__table--inCategory">
                        <div class="attr__row attr__row--ctg" v-for="attribute in  product.specification">
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

        <div class="product__right product__right--list">

            <div class="product__price price" v-if="product.type !== 3">
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
                <div class="action--left action--list">
                    <a href="javascript:void(0)"
                       @click="product.available ? (!$store.getters.hasCartProduct(product.id) ? $store.dispatch('addToCart', product.id) : $root.$refs.cart.show()) : ''"
                       class="btn btn--primary"
                       :class="{'btn--disabled' : !product.available}">
                        {{ $root.trans['common.button.cart']}}
                        <!--
                        <template v-if="$store.getters.hasCartProduct(product.id)">
                            in_cart
                        </template>
                        <template v-else>
                            {{ $root.trans['common.button.cart']}}
                        </template>
                        -->
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

            <div class="product__available available"
                 :class="product.available ? 'available--true' : 'available--false'">
                {{ product.stock_title }}
            </div>

            <div class="product__model model">
                {{ $root.trans['common.text.model'] + ': ' + product.id }}
            </div>

        </div>

    </div>
</template>

<script>
    export default {
        name: "catalogListProduct",
        props: ['product'],
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
