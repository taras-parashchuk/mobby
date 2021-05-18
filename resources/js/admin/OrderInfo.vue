<template>
    <div class="table-list-container" v-if="Object.keys(order).length">
        <div>
            <div class="breadcrumbs">
                <router-link class="breadcrumbs__link" :to="{name: 'dashboard'}">
                    {{$root.translate.columns.home}}
                </router-link>
                -
                <router-link class="breadcrumbs__link" :to="{name: 'orders'}">
                    {{$root.translate.menu['orders-callback'].items.orders}}
                </router-link>
            </div>
            <h2 class="mainContent__heading flex flex--justify-space-between flex--align-center">
                {{$root.translate.columns.order}} №{{order.id}}
                <span class="order__date">
                            {{$root.translate.columns.created_at}}: {{order.created_at}}
                        </span>
            </h2>
        </div>
        <div class="grid grid--2 grid--gap_25">
            <div class="widget widget--startTop">
                <div class="flex flex--align-center">
                    <icon class="icon widget__icon widget__icon--people" icon="people"></icon>
                    <div class="flex flex--column">
                        <div class="widget__heading">
                            {{$root.translateWords('About customer')}}
                        </div>
                    </div>
                </div>
                <div class="orderTable">
                    <div class="orderTable__row" v-if="order.FullName.length">
                        <span class="orderTable__cell orderTable__name">
                            {{$root.translate.columns.fullname}}:
                        </span>
                        <span class="orderTable__cell orderTable__value">
                            {{order.FullName}}
                        </span>
                    </div>
                    <div class="orderTable__row" v-if="order.email">
                        <span class="orderTable__cell orderTable__name">
                            Email:
                        </span>
                        <span class="orderTable__cell orderTable__value">
                            {{order.email}}
                        </span>
                    </div>
                    <div class="orderTable__row" v-if="order.telephone">
                        <span class="orderTable__cell orderTable__name">
                            {{$root.translate.columns.telephone}}:
                        </span>
                        <span class="orderTable__cell orderTable__value">
                            {{order.telephone}}
                        </span>
                    </div>
                </div>
            </div>
            <div class="widget widget--startTop" v-if="order.shipping || order.payment">
                <div class="flex flex--align-center">
                    <icon class="icon widget__icon" icon="shopping-cart"></icon>
                    <div class="flex flex--column">
                        <div class="widget__heading">
                            {{$root.translateWords('Shipping and payment')}}
                        </div>
                    </div>
                </div>
                <div class="orderTable">
                    <div class="orderTable__row" v-if="order.shipping">
                        <span class="orderTable__cell orderTable__name">
                            {{$root.translate.columns.shipping_method}}:
                        </span>
                        <span class="orderTable__cell orderTable__value">
                            {{order.shipping.name}}
                        </span>
                    </div>
                    <div class="orderTable__row" v-if="order.payment">
                        <span class="orderTable__cell orderTable__name">
                            {{$root.translate.columns.payment_method}}:
                        </span>
                        <span class="orderTable__cell orderTable__value">
                            {{order.payment.name}}
                        </span>
                    </div>
                    <template v-if="order.decodeAddressInfo">
                        <div class="orderTable__row"
                             v-for="addressItem in order.decodeAddressInfo">
                            <span class="orderTable__cell orderTable__name">
                                {{addressItem.name}}:
                            </span>
                            <span class="orderTable__cell orderTable__value">
                                {{addressItem.value}}
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div>
            <div class="widget">
                <div class="widget__heading">
                    {{$root.translate.menu.catalog.items.products}}
                </div>
                <div class="mt--20 listData listData--noBg w100perc">
                    <vue-good-table
                            :columns="productsColumns"
                            :rows="products"
                            styleClass="table"
                            row-style-class="table__row">
                        <template slot="table-row" slot-scope="props">
                            <template v-if="props.column.field === 'name'">
                                <template v-if="products[props.index].href">
                                    <a class="table__action text-link" :href="products[props.index].href"
                                       target="_blank">{{products[props.index].name}}
                                        <icon icon="foreign" class="icon icon--foreign ml--5"></icon>
                                    </a>
                                </template>
                                <span v-else>{{products[props.index].name}}</span>
                            </template>
                            <template v-else-if="props.column.field === 'image'">
                                <div class="thumb">
                                    <img class="img-responsive" :src='products[props.index].filemanager_thumb'>
                                </div>
                            </template>
                            <template v-else-if="props.column.field === 'spec'">
                                <div v-for="(specification_name, specification_value) in products[props.index].specification">
                                    {{specification_name}} - {{specification_value}}
                                </div>
                            </template>
                        </template>
                    </vue-good-table>
                </div>
            </div>
        </div>
        <div>
            <div class="widget">
                <div class="widget__heading">
                    {{$root.translateWords('Order history')}}
                </div>
                <div class="mt--20 listData listData--noBg w100perc">
                    <vue-good-table
                            :columns="historyColumns"
                            :rows="histories"
                            styleClass="table"
                            row-style-class="table__row">
                        <template slot="table-row" slot-scope="props">
                            <template v-if="props.column.field === 'status'">
                                {{histories[props.index].status.translate.name}}
                            </template>
                            <template v-else-if="props.column.field === 'notify'">
                                <template v-if="parseInt(histories[props.index].notify)">
                                    {{$root.translate.columns.yes}}
                                </template>
                                <template v-else>
                                    {{$root.translate.columns.no}}
                                </template>
                            </template>
                            <template v-else>
                                {{histories[props.index][props.column.field]}}
                            </template>
                        </template>
                    </vue-good-table>
                </div>
                <div class="w100perc mt--48 flex flex--justify-end">
                    <a href="javascript:void(0)" class="btn btn--confirm js-open-modal"
                       @click.stop="$root.changePopupShowStatus('history')">
                        {{$root.translateWords('Add status')}}
                    </a>
                </div>
            </div>
        </div>
        <modal name="history" width="60%" :title="$root.translateWords('Add new status to order')">
            <template v-slot>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <v-select
                                :options="orderStatuses"
                                v-model="newHistory.order_status_id"
                                class="input"
                                :reduce="orderStatus => orderStatus.id"
                                label="name">
                            <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                        </v-select>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.comment}}:
                    </div>
                    <div class="singleFormGroup__field">
                            <textarea class="input input--inForm w100perc" rows="10"
                                      v-model="newHistory.comment"></textarea>
                    </div>
                </div>
                <div class="flex flex--align-center">
                    <div class="switcher">
                        <check @click.native="newHistory.notify = !newHistory.notify"
                               :checked="newHistory.notify === true"
                               class="switcher__icon"></check>
                        <span @click="newHistory.notify = !newHistory.notify"
                              class="switcher__label">
                            {{$root.translateWords('Notify customer?')}}:
                        </span>
                        <icon icon="icon" class="icon switcher__help"></icon>
                    </div>
                </div>
                <div class="flex mt--48">
                    <a class="btn btn--cancel modal__btn js-open-modal" href="javascript:void(0)"
                       @click="$root.changePopupShowStatus('history', false)">{{$root.translateWords('Cancel')}}</a>
                    <a class="btn btn--confirm modal__btn" href="javascript:void(0)"
                       @click="addHistory">
                        {{$root.translateWords('Add status')}}
                    </a>
                </div>
            </template>
        </modal>
        <widget-actions remove="deleteOrder"></widget-actions>
    </div>
    <!--
    <mdb-container v-if="Object.keys(order).length" class="pb-5">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h1 class="mb-0">{{$root.translate.columns.order}} №{{order.id}}</h1>
        </div>
        <mdb-row>
            <mdb-col>
                <mdb-card>
                    <mdb-card-header color="cyan lighten-1">{{$root.translate.columns.order}}</mdb-card-header>
                    <mdb-card-body>
                        <mdb-card-text>
                            <mdb-row class="justify-content-between">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> {{$root.translate.columns.date_created}} </span>
                                        <mdb-icon far icon="calendar-alt" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.created_at}}
                                </mdb-col>
                            </mdb-row>
                            <mdb-row class="justify-content-between mt-3" v-if="order.shipping">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> {{$root.translate.columns.shipping_method}} </span>
                                        <mdb-icon icon="truck" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.shipping.name}}
                                </mdb-col>
                            </mdb-row>
                            <mdb-row class="justify-content-between mt-3" v-if="order.payment">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> {{$root.translate.columns.payment_method}} </span>
                                        <mdb-icon icon="hand-holding-usd" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.payment.name}}
                                </mdb-col>
                            </mdb-row>
                            <mdb-row class="justify-content-between mt-3">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> {{$root.translate.columns.customer_language}} </span>
                                        <mdb-icon icon="globe" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.language.name}}
                                </mdb-col>
                            </mdb-row>
                            <mdb-row class="justify-content-between mt-3">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> {{$root.translate.columns.customer_currency}} </span>
                                        <mdb-icon icon="coins" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.currency.name}}
                                </mdb-col>
                            </mdb-row>
                            <mdb-row class="justify-content-between mt-3" v-if="order.comment">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> {{$root.translate.columns.comment}} </span>
                                        <mdb-icon far icon="comment-alt" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.comment}}
                                </mdb-col>
                            </mdb-row>
                        </mdb-card-text>
                    </mdb-card-body>
                </mdb-card>
            </mdb-col>
            <mdb-col>
                <mdb-card>
                    <mdb-card-header color="cyan lighten-1">{{$root.translate.columns.customer}}</mdb-card-header>
                    <mdb-card-body>
                        <mdb-card-text>
                            <mdb-row class="justify-content-between mt-3" v-if="order.FullName">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> {{$root.translate.columns.fullname}} </span>
                                        <mdb-icon icon="user" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.FullName}}
                                </mdb-col>
                            </mdb-row>
                            <mdb-row class="justify-content-between mt-3" v-if="order.email">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> Email </span>
                                        <mdb-icon icon="envelope" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.email}}
                                </mdb-col>
                            </mdb-row>
                            <mdb-row class="justify-content-between mt-3" v-if="order.telephone">
                                <mdb-col>
                                    <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                        <span slot="tip"> {{ $root.translate.columns.telephone }} </span>
                                        <mdb-icon icon="mobile-alt" slot="reference" size="lg"/>
                                    </mdb-tooltip>
                                    {{order.telephone}}
                                </mdb-col>
                            </mdb-row>
                            <template v-if="false">
                                <mdb-row class="justify-content-between mt-3">
                                    <mdb-col>
                                        <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                            <span slot="tip"> {{$root.translate.columns.customer_profile}} </span>
                                            <mdb-icon icon="link" slot="reference" size="lg"/>
                                        </mdb-tooltip>
                                    </mdb-col>
                                </mdb-row>
                                <mdb-row class="justify-content-between mt-3">
                                    <mdb-col>
                                        <mdb-tooltip trigger="hover" :options="{placement: 'top'}" class="mr-2">
                                            <span slot="tip"> {{$root.translate.columns['user-group']}} </span>
                                            <mdb-icon icon="users" slot="reference" size="lg"/>
                                        </mdb-tooltip>
                                        <router-link :to="{name: 'user-groups', params: {id: order.user.group_id}}">
                                            {{$root.translate.columns['user-group']}}
                                        </router-link>
                                    </mdb-col>
                                </mdb-row>
                            </template>
                        </mdb-card-text>
                    </mdb-card-body>
                </mdb-card>
            </mdb-col>
            <mdb-col v-if="order.decodeAddressInfo">
                <mdb-card>
                    <mdb-card-header color="cyan lighten-1">{{$root.translate.columns.address}}</mdb-card-header>
                    <mdb-card-body>
                        <mdb-row class="justify-content-between mt-3" v-for="addressItem in order.decodeAddressInfo"
                                 :key="addressItem.name + '-' + addressItem.value">
                            <mdb-col>
                                {{addressItem.name}}
                            </mdb-col>
                            <mdb-col>
                                {{addressItem.value}}
                            </mdb-col>
                        </mdb-row>
                    </mdb-card-body>
                </mdb-card>
            </mdb-col>
        </mdb-row>
        <mdb-row class="mt-5">
            <mdb-col>
                <mdb-tbl bordered>
                    <mdb-tbl-head>
                        <tr>
                            <th>{{ $root.translate.columns.id }}</th>
                            <th>{{ $root.translate.columns.name }}</th>
                            <th>{{$root.translateWords('Product SKU')}}</th>
                            <th>{{$root.translateWords('Price and currency')}}</th>
                            <th>{{ $root.translate.columns.quantity }}</th>
                            <th>{{ $root.translate.columns.specification }}</th>
                        </tr>
                    </mdb-tbl-head>
                    <mdb-tbl-body>
                        <tr v-for="product in products">
                            <td>{{product.id}}</td>
                            <td>{{product.name}}</td>
                            <td>{{product.sku}}</td>
                            <td>{{product.priceFormat}}</td>
                            <td>{{product.quantity}}</td>
                            <td>
                                <div v-for="(specification_name, specification_value) in product.specification">
                                    {{specification_name}} - {{specification_value}}
                                </div>
                            </td>
                        </tr>
                    </mdb-tbl-body>
                    <tfoot>
                    <tr v-for="total in order.totals">
                        <td class="text-right" colspan="6">
                            <template v-if="total.valueFormat">
                                {{total.name}}: {{total.valueFormat}}
                            </template>
                            <template v-else>
                                {{total.name}}
                            </template>
                        </td>
                    </tr>
                    </tfoot>
                </mdb-tbl>
            </mdb-col>
        </mdb-row>
        <mdb-row>
            <mdb-col>
                <mdb-tbl bordered>
                    <mdb-tbl-head>
                        <tr>
                            <th>{{ $root.translate.columns.status }}</th>
                            <th>{{ $root.translate.columns.notification }}</th>
                            <th>{{ $root.translate.columns.comment }}</th>
                            <th>{{ $root.translate.columns.date_created }}</th>
                        </tr>
                    </mdb-tbl-head>
                    <mdb-tbl-body>
                        <tr v-for="history in order.histories">
                            <td>
                                {{history.status.translate.name}}
                            </td>
                            <td>
                                <template v-if="history.notify">
                                    {{$root.translateWords('Notified')}}
                                </template>
                                <template v-else>
                                    {{$root.translateWords('Not notified')}}
                                </template>
                            </td>
                            <td>
                                {{history.comment}}
                            </td>
                            <td>
                                {{history.date_added}}
                            </td>
                        </tr>
                    </mdb-tbl-body>
                </mdb-tbl>

                <h3>{{$root.translateWords('Add status')}}</h3>

                <model-list-select :list="orderStatuses"
                                   v-model="newHistory.order_status_id"
                                   option-value="id"
                                   option-text="name"></model-list-select>

                <div class="custom-control custom-switch mt-5">
                    <input type="checkbox" class="custom-control-input" id="newHistoryNotify"
                           v-model="newHistory.notify">
                    <label class="custom-control-label" for="newHistoryNotify">
                        {{$root.translateWords('Notify customer?')}}
                    </label>
                </div>

                <div class="form-group mt-5">
                    <label for="exampleFormControlTextarea1">{{$root.translate.columns.comment}}</label>
                    <textarea v-model="newHistory.comment" class="form-control" id="exampleFormControlTextarea1"
                              rows="5"></textarea>
                </div>

                <div class="align-content-center" v-if="newHistory.refreshing">
                    <span class="spinner-border spinner-border-sm" role="status"
                          aria-hidden="true"></span>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="javascript:void(0)" @click="addHistory">
                        <font-awesome-icon size="lg" icon="paper-plane"></font-awesome-icon>
                    </a>
                </div>

            </mdb-col>
        </mdb-row>
    </mdb-container>

    -->
</template>

<script>
    export default {
        name: "OrderInfo",
        data() {
            return {
                refreshing: false,
                order: {},
                orderStatuses: [],
                newHistory: {},
                productsColumns: [
                    {
                        label: this.$root.translate.columns.sku,
                        field: 'sku',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.image_short,
                        field: 'image',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.name,
                        field: 'name',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.price,
                        field: 'priceFormat',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.quantity,
                        field: 'quantity',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.specification,
                        field: 'spec',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.total,
                        field: 'total_format',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                ],
                historyColumns: [
                    {
                        label: this.$root.translate.columns.status,
                        field: 'status',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.created_at,
                        field: 'date_added',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translateWords('Notified?'),
                        field: 'notify',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.comment,
                        field: 'comment',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                ]
            };
        },
        created() {
            axios.get('/admin/orders/' + this.$route.params.id).then(httpResponse => {
                this.order = httpResponse.data;
            });

            axios.get('/admin/order-statuses/', {
                params: {
                    autocomplete: true
                }
            }).then(httpResponse => {
                this.orderStatuses = httpResponse.data.order_statuses;
            });

            this.clearValues();

        },
        computed: {
            products() {
                return this.order.products;
            },
            histories() {
                return this.order.histories;
            }
        },
        methods: {
            clearValues() {
                this.newHistory = {
                    order_status_id: null,
                    notify: false,
                    comment: '',
                    refreshing: false
                };
            },
            addHistory() {

                this.refreshing = true;

                axios.post('/admin/orders/' + this.order.id + '/histories', this.newHistory).then(httpResponse => {

                    this.$root.notify(httpResponse.data);

                    axios.get('/admin/orders/' + this.order.id + '/histories').then(httpResponse => {
                        this.$set(this.order, 'histories', httpResponse.data.histories);

                        this.clearValues();
                    });

                    this.$root.changePopupShowStatus('history', false);

                }).catch(error => {
                    if (error.response) {
                        this.$root.notify(error.response.data);
                    }
                }).finally(() => this.refreshing = false);
            },
            deleteOrder() {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.refreshing = true;

                    axios.delete('/admin/orders/' + this.order.id).then(httpResponse => {
                        this.$router.push({name: "orders"});
                    });
                });
            }
        }
    }
</script>

<style scoped>

</style>