<template>
    <div class="table-list-container">


        <h2 class="mainContent__heading">{{$root.translate.menu.users.items.users}}</h2>

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
                        <template v-if="props.column.field == 'firstname_lastname'">
                            <div class="flex flex--align-center mb--10">
                                <input type="text" class="input input--name"
                                       v-model="rows[props.index].firstname">
                            </div>
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--name"
                                       v-model="rows[props.index].lastname">
                            </div>
                        </template>
                        <template v-else-if="props.column.field == 'telephone_email'">
                            <div class="flex flex--align-center mb--10">
                                <input type="text" class="input" :placeholder="$root.translate.columns.telephone"
                                       v-model="rows[props.index].telephone">
                            </div>
                            <div class="flex flex--align-center">
                                <input type="text" class="input" placeholder="Email"
                                       v-model="rows[props.index].email">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'password_confirm'">
                            <div class="flex flex--align-center mb--10">
                                <input type="password" class="input"
                                       v-model="rows[props.index].password">
                            </div>
                            <div class="flex flex--align-center">
                                <input type="password" class="input"
                                       v-model="rows[props.index].password_confirmation">
                            </div>
                        </template>

                        <template v-else-if="props.column.field === 'extra'">
                            <div class="flex flex--align-center mb--10">
                                <v-select
                                        :clearable="false"
                                        :searchable="true"
                                        :options="userGroups"
                                        v-model="rows[props.index].group_id"
                                        class="input input--inForm"
                                        :reduce="userGroup => userGroup.id"
                                        label="name">
                                    <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                </v-select>
                            </div>
                            <div class="flex flex--align-center">
                                <div class="switcher">
                                    <check @click.native="rows[props.index].newsletter = !rows[props.index].newsletter" :checked="rows[props.index].newsletter === true"
                                           class="switcher__icon"></check>
                                    <span @click="rows[props.index].newsletter = !rows[props.index].newsletter"
                                          class="switcher__label">{{$root.translate.columns.subscription}}</span>
                                </div>
                                <div class="switcher">
                                    <check @click.native="rows[props.index].is_admin = !rows[props.index].is_admin" :checked="rows[props.index].is_admin === true"
                                           class="switcher__icon"></check>
                                    <span @click="rows[props.index].is_admin = !rows[props.index].is_admin"
                                          class="switcher__label">{{$root.translate.columns['admin-rules']}}</span>
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
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="remove(props.index)">
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

        <widget-actions add="add" :trans="{add: $root.translateWords('Create user')}"></widget-actions>
    </div>
</template>

<script>

    import {mdbAlert, mdbTbl, mdbTblHead, mdbTblBody, mdbIcon, mdbBtn, mdbInput} from 'mdbvue';

    import {ModelListSelect} from 'vue-search-select'

    export default {
        name: "UsersComponent",
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
                        label: this.$root.translate.columns.fullname,
                        field: 'firstname_lastname',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translateWords('Telephone/Email'),
                        field: 'telephone_email',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translateWords('Password/Confirmation'),
                        field: 'password_confirm',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.extra,
                        field: 'extra',
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
                savedOriginal: [],
                userGroups: []
            }
        },
        created() {

            let url = new URL(window.location.href);
            let page = parseInt(url.searchParams.get("page"));

            if (!page) page = 1;

            this.serverParams.page = page;

            history.pushState(null, null, '/admin/users/?page=' + page);

            axios.get('/admin/user-groups', {
                params: {
                    autocomplete: true
                }
            }).then(httpResponse => {
               this.userGroups = httpResponse.data.user_groups;
            });

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
                this.$root.scrollToNewRow(this.rows, {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    firstname: null,
                    email: null,
                    lastname: null,
                    telephone: null,
                    password: null,
                    password_confirmation: null,
                    newsletter: true,
                    group_id: this.userGroups[0].id,
                    is_admin: false,
                    refreshing: false
                });
            },
            store(index) {
                let user = this.rows[index];

                let user_id = user.id;

                let originalPosition = this.savedOriginal.findIndex(user => {
                    return user.id === user_id
                });

                let request;

                user.refreshing = true;

                if (typeof user.id === 'number') {

                    request = axios.put('/admin/users/' + user.id, user);
                } else {
                    request = axios.post('/admin/users', user);
                }

                request.then(httpResponse => {

                    if (typeof user.id !== 'number') {
                        user.id = httpResponse.data.id;
                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(user));
                    } else {
                        this.savedOriginal.push(this.$root.copy(user));
                    }

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    user.refreshing = false;
                });
            },
            remove(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let user = this.rows[index];

                    let user_id = user.id;

                    let originalPosition = this.savedOriginal.findIndex(user => {
                        return user.id === user_id
                    });

                    user.refreshing = true;

                    if (typeof user.id === 'number') {
                        axios.delete('/admin/users/' + user_id).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.rows.splice(index, 1);

                            this.savedOriginal.splice(originalPosition, 1);

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

                axios.get('/admin/users', {
                    params: this.serverParams
                }).then(httpResponse => {

                    this.totalRecords = httpResponse.data.users.total;

                    if (httpResponse.data.users.data.length === 0 && this.totalRecords > 0) {
                        this.onPageChange({currentPage: 1});
                    } else {
                        this.serverParams.fromRecords = httpResponse.data.users.from;
                        this.serverParams.toRecords = httpResponse.data.users.to;

                        if (httpResponse.data.users.data.length) {
                            httpResponse.data.users.data.forEach(user => {
                                user.refreshing = false;
                            });
                        }

                        this.$set(this, 'rows', httpResponse.data.users.data);
                        this.$set(this, 'savedOriginal', this.$root.copy(httpResponse.data.users.data));
                        this.isLoaded = true;
                    }
                });
            },

            updateParams(newProps) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },

            onPageChange(params) {
                this.updateParams({page: params.currentPage});

                history.pushState(null, null, '/admin/users/?page=' + params.currentPage);

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