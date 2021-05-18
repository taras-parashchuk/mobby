<template>
    <div class="filter l-pos-r" id="filter">
        <template v-if="loaded">
            <div class="filter__head">
                <div class="js-m-container-for-sort"></div>
                <a @click.prevent="toggleMobileFilter" href="javascript:void(0)"
                   class="btn btn--primary filter__btn js-m-toogle-filter">
                    <span>{{$root.trans['filter.heading']}}</span>
                    <i class="icon sb-icon-filter-results-button"></i>
                </a>
            </div>
            <div v-if="filter_open || $root.window_width > 970" class="filter__content filter__content--open">

                <div class="filter__section" v-if="transformSelectedAttributes.length">
                    <div class="filter__title">
                        {{$root.trans['filter.checked.heading']}}
                    </div>
                    <template v-for="selectedAttribute in transformSelectedAttributes">
                        <span class="filter__subtitle text--lightblue">{{selectedAttribute.name}}:</span>
                        <div class="filter__selected">
                            <div v-for="value in selectedAttribute.values"
                                 class="filter__option filter__option--selected"
                                 @click="removeSelectedAttributeValue(selectedAttribute, value)">
                                <span class="title">{{value.name}}</span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="filter__section" v-if="isActivePriceDiagram">

                    <div data-toggle="popover-price">
                        <div class="filter__title">
                            {{$root.trans['filter.price.heading']}}
                        </div>
                        <div class="option-name">
                        <span class="hidden_price">
                            <span id="price-from">{{priceSlider.from}}</span>
                            <span id="price-to">{{priceSlider.to}}</span>
                        </span>
                        </div>

                        <div id="ocTopForm">
                            <div class="ocTopForm__input">
                                <span class="filterPrice__limit-text">{{$root.trans['filter.price.from']}}</span>
                                <div class="filterPrice__input"
                                     :class="$root.theme === 'beauty' ? 'text--darkGrey' : 'text--lightblue'">
                                    <input class="filterPrice__control"
                                           :class="$root.theme === 'beauty' ? 'text--darkGrey' : 'text--lightblue'"
                                           v-model="transformedPrices[0]"
                                           @input="updateActivePrices()" type="text">
                                    <span> {{$root.trans['common.currency_symbol']}}</span>
                                </div>
                            </div>
                            <div class="ocTopForm__input">
                                <span class="filterPrice__limit-text">{{$root.trans['filter.price.to']}}</span>
                                <div class="filterPrice__input"
                                     :class="$root.theme === 'beauty' ? 'text--darkGrey' : 'text--lightblue'">
                                    <input class="filterPrice__control"
                                           :class="$root.theme === 'beauty' ? 'text--darkGrey' : 'text--lightblue'"
                                           v-model="transformedPrices[1]"
                                           @input="updateActivePrices()" type="text">
                                    <span> {{$root.trans['common.currency_symbol']}}</span>
                                </div>
                            </div>
                        </div>

                        <v-slider :lazy="true" :min="priceSlider.from" :max="priceSlider.to" v-model="transformedPrices"
                                  @change="updateActivePrices()"></v-slider>

                    </div>
                </div>

                <div class="filter__section" v-for="(attribute, k) in transformedAttributes">
                    <div class="filter__title">
                        {{attribute.title}}
                        <a href="javascript:void(0)"
                           class="filter__toggle js-filter-toggle-options"
                           @click="changeVisibility(k)"
                           :class="isShowOptions(k) ? 'filter__toggle--open' : 'filter__toggle--close'"><span
                                class="icon"></span></a>
                    </div>

                    <div class="filter__options"
                         :class="isShowOptions(k) ? 'filter__options--close' : 'filter__options--open'">
                        <vue-scroll :ops="scrollOptions">
                            <div>
                                <template v-for="value in attribute.values">
                                    <label class="filter__option filterOptionInList"
                                           :class="{'filterOptionInList--selected': (selectedAttributes[attribute.slug] && selectedAttributes[attribute.slug].values.indexOf(value.slug) !== -1)}">
                                        <a class="filterOptionInList__link" :href="value.link"
                                           :class="{'filterOptionInList__link--selected': (selectedAttributes[attribute.slug] && selectedAttributes[attribute.slug].values.indexOf(value.slug) !== -1)}"
                                           @click.prevent="triggerSetAttributes(value.link_params)">
                                <span class="filterOptionInList__wrap">
                                    <span class="filterOptionInList__title">
                                        {{value.title}}
                                    </span>
                                </span>
                                        </a>
                                    </label>
                                </template>
                            </div>
                        </vue-scroll>
                        <!--
                        <template v-if="!k === 1">
                            <v-slider :lazy="true"
                                      v-model="attribute.test"
                                      :data="attribute.values.map(value => parseFloat(value.title)).sort((a,b) => {return a - b})"
                                      :min="27"
                                      :max="32"
                                      :interval="0.1"
                            :marks="true"></v-slider>
                        </template>
                        <template v-else>

                        </template>
                        -->
                    </div>
                </div>

            </div>
        </template>
        <template v-else>
            <div class="loading js-loading loading--show">
                <div class="loading__wrap"><span class="icon icon--spin sb-icon-loading"><span
                        class="path1"></span><span
                        class="path2"></span><span class="path3"></span><span
                        class="path4"></span><span class="path5"></span><span class="path6"></span><span
                        class="path7"></span><span class="path8"></span><span class="path9"></span><span
                        class="path10"></span><span class="path11"></span><span
                        class="path12"></span></span>
                </div>
            </div>
        </template>
    </div>
</template>

<script>

    import {copy, helpers} from '../app'

    export default {
        name: "catalogColumnFilter",
        props: ['categoryId', 'config', 'selectedAttributes', 'priceSlider', 'activePrices'],
        data() {
            return {
                filter_open: false,
                attributes: [],
                loaded: false,
                scrollOptions: {
                    vuescroll: {
                        mode: 'native',
                        detectResize: true,
                        sizeStrategy: 'number'
                    },
                    scrollPanel: {
                        initialScrollY: false,
                        initialScrollX: false,
                        scrollingX: false,
                        scrollingY: true,
                        speed: 300,
                        easing: undefined,
                        verticalNativeBarPos: 'right'
                    },
                    rail: {
                        background: '#e9e9eb',
                        opacity: 1,
                        size: '2px',
                        specifyBorderRadius: false,
                        gutterOfEnds: null,
                        gutterOfSide: '6px',
                        keepShow: false
                    },
                    bar: {
                        showDelay: 10000000,
                        onlyShowBarOnScroll: true,
                        keepShow: true,
                        background: '#f81a20',
                        opacity: 1,
                        hoverStyle: false,
                        specifyBorderRadius: false,
                        minSize: 0,
                        size: '2px',
                        disable: false
                    }
                },
            };
        },
        created() {

            let self = this;

            axios.get(this.config.api_get_info_link, {
                params: {
                    category_id: this.categoryId
                }
            }).then(function (response) {
                if (response.data) {

                    self.attributes = response.data;

                    self.loaded = true;

                }
            });

            helpers.change_pos_mob('.js-products-sort', '.js-products-filter-container', '.js-m-container-for-sort', 971);

        },
        computed: {
            transformSelectedAttributes() {
                let results = [];

                for (let attribute of this.attributes) {
                    if (this.selectedAttributes.hasOwnProperty(attribute.slug)) {

                        let result = {};

                        result = {
                            'name': attribute.translate.name,
                            'slug': attribute.slug,
                            'values': []
                        };

                        for (let value of attribute.values) {
                            if (this.selectedAttributes[attribute.slug].values.indexOf(value.slug) !== -1) {
                                result['values'].push(
                                    {
                                        'name': value.translate.value,
                                        'slug': value.slug
                                    }
                                );
                            }
                        }

                        results.push(result);
                    }
                }

                return results;
            },
            transformedAttributes() {
                let results = [];

                for (let attribute of this.attributes) {
                    let result = {};

                    result.title = attribute.translate.name;
                    result.slug = attribute.slug;
                    result.values = [];

                    if (attribute.hasOwnProperty('isVisible')) {
                        result.isVisible = attribute.isVisible;
                    }


                    for (let value of attribute.values) {

                        let newParams = copy(this.selectedAttributes);

                        if (this.activeAttributeValue(attribute.slug, value.slug)) {

                            newParams[attribute.slug]['values'].splice(newParams[attribute.slug]['values'].indexOf(value.slug), 1);

                            if (!newParams[attribute.slug]['values'].length) {
                                delete newParams[attribute.slug];
                            }

                        } else {

                            if (typeof newParams[attribute.slug] === 'undefined') {
                                newParams[attribute.slug] = {
                                    is_attribute: true,
                                    multiply: true,
                                    values: []
                                };

                                newParams[attribute.slug].values.push(value.slug);

                            } else {
                                newParams[attribute.slug]['values'].push(value.slug);
                            }

                        }

                        result['values'].push({
                            'title': value.translate.value,
                            'slug': value.slug,
                            'link': this.$root.makeLinkWithNewParams({
                                'attributes': newParams,
                                'page': 1
                            }),
                            'link_params': newParams || {}
                        });
                    }

                    results.push(result);
                }


                return results;
            },
            transformedPrices: {
                get() {
                    let results = [null, null];

                    if (this.activePrices) {
                        results[0] = +this.activePrices[0];
                        results[1] = +this.activePrices[1];
                    } else if (this.priceSlider) {
                        results[0] = +this.priceSlider.from;
                        results[1] = +this.priceSlider.to;
                    }

                    return results;
                },
                set(newValues) {
                    this.transformedPrices[0] = newValues[0];
                    this.transformedPrices[1] = newValues[1];
                }
            },
            isActivePriceDiagram() {
                return (this.config.price_diagram && this.transformedPrices[0] >= 0 && this.transformedPrices[1] && this.priceSlider.from >= 0 && this.priceSlider.to);
            },
            isShowOptions() {
                return (k) => {
                    if (this.attributes[k].hasOwnProperty('isVisible')) {
                        return this.attributes[k].isVisible;
                    } else {
                        return this.config.show_options_limit < (k + 1);
                    }

                };
            },
        },
        methods: {
            removeSelectedAttributeValue(attribute, value) {
                this.$emit('remove-attribute-param', {'attr_slug': attribute.slug, 'value_slug': value.slug});
            },
            changeAttributeValueStatus(attribute, value) {
                console.log(event);
                console.log(attribute);
                console.log(value);
            },
            activeAttributeValue(a_slug, v_slug) {
                return (this.selectedAttributes.hasOwnProperty(a_slug) && this.selectedAttributes[a_slug]['values'].indexOf(v_slug) !== -1);
            },
            triggerSetAttributes(params) {
                this.$emit('set-attributes', params, 1);
            },
            updateActivePrices() {
                if (!this.transformedPrices[0]) this.transformedPrices[0] = this.priceSlider.from;
                if (!this.transformedPrices[1]) this.transformedPrices[1] = this.priceSlider.to;

                this.$emit('set-prices', [this.transformedPrices[0], this.transformedPrices[1]]);
            },
            toggleMobileFilter() {
                this.filter_open = !this.filter_open;
            },
            changeVisibility(k) {
                if (this.attributes[k].hasOwnProperty('isVisible')) {
                    this.attributes[k].isVisible = !(this.attributes[k].isVisible);
                } else {
                    this.$set(this.attributes[k], 'isVisible', true);
                }
            }
        }

    }
</script>

<style scoped>

</style>
