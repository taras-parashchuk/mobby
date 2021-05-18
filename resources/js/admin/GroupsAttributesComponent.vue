<template>
    <div>
        <mdb-alert v-if="messages.success"
                   color="success" dismiss @closeAlert="messages.success = ''">
            {{messages.success}}
        </mdb-alert>
        <template v-if="Object.keys(messages.errors).length">
            <template v-for="(groupError, groupErrorField) in messages.errors">
                <mdb-alert v-for="(error, error_number) in groupError" :key="groupErrorField+'-'+error_number"
                           color="danger" dismiss @closeAlert="groupError = groupError.splice(error_number,1)">
                    {{error}}
                </mdb-alert>
            </template>
        </template>
        <mdb-tbl bordered>
            <mdb-tbl-head>
                <tr>
                    <th>{{ $root.translate.columns.id }}</th>
                    <th>{{ $root.translate.columns.name }}</th>
                    <th>{{ $root.translate.columns['sort-order'] }}</th>
                    <th>{{ $root.translate.columns.status }}</th>
                    <th>{{ $root.translate.columns.actions }}</th>
                </tr>
            </mdb-tbl-head>
            <mdb-tbl-body>
                <tr v-for="(attribute_group, index) in attributes_groups">
                    <td>
                        <template v-if="attribute_group.id">
                            {{attribute_group.id}}
                        </template>
                        <template v-else>
                            <div class="spinner-grow" role="status">
                                <span class="sr-only">Загрузка...</span>
                            </div>
                        </template>
                    </td>
                    <td>
                        <div v-for="translate in attribute_group.translates" class="md-form input-group input-group-sm mb-0 mt-0">
                            <div class="input-group-prepend">
                                <span class="input-group-text md-addon">
                                    {{$root.languages.find((language) => {return language.locale === translate.locale}).name}}
                                </span>
                            </div>
                            <input type="text" class="form-control"
                                   v-model="translate.name">
                        </div>
                    </td>
                    <td>
                        <mdb-input class="mt-0 mb-0" size="sm" :label="$root.translate.columns['sort-order']"
                                   v-model="attribute_group.sort_order"/>
                    </td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="attribute_group.status" class="custom-control-input"
                                   :id="'attribute_groupStatusSwitcher-'+index">
                            <label class="custom-control-label" :for="'attribute_groupStatusSwitcher-'+index"></label>
                        </div>
                    </td>
                    <td>
                        <a href="javascript:void(0)" @click="storeGroup(index)">
                            <template v-if="attribute_group.id">
                                <mdb-icon icon="pencil-alt"/>
                            </template>
                            <template v-else>
                                <mdb-icon icon="plus"/>
                            </template>
                        </a>
                        <template v-if="attribute_group.id">
                            <a href="javascript:void(0)" @click="removeAttributeGroup(index)">
                                <mdb-icon icon="trash-alt"/>
                            </a>
                        </template>
                    </td>
                </tr>
            </mdb-tbl-body>
            <tfoot>
            <tr>
                <td colspan="4">
                    <mdb-btn color="info" @click="addGroup">Додати</mdb-btn>
                </td>
            </tr>
            </tfoot>
        </mdb-tbl>
    </div>
</template>

<script>

    import {mdbAlert, mdbTbl, mdbTblHead, mdbTblBody, mdbIcon, mdbBtn, mdbInput} from 'mdbvue';

    export default {
        name: "AttributesGroupsComponent",
        components: {
            mdbTbl,
            mdbTblHead,
            mdbTblBody,
            mdbIcon,
            mdbAlert,
            mdbBtn,
            mdbInput
        },
        data() {
            return {
                attributes_groups: [],
                messages: {
                    errors: {},
                    success: ''
                }
            }
        },
        created() {
            let self = this;
            axios.get('/admin/groups-attributes')
                .then(httpResponse => {
                    if (httpResponse.data.attributes_groups.length) {
                        self.attributes_groups = httpResponse.data.attributes_groups;
                    }
                });
        },
        methods: {
            removeAttributeGroup(index) {

                let id = this.attributes_groups[index].id;

                let self = this;

                axios.delete('/admin/groups-attributes/' + id)
                    .then(httpResponse => {

                        self.messages.success = httpResponse.data.text.success_deleted;

                        this.attributes_groups.splice(index, 1);
                    });
            },
            addGroup() {

                let translates = [];

                for (let language of this.$root.languages) {
                    translates.push({
                        locale: language.locale,
                        name: ''
                    });
                }

                this.attributes_groups.push({
                    id: null,
                    sort_order: null,
                    translates: translates
                });

            },
            storeGroup(index) {
                let group = this.attributes_groups[index];
                let request;
                let self = this;

                if (group.id) {
                    request = axios.put('/admin/groups-attributes/' + group.id, group);
                } else {
                    request = axios.post('/admin/groups-attributes', group);
                }

                request.then(httpResponse => {

                    if (group.id) {
                        self.messages.success = httpResponse.data.text.success_updated;
                    }else{
                        self.messages.success = httpResponse.data.text.success_created;
                    }

                    group.id = httpResponse.data.id;

                }).catch(error => {
                    self.messages.errors = error.response.data.errors;
                });
            }
        }
    }
</script>

<style scoped>

</style>