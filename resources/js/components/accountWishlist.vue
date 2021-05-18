<template>
    <div class="compare__container js-compare-scroll" v-if="accountInfo.wishlist && accountInfo.wishlist.length">
        <table class="compare__table" v-if="$root.theme !== 'beauty'">
            <colgroup>
                <col class="compare__col" v-for="product in accountInfo.wishlist">
            </colgroup>
            <tr>
                <td class="compare__column compare__column--header"
                    v-for="(product, index) in accountInfo.wishlist">
                    <div class="compare__inner">
                        <div class="moduleProducts__item moduleProducts__item--column">
                            <div class="moduleProducts__img moduleProducts__img--inContent">
                                <a :href="product.href">
                                    <img :src="product.thumb"
                                         :alt="product.translate.name">
                                </a>
                            </div>
                            <div class="moduleProducts__info moduleProducts__info--inContent">
                                <a :href="product.href"
                                   class="moduleProducts__title">{{product.translate.name}}</a>

                                <div class="moduleProducts__available"
                                     :class="product.available ? 'available--true' : 'available--false'">
                                    {{ product.stock_title }}
                                </div>

                                <div class="moduleProducts__model model">
                                    {{ $root.trans['common.text.model'] + ': ' + product.id }}
                                </div>

                                <div class="moduleProducts__rating rating" v-if="product.rating !== false">
                                    <span v-html="showProductRating(product.rating)"></span>
                                    <a :href="product.href_rating"
                                       class="rating__link">{{product.rating_total}}</a>
                                </div>

                                <div class="moduleProducts__price price">
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

                                <div class="action action--column">
                                    <div class="action--left">
                                        <a href="javascript:void(0)"
                                           @click="product.available ? (!$store.getters.hasCartProduct(product.id) ? $store.dispatch('addToCart', product.id) : $root.$refs.cart.show()) : ''"
                                           class="btn btn--primary"
                                           :class="{'btn--disabled' : !product.available}">
                                            {{ $root.trans['common.button.cart']}}
                                        </a>
                                    </div>
                                    <div class="action--left">
                                        <a href="javascript:void(0)"
                                           @click="removeProduct(index)"
                                           class="compare__remove">
                                            <i class="icon sb-icon-cancel-round"></i>
                                            <span>{{$root.trans['common.button.remove']}}</span>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div class="compare__products" v-else>
            <div v-for="(product, index) in accountInfo.wishlist" class="moduleProducts__item moduleProducts__item--column">
                <div class="moduleProducts__img moduleProducts__img--inContent moduleProducts__img--inWishlist">
                    <a :href="product.href">
                        <img :src="product.thumb"
                             :alt="product.translate.name">
                    </a>
                </div>
                <div class="moduleProducts__info moduleProducts__info--inContent moduleProducts__info--inWishlist">
                    <a :href="product.href"
                       class="moduleProducts__title">{{product.translate.name}}</a>
                    <div class="moduleProducts__price price">
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
                    <div class="action action--column action--inWishlist">
                        <div class="action--left">
                            <a href="javascript:void(0)"
                               @click="product.available ? (!$store.getters.hasCartProduct(product.id) ? $store.dispatch('addToCart', product.id) : $root.$refs.cart.show()) : ''"
                               class="btn btn--primary"
                               :class="{'btn--disabled' : !product.available}">
                                {{ $root.trans['common.button.cart']}}
                            </a>
                        </div>
                        <div class="action--left">
                            <a href="javascript:void(0)"
                               @click="removeProduct(index)"
                               class="compare__remove">
                                <i class="icon sb-icon-cancel-round"></i>
                                <span>{{$root.trans['common.button.remove']}}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="empty" v-else>
        <div class="empty__text">
            {{$root.trans['account.error.empty-wishlist']}}
        </div>
    </div>
</template>

<script>

    import store from './../store'

    export default {
        name: "accountWishlist",
        props: ['accountInfo'],
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
            },
            removeProduct(index ) {
                let self = this;

                let product = this.accountInfo.wishlist[index];

                let product_id = product.id;

                store.dispatch('removeFromProductsList', {type: 'wishlist', param: product.id}).then(function () {
                    self.accountInfo.wishlist.splice(self.accountInfo.wishlist.findIndex(product => product.id === product_id), 1);
                });
            }
        }
    }
</script>

<style scoped>

</style>