<template>
    <div class="table-list-container">
        <v-server-table url="totals" :columns="columns" :options="options" ref="list">

            <template slot="actions" slot-scope="props">
                <a href="javascript:void(0)" @click="showTotalForm(props.row)">
                    <mdb-icon icon="edit"/>
                </a>
            </template>

        </v-server-table>

        <mdb-modal size="fluid" v-if="Object.keys(totalModal).length"
                   @close="totalModal = {}; totalSetting = {};">
            <mdb-modal-header>
                <mdb-modal-title>{{$root.translate.columns.settings}} '{{totalSetting.title}}'</mdb-modal-title>
            </mdb-modal-header>
            <mdb-modal-body>

                <component :is="totalModal" v-if="totalModal"
                           :data-prop="totalSetting"
                ></component>

            </mdb-modal-body>
        </mdb-modal>
        <widget-actions v-if="totalModal && Object.keys(totalModal).length" edit="editTotal"></widget-actions>
    </div>
</template>

<script>

    import {
        mdbAlert,
        mdbBtn,
        mdbContainer,
        mdbCol,
        mdbRow,
        mdbIcon,
        mdbTabs,
        mdbInput,
        mdbToastNotification,
        mdbTbl, mdbTblHead, mdbTblBody,
        mdbModal,
        mdbModalHeader,
        mdbModalTitle,
        mdbModalBody,
        mdbModalFooter

    } from 'mdbvue';

    export default {
        name: "TotalsPage",
        components: {
            mdbInput,
            mdbIcon,
            mdbAlert,
            mdbTbl,
            mdbTabs,
            mdbTblHead,
            mdbTblBody,
            mdbBtn,
            mdbModal,
            mdbModalHeader,
            mdbModalTitle,
            mdbModalBody,
            mdbModalFooter
        },
        data() {
            return {
                columns: ['id', 'title', 'code', 'actions'],
                tableData: [],
                options: {
                    headings: {
                        id: this.$root.translate.columns.id,
                        title: this.$root.translate.columns.name,
                        code: this.$root.translate.columns.code,
                        actions: this.$root.translate.columns['action']
                    },
                    perPage: 15,
                    perPageValues: [15],
                    responseAdapter({data}) {
                        return {
                            data: data.data,
                            count: data.total
                        }
                    },
                    filterable: false,
                },
                totalModal: '',
                refreshing: false
            }
        },
        methods:{
            showTotalForm(total)
            {
                this.totalSetting = total;
                this.totalModal = require(`./totals/total${total.code}`).default;
            },
            editTotal(total = null) {
                if(total === null){
                    total = this.totalModal.data;
                }

                this.refreshing = true;

                axios.put('/admin/totals/' + total.id, total).then(httpResponse => {
                    this.$root.notify(httpResponse.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            }
        }
    }
</script>

<style scoped>

</style>