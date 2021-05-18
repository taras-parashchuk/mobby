<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.catalog.items.attributes}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="rows.length || totalRecords">
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
                                <template v-if="typeof rows[props.index].id === 'number'">
                                    <router-link class="table__action"
                                                 :to="{name:'attribute', params: {id: rows[props.index].id}}">
                                        <icon icon="pencil-edit-button" class="icon"></icon>
                                    </router-link>
                                </template>
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
                <div class="listEmpty__heading">{{$root.translateWords('Your attributes list is empty')}} :(</div>
                <div class="listEmpty__text">
                    {{$root.translateWords('You may add them')}}
                    <a class="listEmpty__link" href="javascript:void(0)" @click.stop="add">{{$root.translateWords('manually')}}</a>
                </div>
            </div>
        </template>

        <widget-actions add="add"
                        :trans="{add: $root.translateWords('Create attribute')}"
                        :additional-styles="['other']"
                        :others="[{icon: 'retweet', method: 'refreshFilter', actionName: $root.translateWords('Cache attributes')}]"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "AttributesComponent",
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

            history.pushState(null, null, '/admin/attributes/?page=' + page);

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
        watch: {
            rows() {
              if(this.rows.length === 0 && this.totalRecords > 0){
                  if( this.serverParams.page > 1){
                      this.serverParams.page--;
                  }else{
                      this.serverParams.page = 1;
                  }
                  this.loadItems();
              }
            }
        },
        methods: {
            add() {
                let attribute = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    sort_order: null,
                    translates: [],
                    status: true,
                    refreshing: false
                };

                for (let language of this.$root.languages) {
                    attribute.translates.push({
                        locale: language.locale,
                        name: ''
                    });
                }

                this.$root.scrollToNewRow(this.rows, attribute);

            },
            storeAttribute(index) {

                let attribute = this.rows[index];

                let attribute_id = attribute.id;

                let originalPosition = this.savedOriginal.findIndex(attribute => {
                    return attribute.id === attribute_id
                });

                let request;

                attribute.refreshing = true;

                if (typeof attribute.id === 'number') {

                    request = axios.put('/admin/attributes/' + attribute.id, attribute);
                } else {
                    request = axios.post('/admin/attributes', attribute);
                }

                request.then(httpResponse => {

                    if (typeof attribute.id !== 'number') {
                        attribute.id = httpResponse.data.id;
                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(attribute));
                    } else {
                        this.savedOriginal.push(this.$root.copy(attribute));
                    }

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    attribute.refreshing = false;
                });
            },
            removeAttribute(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let attribute = this.rows[index];

                    let attribute_id = attribute.id;

                    let originalPosition = this.savedOriginal.findIndex(attribute => {
                        return attribute.id === attribute_id
                    });

                    attribute.refreshing = true;

                    if (typeof attribute.id === 'number') {
                        axios.delete('/admin/attributes/' + attribute.id).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.rows.splice(index, 1);

                            this.savedOriginal.splice(originalPosition, 1);

                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            attribute.refreshing = false;
                        });
                    } else {
                        this.rows.splice(index, 1);
                    }
                });
            },
            refreshFilter() {

                this.refreshing = true;

                axios.put('/admin/filter/refresh/').then(httpResponse => {

                    this.$root.notify(httpResponse.data);

                    this.refreshing = false;

                }).catch(error => {

                    if (error.response) {
                        this.$root.notify(error.response.data);
                    }

                    this.refreshing = false;
                });
            },
            loadItems(params = null) {

                if (params !== null) {
                    this.serverParams.sort_column = params[0].field;
                    this.serverParams.sort_direction = params[0].type;
                }

                axios.get('/admin/attributes', {
                    params: this.serverParams
                }).then(httpResponse => {

                    this.totalRecords = httpResponse.data.attributes.total;

                    if (httpResponse.data.attributes.data.length === 0 && this.totalRecords > 0) {
                        this.onPageChange({currentPage: 1});
                    } else {
                        this.serverParams.fromRecords = httpResponse.data.attributes.from;
                        this.serverParams.toRecords = httpResponse.data.attributes.to;

                        if (httpResponse.data.attributes.data.length) {
                            httpResponse.data.attributes.data.forEach(attribute => {
                                attribute.refreshing = false;
                            });
                        }

                        this.$set(this, 'rows', httpResponse.data.attributes.data);
                        this.$set(this, 'savedOriginal', this.$root.copy(httpResponse.data.attributes.data));
                        this.isLoaded = true;
                    }
                });
            },

            updateParams(newProps) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },

            onPageChange(params) {
                if (params.currentPage > 0) {
                    this.updateParams({page: params.currentPage});

                    history.pushState(null, null, '/admin/attributes/?page=' + params.currentPage);

                    this.loadItems();
                }
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