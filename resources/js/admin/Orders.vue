<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu['orders-callback'].items.orders}}</h2>

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
                        <template v-if="props.column.field === 'contacts_info'">
                            <template v-if="rows[props.index].FullName.length">{{rows[props.index].FullName}} - </template>{{rows[props.index].telephone}}
                        </template>

                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="rows[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <template v-if="typeof rows[props.index].id === 'number'">
                                    <router-link class="table__action"
                                                 :to="{name:'orderInfo', params: {id: rows[props.index].id}}">
                                        <icon icon="pencil-edit-button" class="icon"></icon>
                                    </router-link>
                                </template>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action" href="javascript:void(0)" @click.stop="destroy(props.index)">
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
                <div class="listEmpty__heading">{{$root.translateWords('Your orders list is empty')}} :(</div>
            </div>
        </template>

    </div>

</template>

<script>

    export default {
        name: "Orders",
        components:{
            'pagination': require('./paginationComponent').default
        },
        data() {
            return {
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
                columns: [
                    {
                        label: '№',
                        field: 'id',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translateWords('Contacts info'),
                        field: 'contacts_info',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.total,
                        field: 'total.valueFormat',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.status,
                        field: 'history.status.translate.name',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.date_created,
                        field: 'created_at',
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
                rows: [],
                isLoading: false,
                refreshing: false,
            };
        },
        created() {

            let url = new URL(window.location.href);
            let page = parseInt(url.searchParams.get("page"));

            if(!page) page = 1;

            this.serverParams.page = page;

            history.pushState(null, null, '/admin/orders/?page='+page);

            this.loadItems();

        },
        methods: {
            loadItems(params = null){

                if (params !== null) {
                    this.serverParams.sort_column = params[0].field;
                    this.serverParams.sort_direction = params[0].type;
                }

                return axios.get('/admin/orders', {
                    params: this.serverParams
                }).then(response => {

                    this.totalRecords = response.data.orders.total;

                    if(response.data.orders.data.length === 0 && this.totalRecords > 0){
                        this.onPageChange({currentPage: 1});
                    }else{
                        this.serverParams.fromRecords = response.data.orders.from;
                        this.serverParams.toRecords = response.data.orders.to;

                        if(response.data.orders.data.length){
                            response.data.orders.data.forEach(order => {
                                order.refreshing = false;
                            });
                        }

                        this.$set(this, 'rows', response.data.orders.data);
                        this.$set(this, 'savedOriginal', this.$root.copy(response.data.orders.data));
                        this.isLoaded = true;
                    }
                });
            },
            destroy(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let order_id = this.rows[index].id;

                    this.rows[index].refreshing = true;

                    axios.delete('/admin/orders/' + order_id).then(httpResponse => {

                        this.$root.notify(httpResponse.data);

                        this.rows.splice(this.rows.findIndex(item => {
                            return item.id === order_id;
                        }), 1);
                    });
                });
            },
            updateParams(newProps) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },

            onPageChange(params) {
                this.updateParams({page: params.currentPage});

                history.pushState(null, null, '/admin/orders/?page='+params.currentPage);

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