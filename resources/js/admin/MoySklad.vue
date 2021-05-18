<template>
    <div class="external-service">
            <div class="flex flex--justify-space-between">
                <div>
                    <div class="breadcrumbs">
                    <router-link class="breadcrumbs__link" :to="{name: 'dashboard'}">{{$root.translate.columns.home}}
                    </router-link>
                    -
                    <router-link class="breadcrumbs__link" :to="{name: 'external-api'}">
                        {{$t('external_api.external_apis')}}
                    </router-link>
                </div>
                <h2 class="mainContent__heading flex flex--align-center">
                        <img :src="'/core-static/images/externals-api/' + code + '.png'" alt=""
                             class="img">
                       <span>{{$t('external_api.my_api', {name_api: name})}}</span>
                </h2>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="switcherStatus">
                            <div @click="changeActivityAPI(false)" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active switcherStatus__value--active_off': status === false}">
                                {{$root.translate.columns.disabled_short}}
                            </div>
                            <div @click="changeActivityAPI(true)" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': status === true}">
                                {{$root.translate.columns.enabled_short}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="singleForm singleForm--inModulesTpl">
            <div class="table_global">
                <div class="table_global__header table-colums table-colums--edit-service">
                    <div class="">{{$t("words.Data type")}}</div>
                    <div class="">{{$t("columns.status")}}</div>
                    <div class="">{{$t('external_api.auto_mode')}}</div>
                    <div class="">{{$t('external_api.constant_mode')}}</div>
                    <div class=""></div>
                </div>
                    <div v-for="(item, item_code) in service_info" :key="item_code"
                        class="row table-colums table-colums--edit-service">
                        <div class="row__element">
                            <div class="">{{$t(item.name)}}</div>
                        </div>
                        <div class="row__element status" >
                            <div v-if="!item.sync || !item.sync.info || item.sync.info.stopped">
                                    <div >
                                    <span class="status__mark status__mark--false"></span>
                                    <span class="">{{$t("external_api.not_active")}}</span>
                                </div>
                            </div>
                            <div v-else>
                                <div v-if="item.sync.info.finished">
                                     <span class="status__mark status__mark--ok"></span>
                                     <span class="">{{$t("external_api.completed")}}: {{item.sync.info.current}}/{{item.sync.info.total}}</span>
                                 </div>
                                <div v-else-if="item.sync.info.paused">
                                    <span class="status__mark status__mark--pause"></span>
                                    <span class="">{{$t("external_api.paused")}}: {{item.sync.info.current}}/{{item.sync.info.total}}</span>
                                </div>
                                <div v-else-if="item.sync.info.fatal_error">
                                    <span class="status__mark status__mark--false-bg-red"></span>
                                    <span class="">{{$t("external_api.error_has_occurred")}}: {{item.sync.info.current}}/{{item.sync.info.total}}</span>
                                </div>
                                <div v-else-if="item.sync.info.current <= item.sync.info.total && !item.sync.info.paused">
                                    <span class="status__mark status__mark--ok"></span>
                                    <span class="">{{$t("external_api.upload")}}: {{item.sync.info.current}}/{{item.sync.info.total}}</span>
                                </div>
                                <div v-if="!item.sync.info.stopped && item.sync"
                                     @click="downloadErrorsFile(item.sync.info.warnings_count ,item_code)"
                                     class="row__errors row__errors--link"
                                     :class="{'row__errors--disabled': item.sync.info.warnings_count < 1}">
                                        <span>{{$t("external_api.warnings")}}/{{$t("external_api.errors")}}: {{item.sync.info.warnings_count}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row__element flex flex--align-center">
                            <div v-if="item.automaticMode !== null"
                                class="switcherStatus">
                                <div @click="changeSwitcherAutoMode(item, item_code , false)" class="switcherStatus__value"
                                     :class="{'switcherStatus__value--active switcherStatus__value--active_off': item.automaticMode === false}">
                                    {{$root.translate.columns.disabled_short}}
                                </div>
                                <div @click="changeSwitcherAutoMode(item, item_code , true)" class="switcherStatus__value"
                                     :class="{'switcherStatus__value--active': item.automaticMode === true}">
                                    {{$root.translate.columns.enabled_short}}
                                </div>
                            </div>
                            <div v-else>
                                <span class="row__hyphen"></span> 
                            </div>
                        </div>
                        <div class="row__element">
                            <div v-if="item.hourly_mode"
                                class="flex flex--align-center">
                                <div class="switcherStatus">
                                    <div @click="changeSwitcherHourlyMode(item, item_code, false)" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': item.hourly_mode.status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="changeSwitcherHourlyMode(item, item_code, true)" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': item.hourly_mode.status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                                <div v-if="item.hourly_mode.status === true">
                                    <icon @click.native.stop="openExchangeTime(item, item_code)"
                                        icon="gear" class="icon icon--export"></icon>
                                </div>
                            </div>
                            <div v-else>
                               <span class="row__hyphen"></span> 
                            </div>
                        </div>
                        <div v-if="item.refreshing"
                             class="row__element row__element--icon-group">
                                    <font-awesome-icon class="text-warning color" icon="circle-notch"
                                                       spin></font-awesome-icon>
                        </div>
                        <div class="row__element row__element--icon-group" v-else>
                            <div v-if="!item.sync || !item.sync.info || item.sync.info.stopped || item.sync.info.fatal_error || item.sync.info.finished">
                                    <icon icon="down-load-arrow" 
                                          class="icon icon--load-file"
                                          :class="{'icon--position-left': item_code === 'prices_quantities' || item_code === 'rates',
                                           'options-triangle': item_code  === 'products' || item_code === 'categories' || item_code === 'users'}"
                                          @click.native.stop="downloadFile(item, item_code, false)"></icon>
                                <!-- <div  class="loading-options">
                                    <div class="loading-options__row">
                                        <icon  icon="down-load-arrow" 
                                        class="icon icon--load-file"
                                        :class="{'icon--position-left': item_code === 'prices_quantities' || item_code === 'rates'}">
                                        </icon>
                                        <span>Выгрузить</span>
                                    </div>
                                    <div class="loading-options__row">
                                        <icon  icon="settings" 
                                        class="icon icon--load-file"
                                        :class="{'icon--position-left': item_code === 'prices_quantities' || item_code === 'rates'}">
                                        </icon>
                                        <span>Параметри</span>
                                    </div>
                                </div> -->
                                    <icon v-if="item_code !== 'prices_quantities' && item_code !== 'rates'" icon="upload-load-arrow" 
                                          class="icon icon--load-file "
                                          :class="{'options-triangle': item_code  === 'products' || item_code === 'categories' || item_code === 'users'}"
                                          @click.native.stop="uploadFile(item, item_code, false)"></icon>
                                    <div v-if="item.update_parameters.open"
                                         v-click-outside="() => item.update_parameters.open = false" 
                                         class="loading-options">
                                        <div v-if="item.update_parameters.open === 'download'"
                                             @click.stop="downloadFile(item, item_code, true)"
                                             class="loading-options__row">
                                            <icon  icon="down-load-arrow" 
                                            class="icon icon--load-file"
                                            :class="{'icon--position-left': item_code === 'prices_quantities' || item_code === 'rates'}">
                                            </icon>
                                            <span>{{$t('authorization.download')}}</span>
                                        </div>
                                        <div v-if="item.update_parameters.open === 'upload'"
                                             @click.stop="uploadFile(item, item_code, true)"
                                             class="loading-options__row">
                                            <icon  icon="upload-load-arrow" 
                                            class="icon icon--load-file"
                                            :class="{'icon--position-left': item_code === 'prices_quantities' || item_code === 'rates'}">
                                            </icon>
                                            <span>{{$t('authorization.unload')}}</span>
                                        </div>
                                        <div class="loading-options__row"
                                            @click.stop="openLoadSettings(item, item_code)">
                                            <icon  icon="gear"
                                            class="icon icon--load-file"
                                            :class="{'icon--position-left': item_code === 'prices_quantities' || item_code === 'rates'}">
                                            </icon>
                                            <span>{{$t('authorization.parameters')}}</span>
                                        </div>
                                    </div>
                            </div>
                            <div class="" v-else>
                                <icon   v-if="item.sync.info.paused"
                                        @click.native.stop="restoreSynchronization(item)"
                                        icon="start-button" 
                                        class="icon icon--controle-loading"></icon>
                                <icon   v-if="!item.sync.info.paused"
                                        @click.native.stop="pauseSynchronization(item)"
                                        icon="pause-button" 
                                        class="icon icon--controle-loading"></icon>
                                <icon   @click.native.stop="stopSynchronization(item)"
                                        icon="stop-button"  
                                        class="icon  icon--controle-loading"></icon>
                            </div>
                        </div> 
                </div>    
            </div>
        <modal name="exchange-time" width="865px"
            :title="$t('external_api.set_exchange_time')"
            @closed="closeExchangeTime">
            <template v-slot>
                <div class="preloader" v-if="modal_exchange_time.data.refreshing">
                    <icon class="icon preloader__icon" icon="waiting"></icon>
                </div>
                <div>
                    <div class="flex flex--justify-space-between">
                        <div class="singleFormGroup">
                            <div class="singleFormGroup__title">
                                {{$t('external_api.data_exchange_time')}}:
                            </div>
                            <div class="singleFormGroup__field">
                                <datetime class="vdatetime--time mr--8" input-class="input input--data-exchange-time"
                                    v-model="modal_exchange_time.data.hourly_mode.time"
                                    type="time"
                                    :phrases="datetime.phrases"
                                    value-zone='Europe/Kiev'
                                    format="HH:mm"/>
                            </div>
                        </div>
                        <div class="singleFormGroup">
                            <div class="singleFormGroup__title">
                                {{$t('columns.status')}}:
                            </div>
                            <div class="singleFormGroup__field">
                                <div class="switcherStatus">
                                    <div @click="modal_exchange_time.data.hourly_mode.status = false" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': modal_exchange_time.data.hourly_mode.status  === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="modal_exchange_time.data.hourly_mode.status = true" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': modal_exchange_time.data.hourly_mode.status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex--justify-end">
                        <a  @click="saveChangesHourlyMode()"
                            href="javascript:void(0)" class="btn btn--confirm">
                            {{$root.translateWords('Save changes')}}
                        </a>
                    </div>
                </div>
            </template>
        </modal>
        <modal name="modal-load-settings" width="80%"
            :title="$t('words.Editing configuration')"
            @closed="closeLoadSettings">
            <template v-slot>
                <div class="content-size">
                    <div v-if="modal_load_settings.data.load_type === 'download'"
                         class="text">{{$t('authorization.description_of_download_settings')}}</div>
                    <div v-else
                         class="text">{{$t('authorization.description_of_upload_settings')}}</div>
                    <table class="table mt--32">
                        <thead>
                        <tr class="table__row">
                            <td class="table__heading">{{$t('words.Data type')}}:</td>
                            <td class="table__heading table__heading--offset-right flex flex--justify-end">{{$t('authorization.unloading_status')}}:</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="table__row"
                            v-for="item in modal_load_settings.data.update_parameters[modal_load_settings.data.load_type]">
                            <td class="table__value">
                                <div class="">
                                    {{$t(item.name)}}
                                </div>
                            </td>
                            <td class="table__value flex flex--justify-end">
                                <div class="switcherStatus">
                                    <div @click="changeSwitcherLoadingSettings(item, modal_load_settings.data.code, modal_load_settings.data.load_type, false)" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': item.value === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="changeSwitcherLoadingSettings(item, modal_load_settings.data.code, modal_load_settings.data.load_type, true)" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': item.value === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                
            </template>
        </modal>                    
        </div>
    </div>
</template>
<script>

import ClickOutside from 'vue-click-outside'

export default {
        name: "moy_sklad",
        directives: {
            ClickOutside
        },
        data() {
            return {
                default_service_info:{
                    "products": {
                        refreshing: false,
                        update_parameters:{
                            open: false,
                            download: [
                                {
                                    name:"sync.product_name",
                                    type: "name",
                                    value: false
                                },
                                {
                                    name:"columns.images",
                                    type: "images",
                                    value: false
                                },
                                {
                                    name:"columns.sku",
                                    type: "article",
                                    value: false
                                },
                                {
                                    name:"columns.categories",
                                    type: "categories",
                                    value: false
                                },
                                {
                                    name:"columns.description",
                                    type: "description",
                                    value: false
                                },
                                {
                                    name:"authorization.residues_prices",
                                    type: "price_quantity",
                                    value: false
                                }
                            ],
                            upload:[
                                {
                                    name:"sync.product_name",
                                    type: "name",
                                    value: false
                                },
                                {
                                    name:"columns.images",
                                    type: "images",
                                    value: false
                                },
                                {
                                    name:"columns.sku",
                                    type: "article",
                                    value: false
                                },
                                {
                                    name:"columns.categories",
                                    type: "categories",
                                    value: false
                                },
                                {
                                    name:"columns.description",
                                    type: "description",
                                    value: false
                                },
                            ]
                        },
                        name:'menu.catalog.items.products',
                        automaticMode: false,
                        hourly_mode:null,
                        sync: {
                            info:""
                        }
                    },
                    "categories":{
                        refreshing: false,
                        update_parameters:{
                            open: false,
                            download: [
                                {
                                    name:"sync.product_name",
                                    type: "name",
                                    value: false
                                },
                                {
                                    name:"authorization.category_hierarchy",
                                    type: "category_hierarchy",
                                    value: false
                                }
                            ],
                            upload:[
                                {
                                    name:"sync.product_name",
                                    type: "name",
                                    value: false
                                },
                                {
                                    name:"authorization.category_hierarchy",
                                    type: "category_hierarchy",
                                    value: false
                                }
                            ]
                        },
                        name:'menu.catalog.items.categories',
                        automaticMode: false,
                        hourly_mode: null,
                        sync: {
                            info:""
                        }
                    },
                    "users":{
                        refreshing: false,
                        update_parameters:{
                            open: false,
                            download: [
                                {
                                    name:"columns.firstname",
                                    type: "first_name",
                                    value: false
                                },
                                {
                                    name:"columns.surname",
                                    type: "last_name",
                                    value: false
                                },
                                {
                                    name:"Email",
                                    type: "email",
                                    value: false
                                },
                                {
                                    name:"columns.telephone",
                                    type: "phone_number",
                                    value: false
                                }
                            ],
                            upload:[
                                {
                                    name:"sync.product_name",
                                    type: "name",
                                    value: false
                                },
                                {
                                    name:"Email",
                                    type: "email",
                                    value: false
                                },
                                {
                                    name:"columns.telephone",
                                    type: "phone_number",
                                    value: false
                                }
                            ]
                        },
                        name:'menu.users.heading',
                        automaticMode: false,
                        hourly_mode: null,
                        sync: {
                            info:""
                        }
                    },
                    "orders":{
                        refreshing: false,
                        update_parameters:{
                            open: false,
                            download: {

                            },
                            upload:{
                                
                            }
                        },
                        name:'menu.orders-callback.items.orders',
                        automaticMode: false,
                        hourly_mode: null,
                        sync: {
                            info:""
                        }
                    },
                    "prices_quantities":{
                        refreshing: false,
                        update_parameters:{
                            open: false,
                            download: {

                            },
                            upload:{
                                
                            }
                        },
                        name:'external_api.goods_leftovers_prices',
                        automaticMode: null,
                        hourly_mode: {
                           status: false,
                           time: '03:00'
                        },
                        sync: {
                            info:""
                        }
                    },
                    "rates":{
                        refreshing: false,
                        update_parameters:{
                            open: false,
                            download: {

                            },
                            upload:{
                                
                            }
                        },
                        name:'sync.currency_rate',
                        automaticMode: null,
                        hourly_mode: {
                           status: false,
                           time: '03:00'
                        },
                        sync: {
                            info:""
                        }
                    },
                    "currencies":{
                        refreshing: false,
                        update_parameters:{
                            open: false,
                            download: {

                            },
                            upload:{
                                
                            }
                        },
                        name:'menu.localisation.items.currencies',
                        automaticMode: null,
                        hourly_mode: null,
                        sync: {
                            info:""
                        }
                    }
                },

                name: "",
                status:"",
                code: "",
                service_info:{
                },
                modal_exchange_time:{
                    open: false,
                    edit: false
                },
                modal_load_settings:{
                    open: false,
                    edit: false
                },
                datetime: {
                    phrases: {ok: this.$root.translateWords('Apply'), cancel: this.$root.translateWords('Reset')},
                },
            }
        },
        created(){
            this.getSettings();
        },
        methods:{
            //admin/externals-api/{external_api_code|moy-sklad}/edit получення при загрузці	
        //admin/externals-api/{external_api_code|moy-sklad}/{data_type|products}/automatic-mode  
            changeSwitcherAutoMode(item, code, value){
                let self = this;
                item.refreshing = true;
                //•	admin/externals-api/{external_api_code|moy-sklad}/{data_type|products}/automatic-mode
                axios.put('/admin/externals-api/moy-sklad/' + code + '/automatic-mode', {
                    //name: item.type,
                    status: value
                }).then(Response => {
                  //  console.log(Response);
                
                    self.$set(item ,'automaticMode', value);
                    self.$root.notify(Response.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                        item.refreshing = false;
                });
            },
            changeSwitcherHourlyMode(item, code, value){
                let self = this;
                item.refreshing = true;
                //this.$set(item, "always_mode", value)
                axios.put('/admin/externals-api/moy-sklad/' + code + '/hourly-mode', {
                    status: value
                }).then(Response => {
                  //  console.log(item);
                    self.$set(item.hourly_mode ,'status', value);
                    self.$root.notify(Response.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                        item.refreshing = false;
                });
            },
            changeSwitcherLoadingSettings(item, code, load_type, value){
                let self = this;
                let pushingData = {};
                self.$set(item ,'value', value);
                this.modal_load_settings.data.update_parameters[load_type].forEach(element => {
                    pushingData[element.type] = element.value;
                });
                console.log(pushingData);
                axios.put('/admin/externals-api/moy-sklad/' + code + '/parameters/' + load_type, 
                    pushingData
                ).then(Response => {
                  //  console.log(item);
                    //self.$set(item ,'value', value);
                    self.$root.notify(Response.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                    self.$set(item ,'value', !value);
                });
            },
            saveChangesHourlyMode(){
                let self = this;                
                this.modal_exchange_time.data.refreshing = true;
                axios.put('/admin/externals-api/moy-sklad/' + this.modal_exchange_time.data.code + '/hourly-mode', {
                    status: this.modal_exchange_time.data.hourly_mode.status,
                    time: this.modal_exchange_time.data.hourly_mode.time
                }).then(Response => {
                    self.$set(self.service_info[self.modal_exchange_time.data.code] ,'hourly_mode',self.modal_exchange_time.data.hourly_mode)
                    self.$root.notify(Response.data);
                    self.closeExchangeTime();
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.modal_exchange_time.data.refreshing = false;
                });
            },
            uploadFile(item, code, settingOpen){
                let self = this;
                if (!settingOpen) {
                    if (code === "products" || code === "categories" || code === "users") {
                        this.closeLoadSettingsMenu();
                        item.update_parameters.open = 'upload';
                        return 1;
                    }
                }
               //admin/externals-api/{external_api_code|moy-sklad}/{data_type|products}/upload
               item.refreshing = true;
               axios.post('/admin/externals-api/moy-sklad/' + code + '/upload').then(Response => {
                //   console.log(Response);
                    if (Response.data.sync.warnings_count === null) {
                        Response.data.sync.warnings_count = 0;
                    } 
                    self.$set(item.sync, 'info', Response.data.sync);
                    self.$set(item.sync, 'id', Response.data.sync.id)
                    self.$root.notify(Response.data);
                    self.getActualInfo(item);
                    item.update_parameters.open = false;
                    this.closeLoadSettingsMenu();
               }).catch(error => {
                   if (error.response) this.$root.notify(error.response.data);
               }).finally(() => {
                       item.refreshing = false;
               });
                
            },

            downloadFile(item, code, settingOpen){
                let self = this;
                if (!settingOpen) {
                    if (code === "products" || code === "categories" || code === "users") {
                        this.closeLoadSettingsMenu();
                        item.update_parameters.open = 'download';
                        return 1;
                    }
                }
                item.refreshing = true;
                //admin/externals-api/{external_api_code|moy-sklad}/{data_type|products}/download
                axios.post('/admin/externals-api/moy-sklad/' + code + '/download').then(Response => {
                //     console.log(Response);
                //   console.log(Response.data.sync);

                 if (Response.data.sync.warnings_count === null) {
                     Response.data.sync.warnings_count = 0;
                 } 

                    self.$set(item.sync, 'info', Response.data.sync);
                    self.$set(item.sync, 'id', Response.data.sync.id)
                    self.$root.notify(Response.data);
                    self.getActualInfo(item);
                    this.closeLoadSettingsMenu();
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                        item.refreshing = false;
                });
                
            },
            closeExchangeTime() {
                this.$root.changePopupShowStatus('exchange-time', false);
                this.modal_exchange_time.open = false;
            },
            closeLoadSettings() {
                this.$root.changePopupShowStatus('modal-load-settings', false);
                this.modal_load_settings.open = false;
            },
            openExchangeTime(item, code) {
                let modal_data = this.$root.copy(item);
                this.$set(this.modal_exchange_time, "data", modal_data);
                this.$set(this.modal_exchange_time.data, "code", code);
                this.modal_exchange_time.edit = true;
                this.modal_exchange_time.open = true;
                this.$root.changePopupShowStatus('exchange-time', true);
            },
            openLoadSettings(item, code) {
                //let modal_data = this.$root.copy(item);
                this.modal_load_settings.edit = true;
                this.modal_load_settings.open = true;
                this.$set(this.modal_load_settings, "data", item);
                this.$set(this.modal_load_settings.data, "code", code);
                this.$set(this.modal_load_settings.data, "load_type", item.update_parameters.open);
                this.$root.changePopupShowStatus('modal-load-settings', true);
                item.update_parameters.open = false;
            },
            //{external_api_code|moy-sklad}/&sync_id={sync.id}
            stopSynchronization(item){
                let self = this;
                item.refreshing = true;
                axios.put('/admin/externals-api/moy-sklad/stop-sync', {
                    sync_id: item.sync.id
                }).then(Response => {
                    if (Response.data.sync.warnings_count === null) {
                        Response.data.sync.warnings_count = 0;
                    }
                    // self.$set(item ,'auto_mode', value)
                    self.$set(item.sync, 'info', Response.data.sync);
                    self.$root.notify(Response.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    item.refreshing = false;
                    this.closeLoadSettingsMenu();
                });
            },
            //admin/externals-api/{external_api_code|moy-sklad}/pause-sync&sync_id={sync.id}
            pauseSynchronization(item){
                let self = this;
                item.refreshing = true;
                axios.put('/admin/externals-api/moy-sklad/pause-sync', {
                    sync_id: item.sync.id
                }).then(Response => {
                 //   console.log(Response);
                    if (Response.data.sync.warnings_count === null) {
                        Response.data.sync.warnings_count = 0;
                    }
                    // self.$set(item ,'auto_mode', value)
                    self.$set(item.sync, 'info', Response.data.sync);
                    self.$root.notify(Response.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                        item.refreshing = false;
                        this.closeLoadSettingsMenu();
                });
            },
            //admin/externals-api/{external_api_code|moy-sklad}/resume-sync&sync_id={sync.id}
            restoreSynchronization(item){
                let self = this;
                item.refreshing = true;
                axios.put('/admin/externals-api/moy-sklad/resume-sync', {
                    sync_id: item.sync.id
                }).then(Response => {
                    // self.$set(item ,'auto_mode', value)
               // setInterval(() => {
               //     self.getActualInfo(item);
               // }, 10000);
                if (Response.data.sync.warnings_count === null) {
                    Response.data.sync.warnings_count = 0;
                }
                self.$set(item.sync, 'info', Response.data.sync);
                self.$root.notify(Response.data);
                self.getActualInfo(item);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    item.refreshing = false;
                    this.closeLoadSettingsMenu();
                });
            },
            getActualInfo(item){
                let self = this;
                // /admin/externals-api/{external_api_code|moy-sklad}/sync
                    axios.get('/admin/externals-api/moy-sklad/info-sync', {
                    params: {
                        sync_id: item.sync.id
                    }
                }).then(Response => {
                   //     console.log(Response.data.info);
                        //console.log(10000);
                        if (Response.data.info.warnings_count === null) {
                            Response.data.info.warnings_count = 0;
                        }
                        self.$set(item.sync, 'info', Response.data.info);
                        
                            setTimeout(() => {
                                if (item.sync && !item.sync.info.paused && !item.sync.info.stopped && !item.sync.info.fatal_error && !item.sync.info.finished) {
                                    self.getActualInfo(item);
                                }
                            }, 10000);
                        
                    }).catch(error => {
                        if (error.response) this.$root.notify(error.response.data);
                    });
            },                  
            getSettings(){
                let self = this;
                this.$root.waitingServerActionInfo(true, this.$t("words.receiving data"));
                axios.get('/admin/externals-api/moy-sklad').then(Response => {
                  //  console.log(Response.data.name, "name");
                    
                    self.$set(self, "name", Response.data.name);
                    self.$set(self, "status", Response.data.status);
                    self.$set(self, "code", Response.data.code);
                    if (Response.data.settings !== null) {
                        let service_info_data = {};
                        //let default_service = self.$root.copy(self.default_service_info[element]);
                        for (const element in self.default_service_info) {
                          //  console.log(element);
                            if (Response.data.settings[element]) {
                               // console.log( Object.assign(self.$root.copy(self.default_service_info[element]), Response.data.settings[element]), "for Se");
                                

                                                if (Response.data.settings[element].update_parameters) {
                                                    let download_data = [];
                                                    Response.data.settings[element].update_parameters.open = false;
                                                
                                                    if (Response.data.settings[element].update_parameters.download) {
                                                        self.default_service_info[element].update_parameters.download.forEach(elem => {
                                                            for (let response_elem in Response.data.settings[element].update_parameters.download) {
                                                                if (response_elem === elem.type) {
                                                                    download_data.push({
                                                                        name: elem.name,
                                                                        type: elem.type,
                                                                        value: Response.data.settings[element].update_parameters.download[response_elem]
                                                                    });
                                                                }
                                                            }
                                                        });
                                                        Response.data.settings[element].update_parameters.download = download_data;
                                                        console.log(download_data);
                                                    }else{
                                                        Response.data.settings[element].update_parameters.download = self.default_service_info[element].update_parameters.download;
                                                    }
                                                    if (Response.data.settings[element].update_parameters.upload) {
                                                        let upload_data = [];
                                                        self.default_service_info[element].update_parameters.upload.forEach(elem => {
                                                            for (let response_elem in Response.data.settings[element].update_parameters.upload) {
                                                                if (response_elem === elem.type) {
                                                                    upload_data.push({
                                                                        name: elem.name,
                                                                        type: elem.type,
                                                                        value: Response.data.settings[element].update_parameters.upload[response_elem]
                                                                    });
                                                                }
                                                            }
                                                        });
                                                        Response.data.settings[element].update_parameters.upload = upload_data;
                                                        console.log(upload_data);
                                                    }else{
                                                        Response.data.settings[element].update_parameters.upload = self.default_service_info[element].update_parameters.upload;
                                                    }
                                                }


                                service_info_data[element] = Object.assign(self.$root.copy(self.default_service_info[element]), Response.data.settings[element]);
                            }else{
                                service_info_data[element] = self.default_service_info[element];
                            }
                        };
                        
                        self.$set(self, 'service_info', service_info_data);
                        
                        for (let service_item in self.service_info) {
                            if (self.service_info[service_item].sync.id) {
                                self.getActualInfo(self.service_info[service_item]);
                            }
                        }
                    }else{
                        self.$set(self, 'service_info', self.default_service_info);
                    }

                   // console.log(self.service_info, "serv info");
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.$root.waitingServerActionInfo(false);
                });
            },
            changeActivityAPI(value){
                this.$root.waitingServerActionInfo(true, this.$t("words.receiving data"));
                let self = this;
                axios.put('/admin/externals-api/' + this.code, {
                    status: value
                }).then(Response => {                    
                    self.$set(self ,'status', value);
                    self.$root.notify(Response.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.$root.waitingServerActionInfo(false);
                });
            },
            downloadErrorsFile(errors ,code) {
                if (errors >= 1) {
                    axios({
                        url:'/admin/externals-api/moy-sklad/' + code + '/download-log',
                        method: 'GET',
                        responseType: 'blob',
                    }).then(response => {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', 'report-moy-sklad-' + code + '.log');
                        document.body.appendChild(link);
                        link.click();
                    }).catch(error => {
                        if (error.response) this.$root.notify(error.response.data);
                    }).finally(() => this.refreshing = false);
                }
            },
            closeLoadSettingsMenu(){
                for (const element in this.service_info) {
                    if (this.service_info[element].update_parameters !== undefined) {
                        this.service_info[element].update_parameters.open = false;
                    }
                }
            }
            // hiddeSettings(item){
            //     item.update_parameters.open = false
            // }
        }
}
</script>
<style scoped>

</style>