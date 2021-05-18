<template>
    <div class="external-api">
        <div class="flex flex--justify-space-between">
            <div>
                <h2 class="mainContent__heading">
                    {{$t('external_api.external_apis')}}
                </h2>
            </div>
        </div>
        <div class="singleForm singleForm--inModulesTpl">
            <div class="table_global">
                <div class="table_global__header table-colums table-colums--external-api">
                    <div class="">{{$t("marketing_seo.service")}}</div>
                    <div class="">{{$t("columns.status")}}</div>
                    <div class=""></div>
                    <div class=""></div>
                </div>
                <div v-for="item in services" :key="item.id"
                    class="row table-colums table-colums--external-api">
                        <div class="row__element">
                            <img :src="'/core-static/images/externals-api/' + item.code + '.png'" alt="" class="row__img">
                            <div class="">{{item.name}}</div>
                        </div>
                        <div class="row__element flex flex--justify-space-between">
                            <div class="row__element status" >
                               <div v-if="isAuthorized(item)">
                                   <span class="status__mark status__mark--ok"></span>
                                   <span class="">{{$t("external_api.connected")}}</span>
                                </div>
                               <div v-else-if="!isAuthorized(item)">
                                   <span class="status__mark status__mark--false"></span>
                                   <span class="">{{$t("external_api.not_authorized")}}</span>
                                </div> 
                            </div>
                            <div class="row__element">   
                                <div class="switcherStatus">
                                    <div @click="changeActivityAPI(item, false)" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': item.status == false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="changeActivityAPI(item, true)" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': item.status == true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <template v-if="item.refreshing" >
                            <div class="row__element  flex flex--justify-end">
                                <font-awesome-icon class="text-warning color" icon="circle-notch"
                                                   spin></font-awesome-icon>
                            </div>
                        </template>
                        <div class="row__element  flex flex--justify-end" v-else>
                            <icon @click.native.stop="loginConfirm(item)"
                                    icon="pencil-edit-button" class="icon icon--export"></icon>
                        </div>
                </div>    
            </div>            
        </div>
        <modal name="login" width="865px" v-if="modalLogin.data"
            :title="$t('external_api.authorization_in', {name_api: modalLogin.data.name})"
            @closed="closeModalLogin">
            <template v-slot>
                <div class="preloader" v-if="modalLogin.data.refreshing">
                    <icon class="icon preloader__icon" icon="waiting"></icon>
                </div>
                <div>
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$t('columns.login')}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input"
                                v-model="modalLogin.data.login">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$t('columns.password')}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input"
                                v-model="modalLogin.data.password">
                            </div>
                        </div>
                    </div>
                    <!-- <div class="flex flex--justify-end">
                        <a  @click="saveChangesHourlyMode()"
                            href="javascript:void(0)" class="btn btn--confirm">
                            {{$root.translateWords('Save changes')}}
                        </a>
                    </div> -->
                    <div class="flex flex--justify-end">
                        <a  @click="loginConfirm(modalLogin.data)"
                            href="javascript:void(0)" class="btn btn--confirm">
                            {{$t('external_api.entry')}}
                        </a>
                    </div>
                </div>
            </template>
        </modal>

    </div>
</template>
            
<script>

    export default {
        name: "external-apis",
        data() {
            return {
                services:[
                    // {   
                    //     id: 0,
                    //     img : 'logo1',
                    //     name: 'Amo',
                    //     status: true,
                    //     isActive: true,
                    //     isAuthorized: true
                    // },
                    // {   
                    //     id: 1,
                    //     img : 'logo2',
                    //     name: 'Amo232',
                    //     status: false,
                    //     isActive: true,
                    //     isAuthorized: false
                    // },
                    // {   
                    //     id: 2,
                    //     img : 'logo3',
                    //     name: 'Amo123',
                    //     status: true,
                    //     isActive: false,
                    //     isAuthorized: false
                    // }
                ],
                modalLogin: {
                    edit: false,
                    open: false
                },
            }
        },
        created(){
            this.getExternalApis();
        },
        methods:{
            editServise(item){
                if (!this.Authorized) {
                    this.openModalLogin(item);
                }else{
                    window.location.href = "http://localhost:3000/admin/moysklad/";
                }
            },
            closeModalLogin() {
                this.$root.changePopupShowStatus('login', false);
                this.modalLogin.open = false;
            },
            openModalLogin(item) {
                this.modalLogin.edit = true;
                this.modalLogin.open = true;
                this.$set(this.modalLogin, "data", item);
                this.$root.changePopupShowStatus('login', true);                
            },
            loginConfirm(item){
                let self = this;
                if (!self.modalLogin.open) {
                    item.refreshing = true;
               }else{
                   this.modalLogin.data.refreshing = true;
               }
                axios.post('/admin/externals-api/' + item.code + '/auth', {
                    login: item.login,
                    password: item.password
                }).then(Response => {
                       this.$router.push({name: "external-service"});
                }).catch(error => {
                    if (item.login && item.password) {
                        if (error.response) this.$root.notify(error.response.data);
                    }
                    if (!self.modalLogin.open) {
                        self.openModalLogin(item);
                    }
                }).finally(() => {
                    item.refreshing = false;
                    this.modalLogin.data.refreshing = false;
                });
            },
            changeActivityAPI(item, value){
                let self = this;
                item.refreshing = true;
                axios.put('/admin/externals-api/' + item.code, {
                    status: value
                }).then(Response => {
                   // console.log(Response.data.status);
                    
                    self.$set(item ,'status', value);
                    this.$root.notify(Response.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    item.refreshing = false;
                });
            },
            getExternalApis(){
                let self = this;
                this.$root.waitingServerActionInfo(true, this.$t("words.receiving data"));
                axios.get('/admin/externals-api').then(Response => {
                   // console.log(Response.data);
                    
                    self.$set(self, 'services', Response.data);
                    self.services.forEach(elem => {
                        self.$set(elem, "refreshing", false)
                    });
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.$root.waitingServerActionInfo(false);
                });
            },
            isAuthorized(item){
                return item.login && item.password;
            }
        }
    }
</script>
<style scoped>

</style>
