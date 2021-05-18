<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.users.items.groups}}</h2>

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
                        <template v-if="props.column.field == 'summary'">
                            <div v-for="translate in rows[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    {{$root.languages.find((language) => {return language.locale ===
                                    translate.locale}).name}}:
                                    <input type="text" class="input input--label_left"
                                           v-model="translate.summary">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field == 'sort_order'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--sort-order"
                                       v-model.number="rows[props.index].sort_order">
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
                                   href="javascript:void(0)" @click.stop="storeAttribute(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="removeAttribute(props.index)">
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
                <div class="listEmpty__heading">{{$root.translateWords('Your user groups list is empty')}} :(</div>
                <div class="listEmpty__text">
                    {{$root.translateWords('You may add them')}}
                    <a class="listEmpty__link" href="javascript:void(0)" @click.stop="add">{{$root.translateWords('manually')}}</a>
                </div>
            </div>
        </template>

        <widget-actions add="add" :trans="{ add: $root.translateWords('Create user group') }"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "UserGroupsComponent",
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
                        label: this.$root.translate.columns.summary,
                        field: 'summary',
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
                refreshing: false,
                savedOriginal: []
            }
        },
        created() {

            let url = new URL(window.location.href);
            let page = parseInt(url.searchParams.get("page"));

            if (!page) page = 1;

            this.serverParams.page = page;

            history.pushState(null, null, '/admin/user-groups/?page=' + page);

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
            add() {
                let userGroup = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    sort_order: null,
                    translates: [],
                    status: true,
                    refreshing: false
                };

                for (let language of this.$root.languages) {
                    userGroup.translates.push({
                        locale: language.locale,
                        name: ''
                    });
                }

                this.$root.scrollToNewRow(this.rows, userGroup);

            },
            storeAttribute(index) {

                let userGroup = this.rows[index];

                let userGroup_id = userGroup.id;

                let originalPosition = this.savedOriginal.findIndex(userGroup => {
                    return userGroup.id === userGroup_id
                });

                let request;

                userGroup.refreshing = true;

                if (typeof userGroup.id === 'number') {

                    request = axios.put('/admin/user-groups/' + userGroup.id, userGroup);
                } else {
                    request = axios.post('/admin/user-groups', userGroup);
                }

                request.then(httpResponse => {

                    if (typeof userGroup.id !== 'number') {
                        userGroup.id = httpResponse.data.id;
                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(userGroup));
                    } else {
                        this.savedOriginal.push(this.$root.copy(userGroup));
                    }

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    userGroup.refreshing = false;
                });
            },
            removeAttribute(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let userGroup = this.rows[index];

                    let userGroup_id = userGroup.id;

                    let originalPosition = this.savedOriginal.findIndex(userGroup => {
                        return userGroup.id === userGroup_id
                    });

                    userGroup.refreshing = true;

                    if (typeof userGroup.id === 'number') {
                        axios.delete('/admin/user-groups/' + userGroup.id).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.rows.splice(index, 1);

                            this.savedOriginal.splice(originalPosition, 1);

                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            userGroup.refreshing = false;
                        });
                    } else {
                        this.rows.splice(index, 1);
                    }
                });
            },
            loadItems(params = null) {

                if (params !== null) {
                    this.serverParams.sort_column = params[0].field;
                    this.serverParams.sort_direction = params[0].type;
                }

                axios.get('/admin/user-groups', {
                    params: this.serverParams
                }).then(httpResponse => {

                    this.totalRecords = httpResponse.data.user_groups.total;

                    if (httpResponse.data.user_groups.data.length === 0 && this.totalRecords > 0) {
                        this.onPageChange({currentPage: 1});
                    } else {
                        this.serverParams.fromRecords = httpResponse.data.user_groups.from;
                        this.serverParams.toRecords = httpResponse.data.user_groups.to;

                        if (httpResponse.data.user_groups.data.length) {
                            httpResponse.data.user_groups.data.forEach(userGroup => {
                                userGroup.refreshing = false;
                            });
                        }

                        this.$set(this, 'rows', httpResponse.data.user_groups.data);
                        this.$set(this, 'savedOriginal', this.$root.copy(httpResponse.data.user_groups.data));
                        this.isLoaded = true;
                    }
                });
            },

            updateParams(newProps) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },

            onPageChange(params) {
                this.updateParams({page: params.currentPage});

                history.pushState(null, null, '/admin/user-groups/?page=' + params.currentPage);

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
            }
        }
    }
</script>

<style scoped>

</style>