<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu['design-modules'].items.banners}}</h2>

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
                            <div class="flex flex--align-center">
                                <input type="text" class="input"
                                       v-model="rows[props.index].name">
                            </div>
                        </template>
                        <template v-else-if="props.column.field == 'status'">
                            <div class="flex flex--align-center">
                                <div class="switcherStatus">
                                    <div @click="rows[props.index].status = false" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': rows[props.index].status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="rows[props.index].status = true" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': rows[props.index].status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
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
                                    <router-link class="table__action"
                                                 :to="{name:'bannerSlides', params: {id: rows[props.index].id}}">
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
                <div class="listEmpty__heading">{{$root.translateWords('Your banners list is empty')}} :(</div>
                <div class="listEmpty__text">
                    {{$root.translateWords('You may add them')}}
                    <a class="listEmpty__link" href="javascript:void(0)" @click.stop="add">{{$root.translateWords('manually')}}</a>
                </div>
            </div>
        </template>

        <widget-actions add="add" :trans="{add: $root.translateWords('Create banner')}"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "Banners",
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
            };
        },
        created() {

            let url = new URL(window.location.href);
            let page = parseInt(url.searchParams.get("page"));

            if (!page) page = 1;

            this.serverParams.page = page;

            history.pushState(null, null, '/admin/banners/?page=' + page);

            this.loadItems();

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
            loadItems(params = null) {

                if (params !== null) {
                    this.serverParams.sort_column = params[0].field;
                    this.serverParams.sort_direction = params[0].type;
                }

                axios.get('/admin/banners', {
                    params: this.serverParams
                }).then(httpResponse => {

                    this.totalRecords = httpResponse.data.banners.total;

                    if (httpResponse.data.banners.data.length === 0 && this.totalRecords > 0) {
                        this.onPageChange({currentPage: 1});
                    } else {
                        this.serverParams.fromRecords = httpResponse.data.banners.from;
                        this.serverParams.toRecords = httpResponse.data.banners.to;

                        if (httpResponse.data.banners.data.length) {
                            httpResponse.data.banners.data.forEach(banner => {
                                banner.refreshing = false;
                            });
                        }

                        this.$set(this, 'rows', httpResponse.data.banners.data);
                        this.$set(this, 'savedOriginal', this.$root.copy(httpResponse.data.banners.data));
                        this.isLoaded = true;
                    }
                });
            },

            updateParams(newProps) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },

            onPageChange(params) {
                this.updateParams({page: params.currentPage});

                history.pushState(null, null, '/admin/banners/?page=' + params.currentPage);

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

            add() {
                this.rows.push({
                    id: 'tmp-' + Math.random(100000, 10000000),
                    name: '',
                    status: true,
                    refreshing: false
                });
            },
            store(index) {

                let banner = this.rows[index];

                let banner_id = banner.id;

                let originalPosition = this.savedOriginal.findIndex(banner => {
                    return banner.id === banner_id
                });

                let request;

                banner.refreshing = true;

                if (typeof banner.id === 'number') {

                    request = axios.put('/admin/banners/' + banner.id+'?fast=1', banner);
                } else {
                    request = axios.post('/admin/banners', banner);
                }

                request.then(httpResponse => {

                    if (typeof banner.id !== 'number') {
                        banner.id = httpResponse.data.id;
                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(banner));
                    } else {
                        this.savedOriginal.push(this.$root.copy(banner));
                    }

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    banner.refreshing = false;
                });
            },
            destroy(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let banner = this.rows[index];

                    let banner_id = banner.id;

                    let originalPosition = this.savedOriginal.findIndex(banner => {
                        return banner.id === banner_id
                    });

                    banner.refreshing = true;

                    if (typeof banner.id === 'number') {
                        axios.delete('/admin/banners/' + banner.id).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.rows.splice(index, 1);

                            this.savedOriginal.splice(originalPosition, 1);

                        });
                    } else {
                        this.rows.splice(index, 1);
                    }
                });
            }
        },

    }
</script>

<style scoped>

</style>