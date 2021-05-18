<template>
    <a href="javascript:void(0)" id="button-confirm"
       @click="addOrder"
       class="checkout__confirm btn btn--primary">
        <span>{{ $parent.trans.button_confirm }}</span>
        <form method="POST" action="https://www.liqpay.ua/api/3/checkout" id="liqPayForm"
              accept-charset="utf-8">
            <input type="hidden" name="data" v-model="data"/>
            <input type="hidden" name="signature" v-model="signature"/>
        </form>
    </a>
</template>

<script>
    export default {
        name: "liq_pay",
        data() {
            return {
                data: null,
                signature: null,
            }
        },
        methods: {
            addOrder(){
                this.$emit('payment-add-order');
            },
            confirmOrder(){
                axios.get('/component/payment/liqpay/info').then(httpResponse => {

                    this.data = httpResponse.data.liqpay_data;
                    this.signature = httpResponse.data.liqpay_signatur;

                    setTimeout(function () {
                        $('#liqPayForm').trigger('submit')
                    }, 1000);
                });
            }
        }
    }
</script>

<style scoped>

</style>