<template>
    <div>
        <h2 class="mainContent__heading">
            {{$root.translate.columns.targets}}
        </h2>
        <div class="grid grid--2 grid--gap_40">
            <div class="widget">
                <div class="flex flex--align-center">
                    <icon class="icon widget__icon widget__icon--people" icon="people"></icon>
                    <div class="flex flex--column">
                        <div class="widget__heading">
                            {{$root.translate.columns.visitors}}
                        </div>
                        <div class="widget__value">
                            1500
                        </div>
                    </div>
                </div>
                <linear-chart :data="[[
                    100,
                    500,
                    200,
                    100,
                    300,
                    700,
                    300,
                    200
                ]]" :labels="['January', 'February', 'March', 'April', 'May', 'June', 'July']" color="#6f6adf"
                              height="165px" class="linearGraphic"></linear-chart>
            </div>
            <div class="widget">
                <div class="flex flex--align-center">
                    <icon class="icon widget__icon" icon="shopping-cart"></icon>
                    <div class="flex flex--column">
                        <div class="widget__heading">
                            {{$root.translate.columns.orders}}
                        </div>
                        <div class="widget__value">
                            {{totalOrders}}
                        </div>
                    </div>
                </div>
                <linear-chart v-if="ordersCountCurrentYear.length" :data="[ordersCountCurrentYear]" :labels="months"
                              color="#ef5c63" height="165px" class="linearGraphic"></linear-chart>
            </div>
        </div>
        <div class="grid-row-2 grid--gap_20 mb--30">
            <div class="widget widget--diffHeight">
                <div>
                    <div class="widget__heading">
                        {{$root.translate.columns.orders}}
                    </div>
                    <table class="widget__table table">
                        <thead>
                        <tr class="table__row">
                            <td class="table__heading">№</td>
                            <td class="table__heading">{{$root.translate.columns.customer}}</td>
                            <td class="table__heading">{{$root.translate.columns.total}}</td>
                            <td class="table__heading">{{$root.translate.columns.status}}</td>
                            <td class="table__heading">{{$root.translate.columns.date_created}}</td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="table__row" v-for="order in lastOrders">
                            <td class="table__value">{{order.id}}</td>
                            <td class="table__value">
                                <template v-if="order.FullName.length">{{order.FullName}} -</template>
                                {{order.telephone}}
                            </td>
                            <td class="table__value">{{order.total.valueFormat}}</td>
                            <td class="table__value">{{order.history.status.translate.name}}</td>
                            <td class="table__value">{{order.created_at}}</td>
                            <td class="table__value">
                                <router-link :to="{name:'orderInfo', params: {id: order.id}}">
                                    <icon class="icon icon--circle" icon="eye-open"></icon>
                                </router-link>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <router-link v-if="lastOrders.length < totalOrders" class="widget__more" :to="{name: 'orders'}">
                    <span>{{$root.translateWords('All orders')}}</span>
                    <icon class="icon" icon="next"></icon>
                </router-link>
            </div>
            <div class="widget widget--diffHeight">
                <div>
                    <div class="widget__heading">
                        {{$root.translate.columns.appeals}}
                    </div>
                    <div class="widget__rows">
                        <div class="appeal">
                            <div class="appeal__heading">
                                Отзыв о товаре
                                <time>
                                    14.10.20р.
                                </time>
                            </div>
                            <div class="appeal__text">
                                Посититель заказал звонок, указав номер: +380633443560
                            </div>
                        </div>
                    </div>
                </div>
                <router-link class="widget__more" :to="{name: 'orders'}">
                    <span>{{$root.translateWords('All orders')}}</span>
                    <icon class="icon" icon="next"></icon>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>

    import LinearChart from './LinearChart.js'

    export default {
        name: "Dashboard",
        components: {
            LinearChart
        },
        data() {
            return {
                lastOrders: [],
                ordersCountCurrentYear: [],
                months: [],
                totalOrders: 0
            }
        },
        created() {
            axios.get('/admin/orders', {
                params: {
                    page: 1,
                    perPage: 10,
                }
            }).then(response => {
                this.totalOrders = response.data.orders.total;

                this.lastOrders = response.data.orders.data;
            });

            axios.get('/admin/orders?current-year').then(response => {
                this.setOrderResultsWithInterval(response.data.orders);
            });
        },
        methods: {
            setOrderResultsWithInterval(ordersGroup) {

                let monthNames = this.$root.translate.country_formats.month;

                let currentMonth = new Date().getMonth() + 1;

                let results = [];

                let results2 = [];

                let year = new Date().getFullYear();

                while (results.length < 6) {

                    if (currentMonth === 0) {
                        currentMonth = 12;
                        year--;

                        continue;
                    }

                    let record = ordersGroup.find(record => record.month == currentMonth && record.year == year);

                    results2.unshift(record ? record.count: 0);

                    results.unshift(monthNames[currentMonth]);

                    currentMonth--;

                }

                this.$set(this, 'months', results);

                this.$set(this, 'ordersCountCurrentYear', results2);

            }
        }
    }
</script>

<style lang="scss" scoped>
    .grid-row-2 {
        display: grid;
        grid-template-columns: 1fr 280px;
    }
</style>