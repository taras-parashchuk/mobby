<template>

    <div class="table-list-container">
        <div class="flex flex--justify-space-between">
            <div>
                <h2 class="mainContent__heading">
                    {{$root.translate.menu.synchronizations.items.excel}}
                </h2>
            </div>
        </div>
        <div class="singleForm">
            <div class="tabs">
                <a @click="tab = 'configurations'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'configurations'}">
                    {{$root.translate.columns.configurations}}
                </a>
                <a @click="tab = 'import_export'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'import_export'}">
                    {{$root.translateWords('Import/Export')}}
                </a>
            </div>
            <div class="singleForm__content">
                <template v-if="tab === 'configurations'">
                    <div class="listData listData--noBg" v-if="configurations.length">
                        <vue-good-table
                                :columns="columns"
                                :rows="configurations"
                                styleClass="table"
                                row-style-class="table__row">
                            <template slot="table-row" slot-scope="props">
                                <template v-if="props.column.field === 'name'">
                                    <div class="flex flex--align-center">
                                        <input type="text" class="input" v-model="configurations[props.index].name">
                                    </div>
                                </template>
                                <template v-else-if="props.column.field === 'actions'">
                                    <template v-if="configurations[props.index].refreshing">
                                        <font-awesome-icon class="text-warning" icon="circle-notch"
                                                           spin></font-awesome-icon>
                                    </template>
                                    <template v-else>
                                        <a v-if="isChangedRow(configurations[props.index].name)"
                                           class="table__action"
                                           href="javascript:void(0)" @click.stop="storeConfiguration(configurations[props.index])">
                                            <icon icon="floppy-disk" class="icon"></icon>
                                        </a>
                                        <a href="javascript:void(0)" class="table__action"
                                           @click.stop="editConfiguration(props.index)">
                                            <icon icon="pencil-edit-button" class="icon"></icon>
                                        </a>
                                        <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                           href="javascript:void(0)"
                                           @click.stop="deleteConfiguration(props.index)">
                                            <icon icon="delete" class="icon"></icon>
                                        </a>
                                    </template>
                                </template>
                            </template>
                        </vue-good-table>
                    </div>
                </template>
                <template v-else-if="tab === 'import_export'">
                    <div class="flex flex--justify-space-between">
                        <div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translateWords('Import data')}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">

                                    <div class="flex">

                                        <div class="dropZone">
                                            <div class="dropZone__title" v-show="!queueProcessing">
                                                {{ selectedFileNameIfExists }}
                                            </div>
                                            <label class="dropZone__actionZone" for="validatedCustomFile">

                                                <template v-if="!queueProcessing">
                                                    <icon v-if="canUploadFile" class="icon" icon="add-file"></icon>
                                                </template>

                                                <span class="dropZone__helpText">
                                                    <template v-if="queueProcessing">
                                                        <template v-if="queue.type !== null">
                                                            {{queue.type + (queue.current < queue.total ? ('  ' + queue.current + '/' + queue.total) : '')}}
                                                        </template>
                                                        <template v-else>
                                                            {{$root.translateWords('Added to queue')}}
                                                        </template>
                                                    </template>
                                                    <template v-else>
                                                        {{$root.translateWords('Click to load file')}}
                                                    </template>
                                                </span>
                                                <span class="dropZone__subResult dropZone__subResult--error"
                                                      v-if="!queueProcessing && queue">
                                                    <template v-if="queue.fatal_error === true">
                                                        {{$root.translateWords('Import was breaked with error')}}
                                                    </template>
                                                    <template v-else-if="queue.breaked === true">
                                                        {{$root.translateWords('Import was breaked successfully')}}
                                                    </template>
                                                </span>
                                            </label>
                                            <input @change="changeFile()"
                                                   v-if="canUploadFile"
                                                   type="file" ref="file"
                                                   class="custom-file-input"
                                                   id="validatedCustomFile"
                                                   required v-show="false"/>
                                            <div class="flex flex--justify-center">
                                                <template v-if="!queueProcessing">
                                                    <a href="javascript:void(0)" class="btn"
                                                       :class="hasNewUploadedFile && !refreshing ? 'btn--confirm': 'btn--disabled'"
                                                       @click="hasNewUploadedFile && !refreshing ? loadFile() : null">
                                                        {{$root.translateWords('Use import')}}
                                                    </a>
                                                </template>
                                                <template v-else>
                                                    <a href="javascript:void(0)"
                                                       class="btn btn--confirm" @click="breakManualImport">
                                                        {{$root.translateWords('Break import')}}
                                                    </a>
                                                </template>
                                            </div>
                                        </div>

                                        <table class="syncInfo" v-if="queue !== null">
                                            <tr class="syncInfo__param syncInfoParam">
                                                <td class="syncInfoParam__name">{{$root.translateWords('Categories'
                                                    + ' imported successfully')}}:
                                                </td>
                                                <td class="syncInfoParam__value">{{queue.success_categories_count || 0}}
                                                </td>
                                            </tr>
                                            <tr class="syncInfo__param syncInfoParam">
                                                <td class="syncInfoParam__name">{{$root.translateWords('Products' +
                                                    ' imported successfully')}}:
                                                </td>
                                                <td class="syncInfoParam__value">
                                                    {{queue.success_products_count || 0}}
                                                </td>
                                            </tr>
                                            <tr class="syncInfo__param syncInfoParam">
                                                <td class="syncInfoParam__name">
                                                    {{$root.translateWords('Warnings')}}:
                                                </td>
                                                <td class="syncInfoParam__value">{{queue.warnings_count || 0}}</td>
                                            </tr>
                                            <tfoot v-if="queue.warnings_count > 0 || queue.fatal_error === true">
                                            <tr>
                                                <td rowspan="2" class="syncInfoParam__link">
                                                    <a href="javascript:void(0)" class="text-link"
                                                       @click="downloadReport">
                                                        {{$root.translateWords('Download report')}}
                                                    </a>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>

                                    </div>

                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.configuration}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <div class="flex flex--align-center">
                                        <v-select
                                                :disabled="refreshing || queue !== null"
                                                :clearable="true"
                                                :searchable="true"
                                                :options="configurations"
                                                :reduce="configuration => configuration.id"
                                                v-model="manual_configuration"
                                                class="input input--inForm"
                                                label="name">
                                            <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                        </v-select>
                                    </div>
                                </div>
                            </div>
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translateWords('Export data')}}:
                                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                                </div>
                                <div class="singleFormGroup__field">
                                    <a href="javascript:void(0)"
                                       class="btn"
                                       :class="queueProcessing ? 'btn--disabled': 'btn--confirm'"
                                       @click="!queueProcessing ? exportProducts() : null">
                                        {{$root.translateWords('Use export')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <modal name="excel-configuration" width="60%" v-if="Object.keys(modalConfiguration).length"
               :title="$root.translateWords(modalConfiguration.edit ? 'Editing configuration': 'Creating configuration')"
               @closed="closeModalConfiguration">
            <template v-slot>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.name}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="text" class="input input--inForm" v-model="modalConfiguration.data.name">
                        </div>
                    </div>
                </div>
                <table class="widget__table table">
                    <thead>
                    <tr class="table__row">
                        <td class="table__heading">{{$root.translate.columns.field}}</td>
                        <td class="table__heading table__heading--text_right">
                            {{$root.translate.columns.column_number}}
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translate.columns.name}}<i class="text-danger">*</i>
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.name" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{ $root.translate.columns.id }}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.extra_1" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translateWords('Product SKU')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.sku" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{ $root.translate.columns.description }}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.description" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{ $root.translate.columns['meta-keywords'] }}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.meta_keywords"
                                   required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translate.columns.price}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.vendor_price" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translate.columns.currency}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.currency_code"
                                   required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translateWords('Price unit')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.price_unit" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{ $root.translate.columns.image }}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.images" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{ $root.translateWords('Stock quantity')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.quantity" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translate.columns.category_id}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.category_id" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translate.columns.manufacturer}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.manufacturer" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translateWords('Wholesale prices, quantities')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.whosale" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translateWords('Delimiter in wholesale prices, quantities')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.whosale_separator"
                                   required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{ $root.translateWords('Price with discount')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.special" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{ $root.translateWords('Min product quantity for order')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.minimum" required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            {{$root.translateWords('Start of attributes')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.attributes_pos_start"
                                   required/>
                        </td>
                    </tr>
                    <tr class="table__row">
                        <td class="table__value">
                            <div class="flex flex--align-center">
                                {{$root.translateWords('Import product variants?')}}
                                <icon icon="icon" class="icon switcher__help"></icon>
                            </div>
                        </td>
                        <td class="table__value">
                            <div class="switcher">
                                <check @click.native="modalConfiguration.data.settings.with_variants = parseInt(!modalConfiguration.data.settings.with_variants)"
                                       :checked="modalConfiguration.data.settings.with_variants === 1"
                                       class="switcher__icon"></check>
                            </div>

                        </td>
                    </tr>
                    <tr class="table__row" v-if="modalConfiguration.data.settings.with_variants">
                        <td class="table__value">
                            {{$root.translateWords('Common id for relation with variants for variation product')}}
                        </td>
                        <td class="table__value">
                            <input class="input " v-model="modalConfiguration.data.settings.extra_2" required/>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="flex flex--justify-end">
                    <a v-if="!modalConfiguration.edit" href="javascript:void(0)" class="btn btn--confirm"
                       @click="storeConfiguration(modalConfiguration.data, true)">
                        {{$root.translateWords('Create configuration')}}
                    </a>
                    <a v-else href="javascript:void(0)" class="btn btn--confirm"
                       @click="storeConfiguration(modalConfiguration.data)">
                        {{$root.translateWords('Save changes')}}
                    </a>
                </div>
            </template>
        </modal>

        <widget-actions :add="modalConfiguration.open ? false: 'addConfiguration'"
                        :trans="{add: $root.translateWords('Create configuration')}"></widget-actions>
    </div>
</template>

<script>

    let standartCustomSettings = {
        name: 'prom-standart',
        refreshing: false,
        settings: {
            'extra_1': 20,
            'name': 1,
            'model': 0,
            'quantity': 13,
            'price_unit': 7,
            'images': 11,
            'manufacturer': 24,
            'minimum': 8,
            'category_id': 14,
            'currency_code': 6,
            'vendor_price': 5,
            'description': 3,
            'meta_keywords': 2,
            'whosale': '10,9',
            'whosale_separator': ';',
            'attributes_pos_start': '',
            'with_variants': 0,
            'extra_2': 27
        }
    };

    export default {
        name: "ExcelImport",
        data() {
            return {
                tab: 'configurations',
                fileName: null,
                columns: [
                    {
                        label: this.$root.translate.columns.configuration_name,
                        field: 'name',
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
                savedOriginalConfigurations: [
                    {
                        name: 'standart',
                        settings: {}
                    }
                ],
                manual_configuration: null,
                configurations: [
                    this.$root.copy(standartCustomSettings)
                ],
                refreshing: false,
                modalConfiguration: {
                    edit: false,
                    open: false,
                    data: this.$root.copy(standartCustomSettings)
                },
                queue: null
            }
        },

        created() {

            axios.get('/admin/sync-configurations', {
                params: {
                    type: 'excel'
                }
            }).then(httpResponse => {

                let self = this;

                this.configurations.forEach(configuration => {
                    if (!configuration.id) configuration.id = 'tmp-' + Math.random(100000, 10000000);
                });

                if (httpResponse.data.length) {

                    for (let configuration of httpResponse.data) {

                        let confIndex = this.configurations.findIndex(conf => {

                            return conf.name === configuration.name;
                        });

                        if (confIndex !== -1) {
                            this.configurations.splice(confIndex, 1);
                        }

                        configuration.refreshing = false;

                        this.configurations.push(configuration);

                        this.savedOriginalConfigurations.push(this.$root.copy(configuration));
                    }

                }

                this.configurations.filter(configuration => typeof configuration.id !== 'number').forEach(configuration => {
                    self.storeConfiguration(configuration, false);
                });


            });

            this.getQueueStatus();

        },

        computed:{
            canUploadFile() {
                return this.queue === null || (this.queue !== null && (this.queue.breaked === true || this.queue.finished === true || this.queue.fatal_error === true));
            },
            selectedFileNameIfExists() {
                return this.fileName ? this.fileName : this.$root.translateWords('File not selected');
            },
            queueProcessing() {
                return this.queue && this.queue.job_id !== null;
            },
            hasNewUploadedFile() {
                return !!this.fileName;
            }
        },
        methods: {
            getQueueStatus() {

                let es = new EventSource('/admin/excel/get-queue-status');

                es.addEventListener('message', event => {
                    let data = JSON.parse(event.data);

                    if(!Object.keys(data).length){
                        this.$set(this, 'queue', null);

                        this.refreshing = false;

                        es.close();
                    }else{
                        this.$set(this, 'queue', data);

                        if (this.manual_configuration === null) this.$set(this, 'manual_configuration', data.configuration_id);

                    }

                }, false);
            },
            isChangedRow(name) {

                let originalPosition = this.savedOriginalConfigurations.findIndex(item => {
                    return item.name === name
                });

                if (originalPosition === -1) {
                    return true;
                }

                let currentPosition = this.configurations.findIndex(item => {
                    return item.name === name
                });

                let current = this.$root.copy(this.configurations[currentPosition]);

                delete current.refreshing;

                let saved = this.$root.copy(this.savedOriginalConfigurations[originalPosition]);

                delete saved.refreshing;

                return JSON.stringify(current) !== JSON.stringify(saved);
            },

            addConfiguration() {

                this.modalConfiguration.edit = false;
                this.modalConfiguration.open = true;
                this.modalConfiguration.data.name = '';
                this.modalConfiguration.data.type = 'excel';
                this.modalConfiguration.data.id = 'tmp-' + Math.random(100000, 10000000);

                this.$root.changePopupShowStatus('excel-configuration', true);
            },

            storeConfiguration(configuration, pushToList = true) {

                let configuration_id = configuration.id;

                let originalPosition = this.savedOriginalConfigurations.findIndex(item => {
                    return item.id === configuration_id
                });

                let request;

                configuration.refreshing = true;

                configuration.type = 'excel';

                if (typeof configuration.id === 'number') {

                    request = axios.put('/admin/sync-configurations/' + configuration_id, configuration);
                } else {
                    request = axios.post('/admin/sync-configurations', configuration);
                }

                request.then(httpResponse => {

                    if (typeof configuration_id !== 'number') {
                        configuration.id = httpResponse.data.id;

                        configuration.refreshing = false;

                        if(pushToList) this.configurations.push(configuration);
                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginalConfigurations, originalPosition, this.$root.copy(configuration));
                    } else {
                        this.savedOriginalConfigurations.push(this.$root.copy(configuration));
                    }

                    this.$root.notify(httpResponse.data);

                    this.closeModalConfiguration();

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    configuration.refreshing = false;
                });

            },

            editConfiguration(index) {
                let configuration = this.configurations[index];

                this.modalConfiguration.edit = true;
                this.modalConfiguration.open = true;

                this.$set(this.modalConfiguration, 'data', configuration);

                this.$root.changePopupShowStatus('excel-configuration', true);
            },

            closeModalConfiguration() {

                this.$root.changePopupShowStatus('excel-configuration', false);

                this.modalConfiguration.open = false;

                this.$set(this.modalConfiguration, 'data', {
                    name: '',
                    refreshing: false,
                    settings: standartCustomSettings
                });

            },

            deleteConfiguration(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let configuration = this.configurations[index];

                    configuration.refreshing = true;

                    axios.delete('/admin/sync-configurations/' + configuration.id)
                        .then(httpResponse => {

                            this.configurations.splice(index, 1);

                            this.$set(this, 'savedOriginalConfigurations', this.$root.copy(this.configurations));

                            this.$root.notify(httpResponse.data);


                        }).catch(error => {
                        if (error.response) this.$root.notify(error.response.data);
                    }).finally(() => {
                        configuration.refreshing = false;
                    });
                });
            },

            changeFile() {
                var files = event.target.files || event.dataTransfer.files;

                if (files[0]) {
                    this.fileName = files[0].name;
                }
            },
            loadFile() {

                let formData = new FormData();

                formData.append('file', this.$refs.file.files[0]);
                formData.append('configuration_id', JSON.stringify(this.manual_configuration));

                this.refreshing = true;

                axios.post('/admin/excel/add-to-queue', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    this.$refs.file.value = '';
                    this.fileName = null;
                    this.$root.notify(response.data);

                    this.$set(this, 'queue', response.data.queue);

                    this.getQueueStatus();

                }).catch(error => {
                    if (error.response){
                        this.$root.notify(error.response.data);
                        this.refreshing = false
                    }
                });
            },
            exportProducts() {
                axios({
                    url: '/admin/excel/export?configuration_id='+this.manual_configuration,
                    method: 'GET',
                    responseType: 'blob'
                }).then(response => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'export.xlsx');
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {

                    error.response.data.text().then((value) => {

                        try{
                            this.$root.notify(JSON.parse(value));
                        }catch (e) {

                        }

                    });

                }).finally(() => this.refreshing = false);
            },

            breakManualImport(){
                axios.delete('excel/break-manual/' + this.queue.id).then(httpResponse => {
                    this.$root.notify(httpResponse.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                });
            }
        }
    }
</script>

<style scoped>

</style>