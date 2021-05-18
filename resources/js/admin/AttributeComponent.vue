<template>

    <div class="table-list-container" v-if="Object.keys(attribute).length">
        <div class="flex flex--justify-space-between">
            <div>
                <div class="breadcrumbs">
                    <router-link class="breadcrumbs__link" :to="{name: 'dashboard'}">{{$root.translate.columns.home}}
                    </router-link>
                    -
                    <router-link class="breadcrumbs__link" :to="{name: 'attributes'}">
                        {{$root.translate.menu.catalog.items.attributes}}
                    </router-link>
                </div>
                <h2 class="mainContent__heading mainContent__heading--inForm">
                    {{attribute.translates.find(translate => {return translate.locale ==
                    $root.adminLanguage}).name}}</h2>
            </div>
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
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
                <a @click="tab = 'values'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'values'}">
                    {{$root.translate.columns.value}}
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
                                <div class="singleFormGroup__field inputWithTranslates"
                                     v-for="translate in attribute.translates">
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
                                    {{$root.translate.columns.postfix_value}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates"
                                     v-for="translate in attribute.translates">
                                    <div class="flex flex--align-center">
                                        {{$root.languages.find((language) => {return language.locale ===
                                        translate.locale}).name}}:
                                        <input type="text" class="input input--inForm input--label_left"
                                               v-model="translate.postfix">
                                    </div>
                                </div>
                            </div>

                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.summary}}:
                                </div>
                                <div class="singleFormGroup__field inputWithTranslates"
                                     v-for="translate in attribute.translates">
                                    <div class="flex flex--align-center">
                                        {{$root.languages.find((language) => {return language.locale ===
                                        translate.locale}).name}}:
                                        <input type="text" class="input input--inForm input--label_left"
                                               v-model="translate.summary">
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
                                               v-model.number="attribute.sort_order">
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
                                               v-model="attribute.slug">
                                    </div>
                                </div>
                            </div>

                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translateWords('Attachment')}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <v-select
                                                :clearable="true"
                                                :searchable="true"
                                                :options="informations"
                                                v-model="attribute.main_info_id"
                                                class="input input--inForm"
                                                :reduce="information => information.id"
                                                label="name">
                                            <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                        </v-select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>
                <template v-else-if="tab === 'values'">
                    <template v-if="isLoadedValues">
                        <div class="listData listData--noBg" v-if="attribute.values.length">
                            <vue-good-table
                                    :columns="columns"
                                    :rows="attribute.values"
                                    styleClass="table"
                                    row-style-class="table__row">
                                <template slot="table-row" slot-scope="props">

                                    <template v-if="props.column.field === 'name'">
                                        <div v-for="translate in attribute.values[props.index].translates"
                                             class="inputWithTranslates">
                                            <div class="flex flex--align-center">
                                                {{$root.languages.find((language) => {return language.locale ===
                                                translate.locale}).name}}:
                                                <input type="text" class="input input--label_left"
                                                       v-model="translate.value">
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else-if="props.column.field === 'sort_order'">
                                        <div class="flex flex--align-center">
                                            <input type="text" class="input input--sort-order"
                                                   v-model.number="attribute.values[props.index].sort_order">
                                        </div>
                                    </template>
                                    <template v-else-if="props.column.field === 'slug'">
                                        <div class="flex flex--align-center">
                                            <input type="text" class="input input--sort-order"
                                                   v-model="attribute.values[props.index].slug">
                                        </div>
                                    </template>
                                    <template v-else-if="props.column.field === 'status'">
                                        <div class="flex flex--align-center">
                                            <div class="switcherStatus">
                                                <div @click="attribute.values[props.index].status = false"
                                                     class="switcherStatus__value"
                                                     :class="{'switcherStatus__value--active switcherStatus__value--active_off': attribute.values[props.index].status === false}">
                                                    {{$root.translate.columns.disabled_short}}
                                                </div>
                                                <div @click="attribute.values[props.index].status = true"
                                                     class="switcherStatus__value"
                                                     :class="{'switcherStatus__value--active': attribute.values[props.index].status === true}">
                                                    {{$root.translate.columns.enabled_short}}
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else-if="props.column.field === 'actions'">
                                        <template v-if="attribute.values[props.index].refreshing">
                                            <font-awesome-icon class="text-warning" icon="circle-notch"
                                                               spin></font-awesome-icon>
                                        </template>
                                        <template v-else>
                                            <a href="javascript:void(0)"
                                               @click.stop="showModalAttributeValue(props.index)">
                                                click here
                                            </a>
                                            <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                               href="javascript:void(0)"
                                               @click.stop="deleteAttributeValue(props.index)">
                                                <icon icon="delete" class="icon"></icon>
                                            </a>
                                        </template>
                                    </template>
                                    <span v-else>{{props.formattedRow[props.column.field]}}</span>
                                </template>

                            </vue-good-table>

                        </div>
                    </template>
                </template>
            </div>
        </div>

        <modal name="attribute-value-modal" width="70%"
               :title="$root.translateWords('Extra settings for attribute value')">
            <template v-slot>
                <div class="flex flex--align-center mb--16">
                    <div class="switcher">
                        <check @click.native="changePreferStyleForAttributeValue('image')"
                               :checked="modalAttributeValue.prefer_style === 'image'"
                               class="switcher__icon"></check>
                        <span @click="changePreferStyleForAttributeValue('image')"
                              class="switcher__label">{{$root.translateWords('Use image')}}</span>
                    </div>
                    <div class="switcher">
                        <check @click.native="changePreferStyleForAttributeValue('color')"
                               :checked="modalAttributeValue.prefer_style === 'color'"
                               class="switcher__icon"></check>
                        <span @click="changePreferStyleForAttributeValue('color')"
                              class="switcher__label">{{$root.translateWords('Use color code')}}</span>
                    </div>
                </div>
                <div class="flex">
                    <upload-thumb
                            v-if="modalAttributeValue.prefer_style === 'image'"
                            items_type="attributes"
                            :item="$root.encodeId('attributes', attribute.id)"
                            :data="modalAttributeValue"
                            :file_path="modalAttributeValue.image"
                            :thumb_path="modalAttributeValue.filemanager_thumb"
                            @remove="modalAttributeValue.image = ''"
                    ></upload-thumb>
                    <chrome-picker v-else-if="modalAttributeValue.prefer_style === 'color'"
                                   :value="modalAttributeValue.color"
                                   :disable-alpha="true"
                        @input="colorObj => {modalAttributeValue.color = colorObj.hex}"/>
                </div>
            </template>
        </modal>

        <widget-actions :trans="{add: $root.translateWords('Create attribute value')}"
                        :add="tab === 'values'  ? 'addAttributeValue' : false"
                        :store="isChangedInfo ? 'applyForm': false" remove="deleteAttribute"></widget-actions>

    </div>

</template>

<script>

    let UploadThumb = require('./UploadThumbComponent').default;

    import {Chrome} from 'vue-color';

    export default {
        name: "AttributeComponent",
        components: {
            UploadThumb,
            'chrome-picker': Chrome,
        },
        data() {
            return {
                tab: 'main',
                savedOriginal: {},
                savedOriginalValues: {},
                isLoadedValues: false,
                refreshing: false,
                attribute: {},
                categories: [],
                informations: [],
                columns: [
                    {
                        label: this.$root.translate.columns.name,
                        field: 'name',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.slug,
                        field: 'slug',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns['sort-order'],
                        field: 'sort_order',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.status,
                        field: 'status',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.actions,
                        field: 'actions',
                        thClass: 'table__heading table__heading--text_right',
                        tdClass: 'table__value table__value--text_right',
                        sortable: false,
                    },
                ],
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
                modalAttributeValue: null
            }
        },
        created() {

            axios.get('/admin/attributes/' + this.$route.params.id + '/edit').then(httpResponse => {

                httpResponse.data.attribute.values.forEach(value => {
                    value.refreshing = false;
                });

                this.attribute = httpResponse.data.attribute;

                this.savedOriginal = this.$root.copy(this.attribute);

                this.isLoadedValues = true;

            });

            let self = this;

            this.debouncedStoreAttribute = _.debounce(function () {
                self.applyForm();
            }, 1000 * 60 * 2);

            this.onSearchCategories();

            this.getInformations();

        },
        computed: {
            isChangedInfo() {

                let current = this.$root.copy(this.attribute);

                delete current.refreshing;

                let saved = this.$root.copy(this.savedOriginal);

                delete saved.refreshing;

                return JSON.stringify(current) !== JSON.stringify(saved);

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
                    //this.debouncedStoreAttribute();
                }
            }
        },
        methods: {
            applyForm() {
                let self = this;

                let attribute = this.attribute;

                this.refreshing = true;

                if (attribute.id) {
                    axios.put('/admin/attributes/' + attribute.id, attribute)
                        .then(httpResponse => {
                            this.$set(this.attribute, 'id', httpResponse.data.id);
                            this.$set(this.attribute, 'slug', httpResponse.data.slug);

                            this.attribute.values.forEach((value, valueIndex) => {
                                value.id = httpResponse.data.values[valueIndex].id;
                                value.slug = httpResponse.data.values[valueIndex].slug;
                            });

                            this.$root.notify(httpResponse.data);

                            this.savedOriginal = this.$root.copy(this.attribute);

                        }).catch(error => {
                        if (error.response) this.$root.notify(error.response.data);
                    }).finally(() => {
                        this.refreshing = false;
                    });
                } else {
                    axios.post('/admin/attributes', attribute)
                        .then(httpResponse => {

                            self.$set(self.attribute, 'id', httpResponse.data.id);
                            self.$set(self.attribute, 'slug', httpResponse.data.slug);

                            this.$root.notify(httpResponse.data);


                        }).catch(error => {
                        if (error.response) this.$root.notify(error.response.data);
                    }).finally(() => {
                        this.refreshing = false;
                    });
                }
            },
            addAttributeValue() {

                let value = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    attribute_id: this.attribute.id,
                    slug: null,
                    sort_order: 0,
                    status: true,
                    image: '',
                    color: '',
                    prefer_style: null,
                    translates: [],
                    refreshing: false
                };

                for (let language of this.$root.languages) {
                    value.translates.push({
                        locale: language.locale,
                        value: null,
                    });
                }

                this.attribute.values.push(value);
            },
            deleteAttribute() {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.refreshing = true;

                    axios.delete('/admin/attributes/' + this.attribute.id).then(httpResponse => {
                        this.$router.push({name: "attributes"});
                    });
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
                        autocomplete: true
                    }
                }).then(httpResponse => {
                    self.$set(self, 'categories', httpResponse.data.categories);
                    if (loading !== null) loading(false);
                });
            }, 500),
            showModalAttributeValue(index) {
                this.$set(this, 'modalAttributeValue', this.attribute.values[index]);

                this.$root.changePopupShowStatus('attribute-value-modal', true);
            },
            changePreferStyleForAttributeValue(prefer) {
                if (this.modalAttributeValue.prefer_style === prefer) {
                    this.modalAttributeValue.prefer_style = null;
                } else {
                    this.modalAttributeValue.prefer_style = prefer;
                }
            },
            getInformations() {
                axios.get('/admin/informations/', {
                    params: {
                        autocomplete: true,
                    }
                }).then(httpResponse => {
                    this.informations = httpResponse.data.informations;
                })
            },
            deleteAttributeValue(index){
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.attribute.values.splice(index, 1);
                });
            },
        }
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