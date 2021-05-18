<template>

    <div class="table-list-container" v-if="Object.keys(article).length">
        <div class="flex flex--justify-space-between">
            <div>
                <div class="breadcrumbs">
                    <router-link class="breadcrumbs__link" :to="{name: 'dashboard'}">{{$root.translate.columns.home}}
                    </router-link>
                    -
                    <router-link class="breadcrumbs__link" :to="{name: 'articles'}">
                        {{$root.translate.menu.catalog.items.articles}}
                    </router-link>
                </div>
                <h2 class="mainContent__heading mainContent__heading--inForm">
                    {{article.translates.find(translate => {return translate.locale == $root.adminLanguage}).name}}</h2>
            </div>
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="switcherStatus">
                            <div @click="article.status = false" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active switcherStatus__value--active_off': article.status === false}">
                                {{$root.translate.columns.disabled_short}}
                            </div>
                            <div @click="article.status = true" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': article.status === true}">
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
                <a @click="tab = 'relations'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'relations'}">
                    {{$root.translate.columns.relations}}
                </a>
                <a @click="tab = 'seo'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'seo'}">
                    Seo
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
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in article.translates">
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
                                    {{$root.translate.columns.description}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in article.translates">
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
                                    {{$root.translate.columns['sort-order']}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <input type="number" class="input input--inForm"
                                               v-model.number="article.sort_order">
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
                                               v-model="article.slug">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div>
                            <div class="imagesSet">
                                <div class="imagesSet__title">
                                    {{$root.translate.columns.image}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <upload-thumb
                                        :is_tmp="false"
                                        items_type="articles"
                                        :item="$root.encodeId('articles', article.id)"
                                        :data="article"
                                        :file_path="article.image"
                                        :thumb_path="article.filemanager_thumb"

                                ></upload-thumb>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else-if="tab === 'relations'">

                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns['related_articles']}}
                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <v-select
                                        @search="onSearchArticles"
                                        :components="{Deselect, OpenIndicator}"
                                        :multiple="true"
                                        :clearable="true"
                                        :searchable="true"
                                        :options="articles"
                                        v-model="article.relateds"
                                        class="input input--inForm vs--multiply"
                                        label="name">
                                    <template #search="{attributes, events}">
                                        <input
                                                class="vs__search"
                                                :placeholder="$root.translateWords('Add article')"
                                                v-bind="attributes"
                                                v-on="events"
                                        />
                                    </template>
                                    <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                </v-select>
                            </div>
                        </div>
                    </div>

                </template>
                <template v-else-if="tab === 'seo'">
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.title}}:
                        </div>
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in article.translates">
                            <div class="flex flex--align-center">
                                {{$root.languages.find((language) => {return language.locale ===
                                translate.locale}).name}}:
                                <input type="text" class="input input--inForm input--label_left"
                                       v-model="translate.meta_h1">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns['meta-title']}}:
                        </div>
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in article.translates">
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
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in article.translates">
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
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in article.translates">
                            <div class="flex flex--align-center">
                                {{$root.languages.find((language) => {return language.locale ===
                                translate.locale}).name}}:
                                <input type="text" class="input input--inForm input--label_left"
                                       v-model="translate.meta_keywords">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <widget-actions :store="isChangedInfo ? 'update': false" remove="deleteArticle" :foreign="article.href"></widget-actions>

    </div>

</template>

<script>

    let UploadThumb = require('./UploadThumbComponent').default;
    import VueCkeditor from 'vue-ckeditor2';


    export default {
        name: "Article",
        components: {
            UploadThumb,
            VueCkeditor
        },
        data() {
            return {
                refreshing: false,
                article: {},
                articles: [],
                savedOriginal: {},
                tab: 'main',
                Deselect: {
                    render: createElement => createElement('icon', {
                        class: 'icon',
                        props: {
                            icon: 'error'
                        }
                    }),
                },
                OpenIndicator: {
                    render: createElement => createElement('span', ''),
                },
            }
        },
        computed: {
            isChangedInfo() {

                let current = this.$root.copy(this.article);

                if(Object.keys(current).length){

                    current.translates.forEach(translate => translate.description = translate.description.trim());

                    delete current.refreshing;

                    let saved = this.$root.copy(this.savedOriginal);

                    saved.translates.forEach(translate => translate.description = translate.description.trim());

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            },
            languageLinks() {
                let languages = [];

                for (let language of this.$root.languages) {
                    languages.push({
                        text: language.name,
                        locale: language.locale
                    });
                }

                return languages;
            },
        },
        created() {

            let self = this;

            axios.get('/admin/articles/' + this.$route.params.id + '/edit').then((httpResponse) => {
                this.article = httpResponse.data;

                this.$set(this, 'savedOriginal', this.$root.copy(this.article));
            });

            this.debouncedStoreArticle = _.debounce(function(){
                self.update();
            }, 1000 * 60 * 2);

            this.onSearchArticles();

        },
        watch: {
            isChangedInfo: function (value) {
                if (value) {
                    //this.debouncedStoreArticle();
                }
            }
        },
        methods: {
            update() {

                this.refreshing = true;

                axios.put('/admin/articles/' + this.article.id, this.article).then(
                    httpResponse => {
                        this.$root.notify(httpResponse.data);

                        this.$set(this, 'savedOriginal', this.$root.copy(this.article));

                    }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            },
            onArticlesShowingSelect(items) {
                this.article.relateds = items;
            },
            deleteArticle() {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.refreshing = true;

                    axios.delete('/admin/articles/' + this.article.id).then(httpResponse => {
                        this.$router.push({name: "articles"});
                    });
                });
            },
            onSearchArticles(phrase = null, loading = null) {
                if (loading !== null) loading(true);
                this.getArticles(phrase, this, loading);
            },
            getArticles: _.debounce((phrase, self, loading) => {
                axios.get('/admin/articles', {
                    params: {
                        phrase: phrase,
                        autocomplete: true,
                        exclude: self.$route.params.id
                    }
                }).then(httpResponse => {
                    self.$set(self, 'articles', httpResponse.data.articles);
                    if (loading !== null) loading(false);
                });
            }, 500),
            refreshOriginal(){
                this.$set(this, 'savedOriginal', this.$root.copy(this.article));
            }
        }
    }
</script>

<style scoped>

</style>