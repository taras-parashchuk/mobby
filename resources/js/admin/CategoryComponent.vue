<template>
    <div class="table-list-container" v-if="Object.keys(category).length">
        <div class="flex flex--justify-space-between">
            <div>
                <div class="breadcrumbs">
                    <router-link class="breadcrumbs__link" :to="{name: 'dashboard'}">{{$root.translate.columns.home}}
                    </router-link>
                    -
                    <router-link class="breadcrumbs__link" :to="{name: 'categories'}">
                        {{$root.translate.menu.catalog.items.categories}}
                    </router-link>
                </div>
                <h2 class="mainContent__heading mainContent__heading--inProduct">
                    {{category.translates.find(translate => {return translate.locale ==
                    $root.adminLanguage}).name}}</h2>
            </div>
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="switcherStatus">
                            <div @click="category.status = false" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': category.status === false}">
                                {{$root.translate.columns.disabled_short}}
                            </div>
                            <div @click="category.status = true" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': category.status === true}">
                                {{$root.translate.columns.enabled_short}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="singleForm">
            <div class="tabs">
                <a @click="tab = 'main'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'main'}">
                    {{$root.translate.columns.main}}
                </a>
                <a @click="tab = 'seo'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'seo'}">
                    Seo
                </a>
                <a @click="tab = 'filtering'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'filtering'}">
                    Фильтрация
                </a>
            </div>
            <div class="singleForm__content">
                <template v-if="tab === 'main'">
                    <div class="flex flex--justify-space-between">
                        <div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.name}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in category.translates">
                                    <div class="flex flex--align-center">
                                        {{$root.languages.find((language) => {return language.locale ===
                                        translate.locale}).name}}:
                                        <input type="text" class="input input--inForm input--label_left"
                                               v-model="translate.name">
                                    </div>
                                </div>
                            </div>

                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    Название для маркетплейсов:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in category.translates">
                                    <div class="flex flex--align-center">
                                        {{$root.languages.find((language) => {return language.locale ===
                                        translate.locale}).name}}:
                                        <input type="text" class="input input--inForm input--label_left"
                                               v-model="translate.marketplace_name">
                                    </div>
                                </div>
                            </div>

                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.parent_category}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <v-select
                                                @search="onSearchCategories"
                                                :clearable="true"
                                                :searchable="true"
                                                :options="categories"
                                                v-model="category.parent_id"
                                                class="input input--inForm"
                                                :reduce="category => category.id"
                                                label="name">
                                            <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                        </v-select>
                                    </div>
                                </div>
                            </div>

                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.slug}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <input type="text" class="input input--inForm"
                                               v-model="category.slug">
                                    </div>
                                </div>
                            </div>

                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns['sort-order']}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <input type="number" class="input input--inForm"
                                               v-model.number="category.sort_order">
                                    </div>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.description}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in category.translates">
                                    <div class="flex">
                                <span class="mr--8">
                                    {{$root.languages.find((language) => {return language.locale == translate.locale}).name}}:
                                </span>
                                        <vue-ckeditor :config="$root.editorConfig" v-model="translate.description"></vue-ckeditor>
                                    </div>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.source_id}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <input type="text" class="input input--inForm"
                                               v-model="category.extra_1">
                                    </div>
                                </div>
                            </div>

                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.source_name}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <input type="text" class="input input--inForm"
                                               v-model="category.extra_2">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="null">
                            <div class="imagesSet">
                                <div class="imagesSet__title">
                                    {{$root.translateWords('Product images')}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <draggable
                                        v-model="product.images"
                                        v-bind="dragOptions"
                                        @start="drag = true"
                                        @end="drag = false"
                                        tag="transition-group"
                                        class="imagesSet__items"
                                        :componentData="{props: {
                                            type: 'transition',
                                            name: !drag ? 'flip-list' : null
                                        }}">

                                    <div class=""
                                         v-for="(element, number) in product.images"
                                         :key="number">
                                        <upload-thumb
                                                items_type="products"
                                                :item="$root.encodeId('products',product.id)"
                                                :ref="'additionalThumb'"
                                                :data="element"
                                                attribute-src-name="src"
                                                :file_path="element.src"
                                                :thumb_path="element.filemanager_thumb"
                                        ></upload-thumb>
                                        <span v-if="number !== 0" class="thumb__remove"
                                              @click="product.images.splice(number, 1)">
                                                    <mdb-icon color="danger" icon="trash" class="mr-1"/>
                                                </span>
                                    </div>

                                    <div @click="openUploadWindow" class="imagesSet__new" slot="footer" key="footer">
                                        <icon icon="plus" class="icon"></icon>
                                    </div>

                                </draggable>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else-if="tab === 'seo'">
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns['meta-title']}}:
                        </div>
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in category.translates">
                            <div class="flex flex--align-center">
                                {{$root.languages.find((language) => {return language.locale ===
                                translate.locale}).name}}:
                                <input type="text" class="input input--inForm input--label_left"
                                       v-model="translate.meta_title">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns['meta-description']}}:
                        </div>
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in category.translates">
                            <div class="flex flex--align-center">
                                {{$root.languages.find((language) => {return language.locale ===
                                translate.locale}).name}}:
                                <input type="text" class="input input--inForm input--label_left"
                                       v-model="translate.meta_description">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns['meta-keywords']}}:
                        </div>
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in category.translates">
                            <div class="flex flex--align-center">
                                {{$root.languages.find((language) => {return language.locale ===
                                translate.locale}).name}}:
                                <input type="text" class="input input--inForm input--label_left"
                                       v-model="translate.meta_keywords">
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else-if="tab === 'filtering'">
                    <table class="table">
                        <thead>
                        <tr class="table__row">
                            <td class="table__heading">
                                Характеристика
                            </td>
                            <td class="table__heading">
                                Статус
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="table__row" v-for="attribute in category.attributes">
                            <td class="table__value">
                                {{attribute.title}}
                            </td>
                            <td class="table__value">
                                <div class="switcherStatus">
                                    <div @click="attribute.status = false" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': attribute.status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="attribute.status = true" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': attribute.status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </template>
            </div>
        </div>
        <widget-actions :store="isChangedInfo ? 'applyForm': false" remove="deleteCategory" :foreign="category.href"></widget-actions>
    </div>
</template>

<script>

    let UploadThumb = require('./UploadThumbComponent').default;
    import VueCkeditor from 'vue-ckeditor2';

    export default {
        name: "CategoryComponent",
        components: {
            VueCkeditor,
            UploadThumb,
        },
        data() {
            return {
                tab: 'main',
                category: {},
                savedOriginal: {},
                categories: [],
                refreshing: false
            }
        },
        created() {
            let self = this;

            this.onSearchCategories();

            if (this.$route.params.id) {
                axios.get('/admin/categories/' + this.$route.params.id + '/edit')
                    .then(httpResponse => {
                        self.category = httpResponse.data.category;

                        let translates = {};

                        for (let translate of self.category.translates) {
                            translates[translate.locale] = translate;
                        }

                        this.$set(this, 'savedOriginal', this.$root.copy(this.category));

                    });

                this.debouncedStoreCategory = _.debounce(function(){
                    self.applyForm();
                }, 1000 * 60 * 2);
            }
        },
        computed: {
            isChangedInfo() {

                let current = this.$root.copy(this.category);

                if(Object.keys(current).length){

                    delete current.refreshing;

                    current.translates.forEach(translate => translate.description = translate.description.trim());

                    let saved = this.$root.copy(this.savedOriginal);

                    saved.translates.forEach(translate => translate.description = translate.description.trim());

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        methods: {
            applyForm() {
                let self = this;

                let category = this.category;

                let data = JSON.stringify(category);

                this.refreshing = true;

                axios.put('/admin/categories/' + category.id, data)
                    .then(httpResponse => {
                        self.$set(self.category, 'id', httpResponse.data.id);
                        self.$set(self.category, 'slug', httpResponse.data.slug);

                        this.$set(this, 'savedOriginal', this.$root.copy(this.category));

                        this.$root.notify(httpResponse.data);

                    }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });

            },
            onSearchCategories(phrase = null, loading = null) {
                if (loading !== null) loading(true);
                this.getCategories(phrase, this, loading);
            },
            getCategories: _.debounce((phrase, self, loading) => {
                axios.get('/admin/categories/', {
                    params: {
                        phrase: phrase,
                        exclude_id: self.$route.params.id
                    }
                }).then(httpResponse => {
                    self.$set(self, 'categories', httpResponse.data.categories);
                    if (loading !== null) loading(false);
                });
            }, 500),
            deleteCategory() {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.refreshing = true;

                    axios.delete('/admin/categories/' + this.category.id).then(httpResponse => {
                        this.$router.push({name: "categories"});
                    });
                });
            },
            refreshOriginal(){
                this.$set(this, 'savedOriginal', this.$root.copy(this.category));
            }
        },
        watch: {
            isChangedInfo: function (value) {
                if (value) {
                    //this.debouncedStoreCategory();
                }
            }
        },
    }
</script>

<style>
    .nav-tabs {
        margin-bottom: 20px;
    }

    .container {
        position: relative;
    }

    .placement {
        position: absolute;
        right: 0;
        z-index: 2;
    }

    .md-tab-group {
        height: 100%;
    }

    .tab-pane {
        height: 100% !important;
    }
</style>