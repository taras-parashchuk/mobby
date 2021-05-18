<template>
    <div :class="isMobile ? 'mobileMenu__search' : 'topSearch'">
        <template v-if="$root.theme === 'beauty'">
            <div class="topSearch__container">
                <input name="search" v-model="searchPhrase" :placeholder="$root.trans['common.text.what_are_you_searching']"
                       class="topSearch__control" autocomplete="off">
                <a href="javascript:void(0)" @click="search" class="btn topSearch__btn js-search-submit">
                    <i class="icon sb-icon-magnifying-glass"></i>
                </a>
            </div>
        </template>
        <template v-else>
            <input name="search" v-model="searchPhrase" :placeholder="$root.trans['common.text.what_are_you_searching']"
                   class="topSearch__control" autocomplete="off">
            <a href="javascript:void(0)" @click="search" class="btn topSearch__btn js-search-submit">
                <i class="icon sb-icon-magnifying-glass"></i>
            </a>
        </template>

        <div v-if="!isMobile" id="topSearch__results"
             class="topSearch__results"
             :class="!(findedItems && searchPhrase) ? 'topSearch__results--hidden': 'topSearch__results--visible'">
            <div v-for="findedItem in findedItems"
                 class="moduleProducts__item moduleProducts__item--inSearchLive moduleProducts__item--row js-search-product-container">
                <div class="moduleProducts__img">
                    <a :href="findedItem.href" class="js-search-product-link">
                        <img :src="findedItem.thumb"
                             :alt="findedItem.translate.name" class="js-search-product-img">
                    </a>
                </div>
                <div class="moduleProducts__info">
                    <a :href="findedItem.href"
                       class="moduleProducts__title js-search-product-link js-search-product-title"
                    :class="{'moduleProducts__title--inSearch': $root.theme === 'beauty'}">{{findedItem.translate.name}}</a>

                    <div class="moduleProducts__available  js-search-product-available" v-if="$root.theme !== 'beauty'"
                         :class="findedItem.available ? 'available--true': 'available--false'">
                        {{findedItem.stock_title}}
                    </div>

                    <div class="moduleProducts__price moduleProducts__price--inSearch price js-search-product-price">
                    <span class="price"
                          :class="findedItem.special ? 'price__share' : 'price__default'">{{findedItem.price}}</span>
                    </div>
                </div>
            </div>
            <a :href="searchLink" class="topSearch__more js-search-more" v-if="hasMoreResults">
                <span>{{textMore}}</span>
                <i class="icon sb-icon-down-arrow"></i>
            </a>
        </div>
    </div>
</template>

<script>

    export default {
        name: "searchField",
        props: ['placeholder', 'textMore', 'isMobile'],
        data: function () {
            return {
                searchPhrase: '',
                findedItems: [],
                hasMoreResults: false,
                searchLink: ''
            };
        },
        watch: {
            searchPhrase: function () {
                if (this.searchPhrase.length) {
                    this.debouncedGetItems();
                }
            }
        },
        created: function () {
            this.debouncedGetItems = _.debounce(this.getItems, 800)
        },
        methods: {

            getItems: function () {

                let self = this;

                axios.post('/search/autocomplete', {
                    phrase: self.searchPhrase
                }).then(function (httpResponse) {
                    self.findedItems = httpResponse.data.products;
                    self.hasMoreResults = httpResponse.data.hasMoreResults;
                    if (self.hasMoreResults) self.searchLink = httpResponse.data.hasMoreLink;
                });
            },

            search(value = '') {
                if(value.length){
                    window.location = this.$root.routes['home'] + '/search?phrase=' + value;
                }else if (this.searchPhrase) {
                    window.location = this.$root.routes['home'] + '/search?phrase=' + this.searchPhrase;
                } else {
                    window.location = this.$root.routes['home'] + '/search/';
                }
            }
        },
    }
</script>

<style scoped>

</style>