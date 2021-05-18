<template>
    <div class="table-list-container" v-if="Object.keys(information).length">
        <div class="flex flex--justify-space-between">
            <div>
                <div class="breadcrumbs">
                    <router-link class="breadcrumbs__link" :to="{name: 'dashboard'}">{{$root.translate.columns.home}}
                    </router-link>
                    -
                    <router-link class="breadcrumbs__link" :to="{name: 'informations'}">
                        {{$root.translate.menu.catalog.items.information}}
                    </router-link>
                </div>
                <h2 class="mainContent__heading mainContent__heading--inForm">
                    {{information.translates.find(translate => {return translate.locale == $root.adminLanguage}).name}}</h2>
            </div>
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="switcherStatus">
                            <div @click="information.status = false" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active switcherStatus__value--active_off': information.status === false}">
                                {{$root.translate.columns.disabled_short}}
                            </div>
                            <div @click="information.status = true" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': information.status === true}">
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
            </div>
            <div class="singleForm__content">
                <template v-if="tab === 'main'">
                    <div class="flex flex--justify-space-between">
                        <div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.name}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in information.translates">
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
                                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in information.translates">
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
                                               v-model.number="information.sort_order">
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
                                               v-model="information.slug">
                                    </div>
                                </div>
                            </div>

                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.showing}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="flex flex--align-center singleFormGroup__field">
                                    <div class="switcher">
                                        <check @click.native="information.in_top = !information.in_top" :checked="information.in_top === true"
                                               class="switcher__icon"></check>
                                        <span @click="information.in_top = !information.in_top"
                                              class="switcher__label">{{$root.translateWords('In header')}}</span>
                                    </div>
                                    <div class="switcher">
                                        <check @click.native="information.in_bottom = !information.in_bottom" :checked="information.in_bottom === true"
                                               class="switcher__icon"></check>
                                        <span @click="information.in_bottom = !information.in_bottom"
                                              class="switcher__label">{{$root.translateWords('In footer')}}</span>
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
                                        items_type="informations"
                                        :item="$root.encodeId('informations', information.id)"
                                        :data="information"
                                        :file_path="information.image"
                                        :thumb_path="information.filemanager_thumb"

                                ></upload-thumb>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else-if="tab === 'seo'">
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.title}}:
                        </div>
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in information.translates">
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
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in information.translates">
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
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in information.translates">
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
                        <div class="singleFormGroup__field inputWithTranslates" v-for="translate in information.translates">
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

        <widget-actions :store="isChangedInfo ? 'update': false" remove="deleteInformation" :foreign="information.href"></widget-actions>

    </div>
</template>

<script>

    let UploadThumb = require('./UploadThumbComponent').default;

    import VueCkeditor from 'vue-ckeditor2';


    export default {
        name: "Information",
        components: {
            VueCkeditor,
            UploadThumb,
        },
        data() {
            return {
                refreshing: false,
                information: {},
                savedOriginal: {},
                tab: 'main',
            }
        },
        computed: {
            isChangedInfo() {

                let current = this.$root.copy(this.information);

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

            axios.get('/admin/informations/' + this.$route.params.id + '/edit').then((httpResponse) => {
                this.information = httpResponse.data;

                this.savedOriginal = this.$root.copy(this.information);
            });

            this.debouncedStoreInformation = _.debounce(function(){
                self.update();
            }, 1000 * 60 * 2);

        },
        watch: {
            isChangedInfo: function (value) {
                if (value) {
                    //this.debouncedStoreInformation();
                }
            }
        },
        methods: {
            update() {

                this.refreshing = true;

                axios.put('/admin/informations/' + this.information.id, this.information).then(
                    httpResponse => {
                        this.$root.notify(httpResponse.data);

                        this.$set(this, 'savedOriginal', this.$root.copy(this.information));

                    }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            },
            deleteInformation() {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.refreshing = true;

                    axios.delete('/admin/informations/' + this.information.id).then(httpResponse => {
                        this.$router.push({name: "informations"});
                    });
                });
            },
            refreshOriginal(){
                this.$set(this, 'savedOriginal', this.$root.copy(this.information));
            }
        }
    }
</script>

<style scoped>

</style>