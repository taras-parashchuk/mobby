<template>
    <div v-if="Object.keys(order).length">
        <table class="table js-table">
            <thead>
            <tr>
                <td class="table__cell" colspan="2">{{$root.trans['account.text.order_detail']}}</td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="table__cell">
                    <div>{{$root.trans['account.text.order_id']}} № {{order.id}}</div>
                    <div>{{$root.trans['account.text.date_added']}} {{order.created_at}}</div>
                </td>
                <td class="table__cell">
                    <div v-if="order.payment">{{$root.trans['account.text.payment']}} {{order.payment.decoded_method_name}}</div>

                    <div v-if="order.shipping">{{$root.trans['account.text.shipping']}} {{order.shipping.decoded_method_name}}</div>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="table js-table">
            <thead>
            <tr>
                <td class="table__cell">{{$root.trans['account.text.product_name']}}</td>
                <td class="table__cell">{{$root.trans['common.text.quantity']}}</td>
                <td class="table__cell">{{$root.trans['common.text.price']}}</td>
                <td class="table__cell">{{$root.trans['account.text.total']}}</td>
            </tr>
            </thead>
            <tbody>
            <tr v-for="product in order.products">
                <td class="table__cell">
                    <div>{{product.name}}</div>
                    <div v-if="null">
                        <small> - <?php echo $option['name']; ?>
                            : <?php echo $option['value']; ?></small>
                    </div>
                </td>
                <td class="table__cell">{{product.quantity}}</td>
                <td class="table__cell">{{product.priceFormat}}</td>
                <td class="table__cell">{{ product.total_format }}</td>
            </tr>
            </tbody>
            <tfoot>
            <tr v-for="total in order.totals">
                <td colspan="3"></td>
                <td class="table__cell"><b>{{total.name}}</b></td>
                <td class="table__cell">{{total.valueFormat}}</td>
            </tr>
            </tfoot>
        </table>
    </div>
</template>

<script>
    export default {
        name: "accountOrder",
        data(){
            return {
                order: {}
            }
        },
        created() {
            if(this.$route.params.order){
                this.order = this.$route.params.order;
            } else {
                axios.get('/account/get-orders/' + this.$route.params.id + '/')
                    .then(httpResponse => {
                       this.order = httpResponse.data.order || {};
                    });
            }
        }
    }
</script>

<style scoped>

</style>