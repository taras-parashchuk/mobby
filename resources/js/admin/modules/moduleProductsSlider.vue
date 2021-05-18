<template>

    <div>
        <div class="flex flex--justify-space-between">
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.name}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="text" class="input input--inForm"
                                   v-model="module.name">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="switcherStatus">
                            <div @click="module.status = false" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active switcherStatus__value--active_off': module.status === false}">
                                {{$root.translate.columns.disabled_short}}
                            </div>
                            <div @click="module.status = true" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': module.status === true}">
                                {{$root.translate.columns.enabled_short}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="">
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.name}}:
                </div>
                <div class="singleFormGroup__field inputWithTranslates" v-for="language in $root.languages">
                    <div class="flex flex--align-center">
                        {{language.name}}:
                        <input type="text" class="input input--inForm input--label_left"
                               v-model="module.decoded_setting.title[language.locale]">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Product assortment')}}:
                </div>
                <div class="singleFormGroup__field">
                    <v-select
                            :clearable="false"
                            :searchable="false"
                            :options="types"
                            v-model="module.decoded_setting.type"
                            class="input input--inForm"
                            :reduce="type => type.value"
                            label="value">
                        <template slot="selected-option" slot-scope="option">
                            {{ option.name }}
                        </template>
                        <template v-slot:option="option">
                            {{ option.name }}
                        </template>
                    </v-select>
                </div>
            </div>
            <div class="singleFormGroup" v-if="module.decoded_setting.type === 'custom'">
                <div class="singleFormGroup__field">

                    <div class="flex flex--align-center searchResultsItem searchResultsItem--related"
                         v-for="(selectedProduct, selected_index) in module.decoded_setting.product">
                        <div class="flex flex--align-center">
                            <img class="searchResultsItem__img"
                                 :src="selectedProduct.image" alt="" style="max-width: 41px">
                            <div class="flex flex--column flex--justify-space-between">
                                <div class="searchResultsItem__name">{{selectedProduct.name}}</div>
                                <div class="searchResultsItem__sku">
                                    {{$root.translate.columns.sku}}: {{selectedProduct.sku}}
                                </div>
                            </div>
                        </div>
                        <div>
                            <icon @click.native.stop="module.decoded_setting.product.splice(selected_index, 1)" icon="delete"
                                  class="icon searchResultsItem__clear"></icon>
                        </div>
                    </div>

                    <v-select
                            @search="onSearchProducts"
                            :components="{Deselect, OpenIndicator}"
                            :multiple="true"
                            :clearable="true"
                            :searchable="true"
                            :options="products"
                            v-model="module.decoded_setting.product"
                            class="input input--inForm vs--products vs--multiply"
                            label="id">

                        <template #search="{attributes, events}">

                            <div class="flex vs__searchContainer">

                                <div class="vs__searchIcon">
                                    +
                                </div>

                                <input class="vs__search"
                                       :placeholder="$root.translateWords('Add')"
                                       v-bind="attributes"
                                       v-on="events"/>
                            </div>
                        </template>

                        <template v-slot:option="option">
                            <div class="flex flex--align-center searchResultsItem searchResultsItem--product">
                                <img class="searchResultsItem__img"
                                     :src="option.image" style="max-width: 39px" alt="">
                                <div class="flex flex--column flex--justify-space-between">
                                    <div class="searchResultsItem__name">{{option.name}}</div>
                                    <div class="searchResultsItem__sku">
                                        {{$root.translate.columns.sku}}: {{option.sku}}
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-slot:selected-option-container>
                            <slot name="selected-option">
                                <div></div>
                            </slot>
                        </template>

                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>

                    </v-select>
                </div>
            </div>
            <div v-else class="singleFormGroup">
                <div class="singleFormGroup__field">
                    <v-select
                            :clearable="false"
                            :searchable="false"
                            :options="productDifference"
                            v-model="module.decoded_setting.product"
                            class="input input--inForm"
                            :reduce="type => type.value"
                            label="value">
                        <template slot="selected-option" slot-scope="option">
                            {{ option.name }}
                        </template>
                        <template v-slot:option="option">
                            {{ option.name }}
                        </template>
                    </v-select>
                </div>
            </div>
            <div class="singleFormGroup singleFormGroup--inlineSet">
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translateWords('Maximum count of items')}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.limit">
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translateWords('Visible count of items')}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.visible">
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup singleFormGroup--inlineSet">
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.width}}(px):
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.width">
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.height}}(px):
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.height">
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.autostart}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.autoplay = true"
                                   :checked="module.decoded_setting.autoplay === true"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.autoplay = true"
                                  class="switcher__label">{{$root.translate.columns.yes}}</span>
                        </div>
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.autoplay = false"
                                   :checked="module.decoded_setting.autoplay === false"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.autoplay = false" class="switcher__label">{{$root.translate.columns.no}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup" v-if="module.decoded_setting.autoplay">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Autostart after time in ms(1000ms = 1s)')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <input type="number" class="input input--inForm"
                               v-model.number="module.decoded_setting.autoplayTimeout">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Speed flipping in ms(1000ms = 1s)')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <input type="number" class="input input--inForm"
                               v-model.number="module.decoded_setting.smartSpeed">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Show navigation?')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.nav = true"
                                   :checked="module.decoded_setting.nav === true"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.nav = true"
                                  class="switcher__label">{{$root.translate.columns.yes}}</span>
                        </div>
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.nav = false"
                                   :checked="module.decoded_setting.nav === false"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.nav = false" class="switcher__label">{{$root.translate.columns.no}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Show pagination?')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.pagination = true"
                                   :checked="module.decoded_setting.pagination === true"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.pagination = true"
                                  class="switcher__label">{{$root.translate.columns.yes}}</span>
                        </div>
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.pagination = false"
                                   :checked="module.decoded_setting.pagination === false"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.pagination = false" class="switcher__label">{{$root.translate.columns.no}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>

    export default {
        name: "moduleProductSlider",
        props: ['moduleInfo', 'validationProp', 'Deselect', 'OpenIndicator', 'products', 'onSearchProducts'],
        data() {
            return {
                module: {
                    decoded_setting: {}
                },
                types: [
                    {
                        value: 'custom',
                        name: this.$root.translate.columns.assortment.custom
                    },
                    {
                        value: 'auto',
                        name: this.$root.translate.columns.assortment.system_auto
                    }
                ],
                productDifference: [
                    {
                        name: this.$root.translate.columns.assortment.list.special,
                        value: 'special'
                    },
                    {
                        name: this.$root.translate.columns.assortment.list.latest,
                        value: 'latest'
                    },
                    {
                        name: this.$root.translate.columns.assortment.list.bestseller,
                        value: 'bestseller'
                    },
                    {
                        name: this.$root.translate.columns.assortment.list.visited,
                        value: 'visited'
                    },
                    {
                        name: this.$root.translate.columns.assortment.list.related,
                        value: 'related'
                    },
                ]
            }
        },
        created() {

            this.module = this.moduleInfo;

            if (!Object(this.module.decoded_setting).hasOwnProperty('title')) {

                this.$set(this.module.decoded_setting, 'title', {});

                this.$root.languages.forEach((language) => {
                    let locale = language.locale;

                    this.$set(this.module.decoded_setting.title, locale, '');

                });
            }

            if (!Object(this.module.decoded_setting).hasOwnProperty('type')) {

                this.$set(this.module.decoded_setting, 'type', 'custom');

                this.$set(this.module.decoded_setting, 'product', []);
            } else if (this.module.decoded_setting.type === 'custom') {
                this.autocompletedProducts = this.module.decoded_setting.product;
            }

            if(!this.module.id){
                this.$set(this.module.decoded_setting, 'pagination', false);
                this.$set(this.module.decoded_setting, 'nav', false);
                this.$set(this.module.decoded_setting, 'autoplay', false);
            }

        },
        watch: {
            'module.decoded_setting.type': function (newVal) {
                switch (newVal) {
                    case 'custom':
                        if (!Array.isArray(this.module.decoded_setting.product)) {
                            this.$set(this.module.decoded_setting, 'product', []);
                        }
                        break;
                    case 'auto':
                        if (typeof this.module.decoded_setting.product !== 'string') {
                            this.$set(this.module.decoded_setting, 'product', '');
                        }
                        break;
                }
            }
        }
    }
</script>

<style scoped>

</style>