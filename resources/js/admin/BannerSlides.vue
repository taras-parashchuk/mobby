<template>

    <div class="table-list-container" v-if="Object.keys(banner).length">
        <div class="flex flex--justify-space-between">
            <div>
                <div class="breadcrumbs">
                    <router-link class="breadcrumbs__link" :to="{name: 'dashboard'}">{{$root.translate.columns.home}}
                    </router-link>
                    -
                    <router-link class="breadcrumbs__link" :to="{name: 'banners'}">
                        {{$root.translate.menu['design-modules'].items.banners}}
                    </router-link>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.name}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="text" class="input input--inForm"
                                   v-model="banner.name">
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
                            <div @click="banner.status = false" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active switcherStatus__value--active_off': banner.status === false}">
                                {{$root.translate.columns.disabled_short}}
                            </div>
                            <div @click="banner.status = true" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': banner.status === true}">
                                {{$root.translate.columns.enabled_short}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="singleForm">
            <div class="tabs">
                <a v-for="language in languageLinks" href="javascript:void(0)" @click="tab = language.locale" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === language.locale}">
                    {{language.text}}
                </a>
            </div>
            <div class="singleForm__content">
                <template v-for="language in languageLinks" v-if="tab === language.locale">
                    <template v-if="isLoaded">
                        <div class="listData listData--noBg" v-if="slides.length">
                            <vue-good-table
                                    :columns="columns"
                                    :rows="slides(language.locale)"
                                    styleClass="table"
                                    row-style-class="table__row">
                                <template slot="table-row" slot-scope="props">
                                    <template v-if="props.column.field === 'image'">
                                        <upload-thumb
                                                items_type="banners"
                                                :item="$root.encodeId('banners', banner.id)"
                                                :data="slides(language.locale)[props.index]"
                                                :file_path="slides(language.locale)[props.index].image"
                                                :thumb_path="slides(language.locale)[props.index].filemanager_thumb"
                                        ></upload-thumb>
                                    </template>
                                    <template v-else-if="props.column.field === 'link'">
                                        <div class="flex flex--align-center">
                                            <input type="text" class="input"
                                                   v-model="slides(language.locale)[props.index].link">
                                        </div>
                                    </template>
                                    <template v-else-if="props.column.field === 'title'">
                                        <div class="flex flex--align-center">
                                            <input type="text" class="input"
                                                   v-model="slides(language.locale)[props.index].title">
                                        </div>
                                    </template>
                                    <template v-else-if="props.column.field === 'sort_order'">
                                        <div class="flex flex--align-center">
                                            <input type="text" class="input"
                                                   v-model="slides(language.locale)[props.index].sort_order">
                                        </div>
                                    </template>
                                    <template v-else-if="props.column.field === 'actions'">
                                        <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                           href="javascript:void(0)" @click.stop="deleteSlide(slides(language.locale)[props.index].id)">
                                            <icon icon="delete" class="icon"></icon>
                                        </a>
                                    </template>
                                </template>

                            </vue-good-table>

                        </div>
                    </template>
                </template>
            </div>
        </div>

        <widget-actions  :trans="{add: $root.translateWords('Create slide')}" add="add" :store="isChangedInfo ? 'store': false" remove="deleteBanner"></widget-actions>

    </div>

</template>

<script>

    let UploadThumb = require('./UploadThumbComponent').default;

    export default {
        name: "BannerSlides",
        components: {
            UploadThumb
        },
        data() {
            return {
                tab: null,
                refreshing: false,
                isLoaded: false,
                banner: [],
                columns: [
                    {
                        label: this.$root.translate.columns.image_short,
                        field: 'image',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.link,
                        field: 'link',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.title,
                        field: 'title',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns['sort-order'],
                        field: 'sort_order',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.actions,
                        field: 'actions',
                        thClass: 'table__heading table__heading--text_right',
                        tdClass: 'table__value table__value--text_right',
                        sortable: false,
                    },
                ],
                savedOriginal: {}
            };
        },
        created() {
            axios.get('/admin/banners/' + this.$route.params.id + '/edit').then(httpResponse => {
                this.banner = httpResponse.data;

                this.$set(this, 'savedOriginal', this.$root.copy(this.banner));

                this.isLoaded = true;
            });

            if(this.tab === null){
                this.tab = this.$root.adminLanguage;
            }

            let self = this;

            this.debouncedStoreBanner = _.debounce(function(){
                self.update();
            }, 1000 * 60 * 2);

        },
        computed: {
            isChangedInfo() {

                let current = this.$root.copy(this.banner);

                delete current.refreshing;

                let saved = this.$root.copy(this.savedOriginal);

                delete saved.refreshing;

                return JSON.stringify(current) !== JSON.stringify(saved);

            },
            slides() {
                return (locale) => {
                    return this.banner.slides.filter(slide => {
                        return slide.locale === locale
                    });
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
        watch: {
            isChangedInfo: function (value) {
                if (value) {
                    this.debouncedStoreBanner();
                }
            }
        },
        methods: {
            add() {
                this.banner.slides.push({
                    id: 'tmp-' + Math.random(100000, 10000000),
                    sort_order: 0,
                    locale: this.tab,
                    title: '',
                    link: '',
                    image: '',
                });
            },
            deleteSlide(id) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.banner.slides.splice(this.banner.slides.findIndex(slide => {return slide.id === id}), 1);
                });
            },
            store() {

                this.refreshing = true;

                axios.put('/admin/banners/' + this.banner.id, this.banner).then(
                    httpResponse => {
                        this.$root.notify(httpResponse.data);

                        this.$set(this, 'savedOriginal', this.$root.copy(this.banner));

                    }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            },
            deleteBanner() {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.refreshing = true;

                    axios.delete('/admin/banners/' + this.banner.id).then(httpResponse => {
                        this.$router.push({name: "banners"});
                    });
                });
            }
        }
    }
</script>

<style scoped>

</style>