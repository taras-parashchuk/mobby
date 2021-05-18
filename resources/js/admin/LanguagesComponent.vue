<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.localisation.items.languages}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="languages.length">
                <vue-good-table
                        :columns="columns"
                        :rows="languages"
                        styleClass="table"
                        row-style-class="table__row">

                    <template slot="table-row" slot-scope="props">
                        <template v-if="props.column.field === 'name'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--name input--label_left"
                                       v-model="languages[props.index].name">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'sort_order'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--sort-order"
                                       v-model.number="languages[props.index].sort_order">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'locale'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--locale"
                                       v-model.number="languages[props.index].locale">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'status'">
                            <div class="switcherStatus">
                                <div @click="languages[props.index].status = false"
                                     class="switcherStatus__value"
                                     :class="{'switcherStatus__value--active switcherStatus__value--active_off': languages[props.index].status === false}">
                                    {{$root.translate.columns.disabled_short}}
                                </div>
                                <div @click="languages[props.index].status = true"
                                     class="switcherStatus__value"
                                     :class="{'switcherStatus__value--active': languages[props.index].status === true}">
                                    {{$root.translate.columns.enabled_short}}
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'settings'">
                            <div class="flex flex--align-center">
                                <div class="switcher">
                                    <check @click.native="languages[props.index].show_on_site = !languages[props.index].show_on_site"
                                           :checked="languages[props.index].show_on_site === true"
                                           class="switcher__icon"></check>
                                    <span @click="languages[props.index].show_on_site = !languages[props.index].show_on_site"
                                          class="switcher__label">{{$root.translate.columns['showing-on-site']}}</span>
                                </div>
                            </div>
                            <div class="flex flex--align-center">
                                <div class="switcher">
                                    <check @click.native="languages[props.index].index = !languages[props.index].index"
                                           :checked="languages[props.index].index === true"
                                           class="switcher__icon"></check>
                                    <span @click="languages[props.index].index = !languages[props.index].index"
                                          class="switcher__label">{{$root.translate.columns.indexing}}</span>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="languages[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-if="isChangedRow(languages[props.index].id)" class="table__action"
                                   href="javascript:void(0)" @click.stop="storeLanguage(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
                            </template>
                        </template>
                        <span v-else>
                        {{props.formattedRow[props.column.field]}}
                    </span>
                    </template>

                </vue-good-table>

            </div>
        </template>
    </div>
</template>

<script>

    export default {
        name: "LanguagesComponent",
        data() {
            return {
                languages: [],
                savedOriginal: [],
                refreshing: false,
                isLoaded: false,
                columns: [
                    {
                        label: this.$root.translate.columns.name,
                        field: 'name',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.localisation,
                        field: 'locale',
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
                        label: this.$root.translate.columns.settings,
                        field: 'settings',
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
            }
        },
        created() {

            axios.get('/admin/languages')
                .then(httpResponse => {
                    if (httpResponse.data.languages.length) {
                        httpResponse.data.languages.forEach(language => {

                            language.refreshing = false;

                            this.languages.push(language);
                        });
                    }

                    this.$set(this, 'savedOriginal', this.$root.copy(this.languages));

                    this.isLoaded = true;
                });
        },
        computed: {
            isChangedRow() {
                return (id) => {
                    let originalPosition = this.savedOriginal.findIndex(item => {
                        return item.id === id
                    });

                    if (originalPosition === -1) {
                        return true;
                    }

                    let currentPosition = this.languages.findIndex(item => {
                        return item.id === id
                    });

                    let current = this.$root.copy(this.languages[currentPosition]);

                    delete current.refreshing;

                    let saved = this.$root.copy(this.savedOriginal[originalPosition]);

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        methods: {
            storeLanguage(index) {
                let language = this.languages[index];
                let request;
                let self = this;

                language.refreshing = true;

                let language_id = language.id;

                let originalPosition = this.savedOriginal.findIndex(language => {
                    return language.id === language_id
                });

                if (typeof language.id === 'number') {
                    request = axios.put('/admin/languages/' + language.id, language);
                } else {
                    request = axios.post('/admin/languages', language);
                }

                request.then(httpResponse => {

                    this.$root.notify(httpResponse.data);

                    language.id = httpResponse.data.id;

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(language));
                    } else {
                        this.savedOriginal.push(this.$root.copy(language));
                    }

                    this.$set(this.$root, 'languages', this.languages.filter(language => language.status));

                }).catch(error => {
                    if (error.response) {
                        this.$root.notify(error.response.data);
                    }
                }).finally(() => {
                    language.refreshing = false;
                });
            }
        }
    }
</script>

<style scoped>

</style>