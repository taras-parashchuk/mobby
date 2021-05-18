<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.localisation.items['stock-statuses']}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="stockStatuses.length">
                <vue-good-table
                        :columns="columns"
                        :rows="stockStatuses"
                        styleClass="table"
                        row-style-class="table__row">
                    <template slot="table-row" slot-scope="props">

                        <template v-if="props.column.field === 'name'">
                            <div v-for="translate in stockStatuses[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    {{$root.languages.find((language) => {return language.locale ===
                                    translate.locale}).name}}:
                                    <input type="text" class="input input--label_left"
                                           v-model="translate.title">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'status'">
                            <div class="flex flex--align-center">
                                <div class="switcherStatus">
                                    <div @click="stockStatuses[props.index].status = false" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': stockStatuses[props.index].status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="stockStatuses[props.index].status = true" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': stockStatuses[props.index].status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="stockStatuses[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-if="isChangedRow(stockStatuses[props.index].id)" class="table__action"
                                   href="javascript:void(0)" @click.stop="storeStockStatus(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="deleteStockStatus(props.index)">
                                    <icon icon="delete" class="icon"></icon>
                                </a>
                            </template>
                        </template>
                        <span v-else>
                        {{props.formattedRow[props.column.field]}}
                    </span>
                    </template>

                </vue-good-table>

            </div>
            <div v-else class="listEmpty">
                <div class="listEmpty__heading">{{$root.translateWords('Your stock statuses list is empty')}} :(</div>
                <div class="listEmpty__text">
                    {{$root.translateWords('You may add them')}}
                    <a class="listEmpty__link" href="javascript:void(0)" @click.stop="addStockStatus">{{$root.translateWords('manually')}}</a>
                </div>
            </div>
        </template>

        <!--
        <mdb-tbl bordered>
            <mdb-tbl-head>
                <tr>
                    <th>{{ $root.translate.columns.id }}</th>
                    <th>{{ $root.translate.columns.name }}</th>
                    <th>{{ $root.translate.columns.status }}</th>
                    <th>{{ $root.translate.columns.actions }}</th>
                </tr>
            </mdb-tbl-head>
            <mdb-tbl-body>
                <tr v-for="(stockStatus, index) in stockStatuses">
                    <td>
                        <template v-if="stockStatus.id">
                            {{stockStatus.id}}
                        </template>
                        <template v-else>
                            <div class="spinner-grow" role="status">
                                <span class="sr-only">Загрузка...</span>
                            </div>
                        </template>
                    </td>
                    <td>
                        <div v-for="translate in stockStatus.translates"
                             class="md-form input-group input-group-sm mb-0 mt-0">
                            <div class="input-group-prepend">
                                <span class="input-group-text md-addon">
                                    {{$root.languages.find((language) => {return language.locale === translate.locale}).name}}
                                </span>
                            </div>
                            <input type="text" class="form-control"
                                   v-model="translate.title">
                        </div>
                    </td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="stockStatus.status" class="custom-control-input"
                                   :id="'stockStatusStatusSwitcher-'+index">
                            <label class="custom-control-label" :for="'stockStatusStatusSwitcher-'+index"></label>
                        </div>
                    </td>
                    <td>
                        <template v-if="stockStatus.refreshing">
                            <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                        </template>
                        <template v-else>
                            <a href="javascript:void(0)" @click="storeStockStatus(index)">
                                <template v-if="stockStatus.id">
                                    <mdb-icon class="mr-2" icon="pencil-alt"/>
                                </template>
                                <template v-else>
                                    <font-awesome-icon class="mr-2" icon="save"></font-awesome-icon>
                                </template>
                            </a>
                            <a href="javascript:void(0)" @click="deleteStockStatus(index)">
                                <mdb-icon icon="trash-alt"/>
                            </a>
                        </template>
                    </td>
                </tr>
            </mdb-tbl-body>
        </mdb-tbl>
        -->

        <widget-actions add="addStockStatus" :trans="{add: $root.translateWords('Create stock status')}"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "StockStatusComponent",
        data() {
            return {
                stockStatuses: [],
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
            }
        },
        created() {
            axios.get('/admin/stock-statuses/').then(
                httpResponse => {
                    httpResponse.data.stock_statuses.forEach(stockStatus => {
                        stockStatus.refreshing = false;

                        this.stockStatuses.push(stockStatus);
                    });

                    this.$set(this, 'savedOriginal', this.$root.copy(this.stockStatuses));

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

                    let currentPosition = this.stockStatuses.findIndex(item => {
                        return item.id === id
                    });

                    let current = this.$root.copy(this.stockStatuses[currentPosition]);

                    delete current.refreshing;

                    let saved = this.$root.copy(this.savedOriginal[originalPosition]);

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        methods: {
            addStockStatus() {
                let stockStatus = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    translates: [],
                    status: true,
                    refreshing: false
                };

                for (let language of this.$root.languages) {
                    stockStatus.translates.push({
                        locale: language.locale,
                        title: ''
                    });
                }

                this.$root.scrollToNewRow(this.stockStatuses, stockStatus);

            },
            deleteStockStatus(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let stockStatus = this.stockStatuses[index];

                    stockStatus.refreshing = true;

                    let stockStatus_id = stockStatus.id;

                    let originalPosition = this.savedOriginal.findIndex(stockStatus => {
                        return stockStatus.id === stockStatus_id
                    });

                    if (typeof stockStatus.id === 'number') {
                        axios.delete('/admin/stock-statuses/' + stockStatus.id).then(
                            httpResponse => {

                                this.$root.notify(httpResponse.data);

                                this.stockStatuses.splice(index, 1);

                                this.savedOriginal.splice(originalPosition, 1);
                            }
                        )
                    } else {
                        this.stockStatuses.splice(index, 1);
                    }
                });
            },
            storeStockStatus(index) {
                let stockStatus = this.stockStatuses[index];
                let request;

                let stockStatus_id = stockStatus.id;

                stockStatus.refreshing = true;

                let originalPosition = this.savedOriginal.findIndex(stockStatus => {
                    return stockStatus.id === stockStatus_id
                });

                if (typeof stockStatus.id === 'number') {
                    request = axios.put('/admin/stock-statuses/' + stockStatus.id, stockStatus);
                } else {
                    request = axios.post('/admin/stock-statuses', stockStatus);
                }

                request.then(httpResponse => {

                    this.$root.notify(httpResponse.data);

                    stockStatus.id = httpResponse.data.id;

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(stockStatus));
                    } else {
                        this.savedOriginal.push(this.$root.copy(stockStatus));
                    }

                }).catch(error => {
                    if (error.response) {

                        this.$root.notify(error.response.data);
                    }
                }).finally(() => {
                    stockStatus.refreshing = false;
                });
            }
        }

    }
</script>

<style scoped>

</style>