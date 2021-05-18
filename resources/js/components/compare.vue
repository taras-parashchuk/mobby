<template>
    <div id="content" class="compareInfo" v-if="!loading">
        <template v-if="products.length">
            <vue-scroll :ops="$root.ops">
                <div class="compare__container js-compare-scroll">
                    <table class="compare__table">
                        <colgroup>
                            <col class="compare__col">
                            <col class="compare__col" v-for="product in products">
                        </colgroup>
                        <thead>
                        <tr>
                            <td class="compare__column compare__column--header">
                                <div class="compare__inner compare__inner--reload">
                                    <div class="compareReload">
                                        <div class="compareReload__container">
                                            <a href="javascript:void(0)"
                                            class="compare__reload"
                                            :class="[{'compare__reload--active' : selected_type === 'all'}, {'text-link' : selected_type !== 'all'}]"
                                            @click="selected_type = 'all'">
                                                {{$root.trans['pages.comparelist.button.all_attr']}}
                                            </a>
                                        </div>
                                        <div class="compareReload__container">
                                            <a href="javascript:void(0)"
                                            class="compare__reload"
                                            :class="[{'compare__reload--active' : selected_type === 'diff'}, {'text-link' : selected_type !== 'diff'}]"
                                            @click="selected_type = 'diff'">
                                                {{$root.trans['pages.comparelist.button.different_attr']}}
                                            </a>
                                        </div>
                                        <div class="compareReload__container">
                                            <a href="javascript:void(0)"
                                            class="compare__reload"
                                            :class="[{'compare__reload--active' : selected_type === 'same'}, {'text-link' : selected_type !== 'same'}]"
                                            @click="selected_type = 'same'">
                                                {{$root.trans['pages.comparelist.button.same_attr']}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="compare__column compare__column--header" v-for="(product, index) in products">
                                <div class="compare__inner">
                                    <div class="moduleProducts__item moduleProducts__item--column">
                                        <div class="moduleProducts__img moduleProducts__img--inContent">
                                            <a :href="product.href">
                                                <img :src="product.thumb"
                                                    :alt="product.translate.name"
                                                    class="img-responsive">
                                            </a>
                                        </div>
                                        <div class="moduleProducts__info moduleProducts__info--inContent">
                                            <a :href="product.href"
                                            class="moduleProducts__title">
                                                {{product.translate.name}}
                                            </a>

                                            <div class="moduleProducts__available"
                                                :class="product.available ? 'available--true' : 'available--false'">
                                                {{ product.stock_title }}
                                            </div>

                                            <div class="moduleProducts__model model">
                                                {{$root.trans['catalog.text.model']}} {{product.id}}
                                            </div>

                                            <div class="moduleProducts__rating rating" v-if="product.rating !== false">
                                                <span v-html="showProductRating(product.rating)"></span>
                                                <a :href="product.href_rating"
                                                class="rating__link">{{product.rating_total}}</a>
                                            </div>

                                            <div class="moduleProducts__price price" v-if="product.type !== 2">
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
                                            <div v-else>
                                                <span class="price__default">
                                                    {{ product.pricesFormat}}
                                                </span>
                                            </div>

                                            <div class="action action--column">
                                                <div class="action--left">
                                                    <catalog-add-to-cart :id="product.id"
                                                                        :available="product.available"
                                                                        :text_add_to_cart="$root.trans['common.button.cart']"></catalog-add-to-cart>
                                                </div>
                                                <div class="action--left">
                                                    <a href="javascript:void(0)"
                                                    @click.prevent="remove(index)"
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
                        </thead>
                        <tbody>
                        <tr v-for="attribute in attributes" class="compare__row js-compare-attr"
                            v-if="selected_type === 'all' || (attribute.same ? selected_type === 'same' : selected_type === 'diff')">
                            <td class="compare__column compare__column--name compare__column--attr">
                                {{attribute.name}}
                            </td>
                            <td v-for="product in products"
                                class="compare__column compare__column--value compare__column--attr">
                                <template
                                        v-if="product.to_attributes.find(to_attribute => {return to_attribute.attribute.id === attribute.id})">
                                    <template
                                            v-for="attribute_value in product.to_attributes.find(to_attribute => {return to_attribute.attribute.id === attribute.id}).values">
                                        {{attribute_value.translate.value}}
                                    </template>
                                </template>
                                <template v-else>
                                    ---
                                </template>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </vue-scroll>
        </template>
        <template v-else>
            <div class="empty">
                <div class="empty__text">
                    {{$root.trans['common.text.empty']}}
                </div>
                <div class="empty__btns">
                    <a :href="$root.routes['home']"
                       class="btn btn--primary empty__btn">
                        {{$root.trans['common.button.home']}}
                    </a>
                </div>
            </div>
        </template>
    </div>
</template>

<script>

    import store from './../store'

    export default {
        name: "compare",
        props: ['category_id'],
        store,
        components: {
            'catalog-add-to-cart': require('./catalogAddToCart').default
        },
        data() {
            return {
                testMe: [0, 60],
                products: [],
                attributes: [],
                selected_type: 'all',
                loading: true
            }
        },
        created() {
            let self = this;

            this.getProducts().then(() => {
                self.loading = false;

                $('.js-compare-scroll').mCustomScrollbar({
                    mouseWheel: {
                        enable: true,
                        axis: 'x',
                        invert: true
                    },
                    axis: "x"
                });
            });
        },
        methods: {
            getProducts() {
                let self = this;

                return new Promise((resolve) => {
                    axios.get('/compare-info/' + this.category_id)
                        .then(httpResponse => {
                            self.$set(self, 'products', httpResponse.data.products);
                            self.$set(self, 'attributes', httpResponse.data.attributes);
                            resolve();
                        });
                });
            },
            showProductRating: function (rating) {
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
            remove(i) {
                let self = this;

                this.$store.dispatch('removeFromProductsList', {
                    type: 'comparelist',
                    param: self.products[i].id
                }).then(() => {
                    self.products.splice(i, 1);
                });
            }
        },
        watch: {
            selected_type: (new_value, old_value) => {
                //if (old_value !== new_value) $('.js-compare-scroll').mCustomScrollbar('update');
            }
        }
    }
</script>

<style scoped>

</style>
