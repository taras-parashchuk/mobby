<template>
    <div>

        <mdb-input :label="$root.translateWords('Start sum for free shipping')" v-model="setting.min" required/>

        <mdb-input :label="$root.translateWords('Start sum for notification about free shipping')"
                   v-model="setting.min_recomended" required/>
    </div>
</template>

<script>

    import {ModelSelect, ModelListSelect, MultiListSelect, MultiSelect} from 'vue-search-select'

    import {
        mdbTblHead,
        mdbTbl,
        mdbTblBody,
        mdbIcon,
        mdbBtn,
        mdbInput,
        mdbAlert,
        mdbModal,
        mdbModalBody,
        mdbModalTitle,
        mdbModalHeader
    } from 'mdbvue'

    export default {
        name: "totalFreeShipping",
        components: {
            ModelSelect,
            mdbBtn,
            mdbInput,
            mdbAlert,
            ModelListSelect,
            MultiListSelect,
            mdbModal,
            mdbModalBody,
            mdbModalHeader,
            mdbModalTitle,
            mdbIcon,
            MultiSelect,
            mdbTbl,
            mdbTblBody,
            mdbTblHead
        },
        props: ['dataProp', 'validationProp'],
        data() {
            return {
                data: {}
            }
        },
        created() {
            if (this.dataProp) {
                this.data = this.dataProp;
            }
        },
        computed: {
            setting() {
                return this.data.decoded_setting;
            }
        }
    }
</script>

<style scoped>

</style>