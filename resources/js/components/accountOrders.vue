<template>
    <div>
        <table class="table js-table" v-if="orders.length">
            <thead>
            <tr>
                <td class="table__cell">{{$root.trans['account.text.order_id']}}</td>
                <td class="table__cell">{{$root.trans['account.text.customer']}}</td>
                <td class="table__cell">{{$root.trans['common.text.quantity']}}</td>
                <td class="table__cell">{{$root.trans['account.text.status']}}</td>
                <td class="table__cell">{{$root.trans['account.text.total']}}</td>
                <td class="table__cell" colspan="2">{{$root.trans['account.text.date_added']}}</td>
            </tr>
            </thead>
            <tbody>
            <tr v-for="order in orders">
                <td class="table__cell text-right">№{{order.id}}</td>
                <td class="table__cell text-left">{{order.FullName}}</td>
                <td class="table__cell text-right">{{order.products_count}}</td>
                <td class="table__cell text-left">{{order.history ? order.history.comment : ''}}</td>
                <td class="table__cell text-right">{{order.total ? order.total.valueFormat: ''}}</td>
                <td class="table__cell text-left">{{order.date_added}}</td>
                <td class="table__cell text-right">
                    <router-link :to="{name: 'order', params: {id: order.id, order: order}}"
                       class="btn btn-info"><i class="icon sb-icon-filter-results-button"></i>
                    </router-link>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="empty" v-else>
            <div class="empty__text">
                {{$root.trans['account.error.empty-orders']}}
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "accountOrders",
        data() {
            return {
                orders: [],
            }
        },
        created() {
            axios.get('/account/get-orders').then(httpResponse => {
                this.orders = httpResponse.data.orders;
            });
        }
    }
</script>

<style scoped>

</style>