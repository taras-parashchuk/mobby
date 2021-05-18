<template>
    <div>

        <mdb-tbl bordered>
            <mdb-tbl-head>
                <tr>
                    <th>{{ $root.translateWords('Start sum')}}</th>
                    <th>{{ $root.translateWords('End sum')}}</th>
                    <th>{{ $root.translate.columns.discount_type}}</th>
                    <th>{{ $root.translate.columns.value}}</th>
                    <th>{{ $root.translate.columns['user-group'] }}</th>
                    <th>{{ $root.translate.columns['action'] }}</th>
                </tr>
            </mdb-tbl-head>
            <mdb-tbl-body>
                <tr v-for="(discount, index) in discounts">
                    <td>
                        <mdb-input class="m-0" v-model="discount.min" required/>
                    </td>
                    <td>
                        <mdb-input class="m-0" v-model="discount.max" required/>
                    </td>
                    <td>
                        <model-select
                                :options="discountTypes"
                                v-model="discount.type"
                        ></model-select>
                    </td>
                    <td>
                        <mdb-input class="m-0" v-model="discount.discount" required/>
                    </td>
                    <td>
                        <model-select
                                :options="userGroups"
                                v-model="discount.user_group_id"
                        ></model-select>
                    </td>
                    <td>
                        <a href="javascript:void(0)" @click="deleteDiscount(index)">
                            <mdb-icon icon="trash-alt"/>
                        </a>
                    </td>
                </tr>
            </mdb-tbl-body>
            <tfoot>
            <tr>
                <td colspan="6">
                    <div class="d-flex justify-content-end">
                        <a href="javascript:void(0)" @click="addDiscount">
                            <font-awesome-icon size="lg" icon="plus"></font-awesome-icon>
                        </a>
                    </div>
                </td>
            </tr>
            </tfoot>
        </mdb-tbl>
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
        name: "totalDiscountTotalSumPrice",
        props: ['dataProp', 'validationProp'],
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
        data() {
            return {
                userGroups: [
                    {
                        text: this.$root.translate.columns['all-user-groups'],
                        value: 0
                    }
                ],
                discountTypes: [
                    {
                        text: this.$root.translate.columns.discount_percent,
                        value: 1
                    },
                    {
                        text: this.$root.translate.columns.discount_fix,
                        value: 2
                    }
                ],
                data: {
                    decoded_setting: {}
                }
            }
        },
        created() {

            if (this.dataProp) {
                this.data = this.dataProp;
            }

            if (!Object(this.data.decoded_setting).hasOwnProperty('discounts')) {
                this.$set(this.data.decoded_setting, 'discounts', []);
            }

            axios.get('/admin/user-groups/', {
                params: {
                    autocomplete: true
                }
            }).then(httpResponse => {
                for (let userGroup of httpResponse.data.user_groups) {
                    this.userGroups.push({
                        text: userGroup.name,
                        value: userGroup.id
                    });
                }
            });
        },
        methods: {
            addDiscount() {
                this.discounts.push({
                    min: 0,
                    max: 0,
                    type: 1,
                    discount: 0,
                    user_group_id: 0
                });
            },
            deleteDiscount(index) {
                this.discounts.splice(index, 1);
            }
        },
        computed: {
            discounts() {
                return this.data.decoded_setting.discounts;
            }
        }
    }
</script>

<style scoped>

</style>