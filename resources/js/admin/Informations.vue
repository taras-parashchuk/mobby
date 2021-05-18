<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.catalog.items.informations}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="rows.length">
                <vue-good-table
                        @on-page-change="onPageChange"
                        @on-sort-change="onSortChange"
                        @on-column-filter="onColumnFilter"
                        @on-per-page-change="onPerPageChange"
                        :pagination-options="{
                            enabled: false
                        }"
                        mode="remote"
                        :columns="columns"
                        :rows="rows"
                        :totalRows="totalRecords"
                        :isLoading.sync="isLoading"
                        styleClass="table"
                        row-style-class="table__row">

                    <template slot="table-row" slot-scope="props">
                        <template v-if="props.column.field == 'name'">
                            <div v-for="translate in rows[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    {{$root.languages.find((language) => {return language.locale ===
                                    translate.locale}).name}}:
                                    <input type="text" class="input input--label_left"
                                           v-model="translate.name">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field == 'sort_order'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--sort-order" v-model.number="rows[props.index].sort_order">
                            </div>
                        </template>
                        <template v-else-if="props.column.field == 'status'">
                            <div class="flex flex--align-center">
                                <div class="switcherStatus">
                                    <div @click="rows[props.index].status = false"
                                         class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': rows[props.index].status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="rows[props.index].status = true"
                                         class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': rows[props.index].status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'showing'">
                            <div class="flex flex--align-center">
                                <div class="switcher">
                                    <check @click.native="rows[props.index].in_top = !rows[props.index].in_top" :checked="rows[props.index].in_top === true"
                                           class="switcher__icon"></check>
                                    <span @click="rows[props.index].in_top = !rows[props.index].in_top"
                                          class="switcher__label">{{$root.translateWords('In header')}}</span>
                                </div>
                                <div class="switcher">
                                    <check @click.native="rows[props.index].in_bottom = !rows[props.index].in_bottom" :checked="rows[props.index].in_bottom === true"
                                           class="switcher__icon"></check>
                                    <span @click="rows[props.index].in_bottom = !rows[props.index].in_bottom"
                                          class="switcher__label">{{$root.translateWords('In footer')}}</span>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="rows[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-if="isChangedRow(rows[props.index].id)" class="table__action"
                                   href="javascript:void(0)" @click.stop="store(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
                                <template v-if="typeof rows[props.index].id === 'number'">
                                    <a class="table__action" :href="rows[props.index].href" target="_blank">
                                        <icon icon="foreign" class="icon"></icon>
                                    </a>
                                    <router-link class="table__action"
                                                 :to="{name:'information', params: {id: rows[props.index].id}}">
                                        <icon icon="pencil-edit-button" class="icon"></icon>
                                    </router-link>
                                </template>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="destroy(props.index)">
                                    <icon icon="delete" class="icon"></icon>
                                </a>
                            </template>
                        </template>
                        <span v-else>
                        {{props.formattedRow[props.column.field]}}
                    </span>
                    </template>

                    <template slot="pagination-bottom" slot-scope="props">

                    </template>

                </vue-good-table>

                <pagination
                        v-if="serverParams.perPage < totalRecords"
                        :current-page="serverParams.page"
                        :per-page="serverParams.perPage"
                        :total="totalRecords"
                        :from-records="serverParams.fromRecords"
                        :to-records="serverParams.toRecords"
                        :pageChanged="onPageChange"
                        :perPageChanged="onPerPageChange">
                </pagination>


            </div>
            <div v-else class="listEmpty">
                <div class="listEmpty__heading">{{$root.translateWords('Your information list is empty')}} :(</div>
                <div class="listEmpty__text">
                    {{$root.translateWords('You may add them')}}
                    <a class="listEmpty__link" href="javascript:void(0)" @click.stop="add">{{$root.translateWords('manually')}}</a>
                </div>
            </div>
        </template>

        <widget-actions add="add" :trans="{add: $root.translateWords('Create information')}"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "Informations",
        components: {
            'pagination': require('./paginationComponent').default,
        },
        data() {
            return {
                rows: [],
                totalRecords: 0,
                serverParams: {
                    columnFilters: {},
                    sort_column: null,
                    sort_direction: null,
                    page: 1,
                    perPage: 100,
                    fromRecords: null,
                    toRecords: null
                },
                isLoaded: false,
                isLoading: false,
                columns: [
                    {
                        label: this.$root.translate.columns.name,
                        field: 'name',
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
                        label: this.$root.translate.columns.showing,
                        field: 'showing',
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
                refreshing: false,
                savedOriginal: []
            }
        },
        created() {

            let url = new URL(window.location.href);
            let page = parseInt(url.searchParams.get("page"));

            if (!page) page = 1;

            this.serverParams.page = page;

            history.pushState(null, null, '/admin/informations/?page=' + page);

            this.loadItems();

        },
        computed:{
            isChangedRow() {
                return (id) => {
                    let originalPosition = this.savedOriginal.findIndex(item => {
                        return item.id === id
                    });

                    if (originalPosition === -1) {
                        return true;
                    }

                    let currentPosition = this.rows.findIndex(item => {
                        return item.id === id
                    });

                    let current = this.$root.copy(this.rows[currentPosition]);

                    delete current.refreshing;

                    let saved = this.$root.copy(this.savedOriginal[originalPosition]);

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        methods: {
            updateParams(newProps) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },

            onPageChange(params) {
                this.updateParams({page: params.currentPage});

                history.pushState(null, null, '/admin/informations/?page=' + params.currentPage);

                this.loadItems();
            },

            onPerPageChange(params) {
                this.updateParams({perPage: params.currentPerPage});
                this.loadItems();
            },

            onSortChange(params) {
                this.serverParams.sort_direction = params[0].type;
                this.serverParams.sort_column = params[0].field;

                this.loadItems();
            },

            onColumnFilter(params) {
                this.updateParams(params);
                this.loadItems();
            },
            loadItems(params = null){
                if (params !== null) {
                    this.serverParams.sort_column = params[0].field;
                    this.serverParams.sort_direction = params[0].type;
                }

                axios.get('/admin/informations', {
                    params: this.serverParams
                }).then(httpResponse => {

                    this.totalRecords = httpResponse.data.informations.total;

                    if (httpResponse.data.informations.data.length === 0 && this.totalRecords > 0) {
                        this.onPageChange({currentPage: 1});
                    } else {
                        this.serverParams.fromRecords = httpResponse.data.informations.from;
                        this.serverParams.toRecords = httpResponse.data.informations.to;

                        if (httpResponse.data.informations.data.length) {
                            httpResponse.data.informations.data.forEach(information => {
                                information.refreshing = false;
                            });
                        }

                        this.$set(this, 'rows', httpResponse.data.informations.data);
                        this.$set(this, 'savedOriginal', this.$root.copy(httpResponse.data.informations.data));
                        this.isLoaded = true;
                    }
                });
            },

            add() {

                let translates = [];

                for (let language of this.$root.languages) {
                    translates.push({
                        locale: language.locale,
                        name: ''
                    });
                }

                this.$root.scrollToNewRow(this.rows, {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    translates: translates,
                    sort_order: 0,
                    in_top: false,
                    in_bottom: false,
                    status: true,
                    refreshing: false,
                    href: ''
                });

            },

            store(index) {
                let information = this.rows[index];

                let information_id = information.id;

                let originalPosition = this.savedOriginal.findIndex(information => {
                    return information.id === information_id
                });

                let request;

                information.refreshing = true;

                if (typeof information.id === 'number') {

                    request = axios.put('/admin/informations/' + information.id, information);
                } else {
                    request = axios.post('/admin/informations', information);
                }

                request.then(httpResponse => {

                    if (typeof information.id !== 'number') {
                        information.id = httpResponse.data.id;
                        information.href = httpResponse.data.href;
                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(information));
                    } else {
                        this.savedOriginal.push(this.$root.copy(information));
                    }

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    information.refreshing = false;
                });
            },

            destroy(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let information = this.rows[index];

                    let information_id = information.id;

                    let originalPosition = this.savedOriginal.findIndex(information => {
                        return information.id === information_id
                    });

                    information.refreshing = true;

                    if (typeof information.id === 'number') {
                        axios.delete('/admin/informations/' + information.id).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.rows.splice(index, 1);

                            this.savedOriginal.splice(originalPosition, 1);

                        });
                    } else {
                        this.rows.splice(index, 1);
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>