<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.localisation.items['order-statuses']}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="orderStatuses.length">
                <vue-good-table
                        :columns="columns"
                        :rows="orderStatuses"
                        styleClass="table"
                        row-style-class="table__row">
                    <template slot="table-row" slot-scope="props">

                        <template v-if="props.column.field === 'name'">
                            <div v-for="translate in orderStatuses[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    {{$root.languages.find((language) => {return language.locale ===
                                    translate.locale}).name}}:
                                    <input type="text" class="input input--label_left"
                                           v-model="translate.name">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field == 'status'">
                            <div class="flex flex--align-center">
                                <div class="switcherStatus">
                                    <div @click="orderStatuses[props.index].status = false" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': orderStatuses[props.index].status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="orderStatuses[props.index].status = true" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': orderStatuses[props.index].status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="orderStatuses[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-if="isChangedRow(orderStatuses[props.index].id)" class="table__action"
                                   href="javascript:void(0)" @click.stop="store(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
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

                </vue-good-table>

            </div>
            <div v-else class="listEmpty">
                <div class="listEmpty__heading">{{$root.translateWords('Your order statuses list is empty')}} :(</div>
                <div class="listEmpty__text">
                    {{$root.translateWords('You may add them')}}
                    <a class="listEmpty__link" href="javascript:void(0)" @click.stop="add">{{$root.translateWords('manually')}}</a>
                </div>
            </div>
        </template>

        <widget-actions add="add" :trans="{add: $root.translateWords('Create order status')}"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "OrderStatuses",
        data() {
            return {
                orderStatuses: [],
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
            axios.get('/admin/order-statuses/').then(
                httpResponse => {
                    httpResponse.data.order_statuses.forEach(orderStatus => {
                        orderStatus.refreshing = false;

                        this.orderStatuses.push(orderStatus);
                    });

                    this.$set(this, 'savedOriginal', this.$root.copy(this.orderStatuses));

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

                    let currentPosition = this.orderStatuses.findIndex(item => {
                        return item.id === id
                    });

                    let current = this.$root.copy(this.orderStatuses[currentPosition]);

                    delete current.refreshing;

                    let saved = this.$root.copy(this.savedOriginal[originalPosition]);

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        methods: {
            add() {
                let orderStatus = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    status: true,
                    translates: [],
                    refreshing: false
                };

                for (let language of this.$root.languages) {
                    orderStatus.translates.push({
                        locale: language.locale,
                        name: ''
                    });
                }

                this.$root.scrollToNewRow(this.orderStatuses, orderStatus);
            },
            destroy(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let orderStatus = this.orderStatuses[index];

                    orderStatus.refreshing = true;

                    let orderStatus_id = orderStatus.id;

                    let originalPosition = this.savedOriginal.findIndex(orderStatus => {
                        return orderStatus.id === orderStatus_id
                    });

                    if (typeof orderStatus.id === 'number') {
                        axios.delete('/admin/order-statuses/' + orderStatus.id).then(
                            httpResponse => {

                                this.$root.notify(httpResponse.data);

                                this.orderStatuses.splice(index, 1);

                                this.savedOriginal.splice(originalPosition, 1);
                            }
                        ).catch(error => {
                            if (error.response){
                                this.$root.notify(error.response.data);
                            }
                        }).finally(() => {
                            orderStatus.refreshing = false;
                        });
                    }else{
                        this.orderStatuses.splice(index, 1);
                    }
                });
            },
            store(index) {
                let orderStatus = this.orderStatuses[index];
                let request;

                orderStatus.refreshing = true;

                let orderStatus_id = orderStatus.id;

                let originalPosition = this.savedOriginal.findIndex(orderStatus => {
                    return orderStatus.id === orderStatus_id
                });

                if (typeof orderStatus.id === 'number') {
                    request = axios.put('/admin/order-statuses/' + orderStatus.id, orderStatus);
                } else {
                    request = axios.post('/admin/order-statuses', orderStatus);
                }

                request.then(httpResponse => {

                    this.$root.notify(httpResponse.data);

                    orderStatus.id = httpResponse.data.id;

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(orderStatus));
                    } else {
                        this.savedOriginal.push(this.$root.copy(orderStatus));
                    }

                }).catch(error => {
                    if (error.response){

                        this.$root.notify(error.response.data);
                    }
                }).finally(() => {
                    orderStatus.refreshing = false;
                });
            }
        }
    }
</script>

<style scoped>

</style>