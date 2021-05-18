<template>
    <div>
        <div class="flex flex--justify-space-between">
            <div>
                <h2 class="mainContent__heading">
                    Синхронизация с поставщиками
                </h2>
            </div>
        </div>
        <div class="singleForm">
            <div class="tabs">
                <a @click="tab = 'products'" href="javascript:void(0)"
                   class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'products'}">
                    {{$root.translate.columns.products}}
                </a>
                <a @click="tab = 'categories'" href="javascript:void(0)" class="tabs__heading"
                   :class="{'tabs__heading--active': tab === 'categories'}">
                    {{$root.translate.columns.categories}}
                </a>
            </div>
            <div v-show="tab === 'products'">
                <!--  :settings="{offset_top: true}" :currencies="currencies" @filter="expandFilter" v-on:clear-filter-option="clearFilterParam"
                :pagination-options="{
                        enabled: false
                    }"-->

                <div class="filter mt--32">

                    <div class="filter__name">
                        <input @input="debounceFunction(onColumnFilter)" type="text" class="input"
                               v-model="serverParams.name_sku"
                               :placeholder="$root.translateWords('Search products by name or sku')">
                        <icon icon="search" class="icon filter__name_icon"></icon>
                    </div>

                    <advanced-filter @filter="expandFilter"
                                     v-on:clear-filter-option="clearFilterParam"
                                     :settings="{offset_top: true}"
                                     :current_options="['suppliers_categories', 'suppliers', 'quantity', 'supplier_type']"></advanced-filter>
                </div>
                <vue-good-table
                    @on-page-change="onPageChange"
                    @on-column-filter="onColumnFilter"
                    @on-per-page-change="onPerPageChange"

                    mode="remote"
                    :columns="columns"
                    :rows="rows"
                    :totalRows="totalRecords"
                    :isLoading.sync="isLoading"
                    styleClass="table"
                    row-style-class="table__row table__row--text_left">
                        <template slot="table-column" slot-scope="props">
                            <span v-if="props.column.field == 'sku' || props.column.field == 'quantity' || props.column.field == 'updated_at'"
                            @click.stop="changeSort(props.column.field)"
                            class="curcor-active">
                                {{props.column.label}}
                                <icon icon="right-arrow" class="icon icon--rotate-down"
                                :class="{'icon--hidden': chack_field(props.column.field) === false,
                                'icon--rotate-top': chack_field(props.column.field) === true}"></icon>
                            </span>
                            <span v-else>
                                {{props.column.label}}
                            </span>
                        </template>
                        <template slot="table-row" slot-scope="props">
                            <template v-if="props.column.field == 'sku'">
                                <input type="text" class="input" v-if="!rows[props.index].sku.length"
                                       v-model="rows[props.index].future_sku">
                                <template v-else>
                                    {{rows[props.index].sku}}
                                </template>
                            </template>
                            <template v-else-if="props.column.field == 'name'">
                                {{rows[props.index].name}}
                            </template>
                            <template v-else-if="props.column.field == 'suppliers'">
                                {{rows[props.index].supplier_code}}
                            </template>
                            <template v-else-if="props.column.field == 'quantity'">
                                {{rows[props.index].quantity}}
                            </template>
                            <template v-else-if="props.column.field == 'price'">
                                {{rows[props.index].price}}
                            </template>
                            <template v-else-if="props.column.field == 'type'">
                                <div v-if="rows[props.index].type === 0">
                                    <span class="status__mark status__mark--false-bg-red"></span>
                                    Не создано
                                </div>
                                <div v-if="rows[props.index].type === 1">
                                    <span class="status__mark status__mark--false"></span>
                                    Нет привязки
                                </div>
                                <div v-if="rows[props.index].type === 2">
                                    <span class="status__mark status__mark--ok"></span>
                                    Создано
                                </div>
                            </template>
                            <template v-else-if="props.column.field == 'updated_at'">
                                {{rows[props.index].updated_at}}
                            </template>
                            <template v-else-if="props.column.field == 'icon'">
                                <template v-if="!rows[props.index].sku.length">
                                    <a class="table__action" href="javascript:void(0)" @click.stop="updateSku(rows[props.index])">
                                        <icon icon="floppy-disk" class="icon"></icon>
                                    </a>
                                </template>
                                <template v-else>
                                    <div v-if="rows[props.index].type === 0">
                                        <template v-if="rows[props.index].refreshing">
                                            <font-awesome-icon class="text-warning table__value table__value--text_right" icon="circle-notch" spin></font-awesome-icon>
                                        </template>
                                        <template v-else>
                                            <icon @click.native.stop="craeteSuppliersProduct(rows[props.index])" icon="plus-bold" class="icon icon--export"></icon>
                                            <!-- <icon @click.native.stop="editModalProductsList(rows[props.index])"
                                            icon="search" class="icon icon--export"></icon> -->
                                        </template>
                                    </div>
                                    <div v-else-if="rows[props.index].type === 1">
                                        <template v-if="rows[props.index].refreshing">
                                            <font-awesome-icon class="text-warning table__value table__value--text_right" icon="circle-notch" spin></font-awesome-icon>
                                        </template>
                                        <template v-else>
                                            <icon @click.native.stop="createRelationWithProduct(1, rows[props.index])" icon="chain" class="icon icon--export"></icon>
                                            <icon @click.native.stop="editModalProductsList(rows[props.index])"
                                                  icon="search" class="icon icon--export"></icon>
                                        </template>
                                    </div>
                                    <div v-else-if="rows[props.index].type === 2"></div>
                                </template>
                            </template>
                        </template>
                </vue-good-table>
                <pagination
                        v-if="serverParams.perPage < totalRecords"
                        :current-page="serverParams.page"
                        :per-page="serverParams.perPage"
                        :total="totalRecords"
                        :from-records="serverParams.fromRecords"
                        :to-records="serverParams.toRecords"
                        :pageChanged="onPageChange"
                        :perPageChanged="onPerPageChange">
                </pagination>
            </div>

            <div v-show="tab === 'categories'">
                <table class="table mt--48">
                    <thead>
                        <tr class="table__heading table__heading">
                            <td class="table__row table__row--header-padding ">
                                <span>Категории</span>
                            </td>
                            <td class="table__row"></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table__row"
                            v-for="(item) in categories_list" :key="item.id">
                            <td class="table__value table__value--inConfiguration">
                                <p>{{item.name}}</p>
                            </td>
                            <td class="table__value table__value--inConfiguration table__value--text_right">
                                <template>
                                    <icon @click.native.stop="editModalCategories(item)"
                                    icon="pencil-edit-button" class="icon icon--export"></icon>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <modal name="modal_products" width="80%" v-if="Object.keys(modal_products_list).length"
                :title="$root.translateWords(modal_products_list.edit ? 'Editing configuration': 'Creating configuration')"
                @closed="closeModal">
                <template v-slot>
                    <div v-if="search_result.length !== 0">
                        <div class="searchResultsItem searchResultsItem--related searchResultsItem--padding-goods-list border-bottom">
                            <div class="flex flex--align-center">
                                <img class="searchResultsItem__img searchResultsItem__img--suppliers"
                                :src="search_result.image" alt="" >
                                <div class="flex flex--column flex--justify-center">
                                    <div class="searchResultsItem__name">{{search_result.name}}</div>
                                    <div class="searchResultsItem__sku">{{search_result.sku}}</div>
                                </div>
                            </div>
                            <div class="XmlExport__delete-item-element">
                                <icon @click.native.stop="searchPickedDelete()"
                                icon="delete" class="icon icon--export"></icon>
                            </div>
                        </div>
                        <div class="flex flex--justify-end mt--48">
                            <a @click.stop="createRelationWithProduct(0, modal_products_list.data)"
                                href="javascript:void(0)" class="btn btn--confirm">
                                Создать
                            </a>
                        </div>
                    </div>
                    <div v-else-if="!search_result.length" class="singleFormGroup singleFormGroup--max-height">
                        <v-select
                        @search="onSearchProducts"
                            :components="{Deselect, OpenIndicator}"
                            :multiple="false"
                            :clearable="true"
                            :searchable="true"
                            :no-drop="true"
                            :options="search_products_list"
                            v-model="search_result"
                            class="vs--singal-search vs--single-flex border-bottom border-bottom--padding"
                            placeholder="Поиск"
                            label="name" />
                        <div v-if="search_products_list.length" key="search_products_list">
                            <div v-bar="{ useScrollbarPseudo: true, preventParentScroll: true }"
                                class="singleFormGroup singleFormGroup--max-height mt--32">
                                <div>
                                    <div v-for="item in search_products_list" :key="item.id"
                                        class="searchResultsItem flex flex--align-center"
                                        @click.stop="dropdownSelect(item)">
                                        <img class="searchResultsItem__img searchResultsItem__img--suppliers"
                                        :src="item.image" alt="">
                                        <div class="flex flex--column flex--justify-space-between">
                                            <div class="searchResultsItem__name">{{item.name}}</div>
                                            <div class="searchResultsItem__sku">{{item.sku}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </modal>

            <modal name="modal_categories" width="80%" v-if="Object.keys(modal_categories_list).length"
                :title="$root.translateWords('Поиск товаров')"
                @closed="closeModal">
                <template v-slot>
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__title">
                            {{$root.translateWords('Main category')}}:
                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                        </div>
                        <div class="singleFormGroup__field ">
                            <div class="flex flex--align-center">
                                <v-select
                                        :clearable="true"
                                        :searchable="true"
                                        :options="site_categories"
                                        v-model="modal_categories_list.data.category_id"
                                        class="input input--inForm"
                                        :reduce="category => category.id"
                                        label="name">
                                    <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                </v-select>
                            </div>
                        </div>
                        <div>
                            <div class="singleFormGroup__title mt--32">
                                Исключить характеристики:
                            </div>
                            <v-select
                                    multiple
                                    :clearable="true"
                                    :searchable="true"
                                    :clear-search-on-select="true"
                                    :options="attributes_list"
                                    :reduce="attribute => attribute.id"
                                    v-model="modal_categories_list.data.to_attributes"
                                    class="input input--inForm vs--multiply pt--0"
                                    :components="{Deselect, OpenIndicator}"
                                    label="name"
                                    :placeholder="$root.translateWords('Create value')"/>
                        </div>
                        <div class="flex flex--justify-end mt--48">
                            <a @click.stop="saveModalCategoriesListChanges()"
                                href="javascript:void(0)" class="btn btn--confirm">
                                Сохранить изменения
                            </a>
                        </div>
                    </div>
                </template>
            </modal>
        </div>
    </div>
</template>


<script>

        export default {
            name: "Suppliers",
            components: {
                //UploadThumb,
                'advanced-filter': require('./FilterComponent').default,
                'pagination': require('./paginationComponent').default,
            },
            data(){
                return{
                    serverParams: {
                        columnFilters: {},
                        sort_column: "quantity",
                        sort_direction: 'desc',
                        page: 1,
                        perPage: 400,
                        fromRecords: null,
                        toRecords: null,
                        sort: [
                            // {
                            //     field: "quantity",
                            //     type: "desc"
                            // },
                            // {
                            //     field: "sku",
                            //     type: "asc"
                            // },
                            {
                                field: "quantity",
                                type: "desc"
                            },
                        ]
                        },
                    search_products_list:[],
                    rows:[],
                    columns:[
                            {
                            label: 'Артикул',
                            field: 'sku',
                            thClass: 'table__heading',
                            tdClass: 'table__value',
                            sortable: true,
                            direction:'asc'
                        },
                        {
                            label: this.$root.translate.columns.name,
                            field: 'name',
                            thClass: 'table__heading',
                            tdClass: 'table__value table__value--name',
                            sortable: false,
                        },
                        {
                            label: 'Поставщик',
                            field: 'suppliers',
                            thClass: 'table__heading',
                            tdClass: 'table__value',
                            sortable: false,
                        },
                        {
                            label: this.$root.translate.columns.quantity,
                            field: 'quantity',
                            thClass: 'table__heading',
                            tdClass: 'table__value',
                            sortable: true,
                        },
                        {
                            label: this.$root.translate.columns.price,
                            field: 'price',
                            thClass: 'table__heading',
                            tdClass: 'table__value',
                            sortable: false,
                        },
                        {
                            label: 'РРЦ',
                            field: 'rrc_price',
                            thClass: 'table__heading',
                            tdClass: 'table__value',
                            sortable: false,
                        },
                        {
                            label: this.$root.translate.columns.status,
                            field: 'type',
                            thClass: 'table__heading',
                            tdClass: 'table__value',
                            sortable: false,
                        },
                        {
                            label: 'Обновлено',
                            field: 'updated_at',
                            thClass: 'table__heading',
                            tdClass: 'table__value',
                            sortable: false,
                        },
                        {
                            label: " ",
                            field: 'icon',
                            thClass: 'table__heading table__heading--text_right',
                            tdClass: 'table__value table__value--text_right',
                            sortable: false,
                        }
                    ],
                    isLoading: false,
                    totalRecords: 0,
                    tab: "products",
                    search_result: '',
                    savedOriginal:[],
                    modal_products_list:{
                        edit: false,
                        open: false
                    },
                    modal_categories_list:{
                        edit: false,
                        open: false,
                    },
                    categories_list:[],
                    site_categories:[],
                    attributes_list:[],
                    Deselect: {
                        render: createElement => createElement('icon', {
                            class: 'icon',
                            props: {
                                icon: 'error'
                            }
                        }),
                    },
                    OpenIndicator: {
                        render: createElement => createElement('span', ''),
                    },
                }
            },
                created(){
                    this.loadSuppliersProducts();
                    this.getCategoriesSuppliers();
                    this.getAttributes();
                    this.getSiteCategories();
                },
                methods:{
                    editModalCategories(item){
                        let self = this;
                        let configuration = item
                        this.modal_categories_list.edit = true;
                        this.modal_categories_list.open = true;
                        this.$set(this.modal_categories_list, 'data', configuration);
                        this.$root.changePopupShowStatus('modal_categories', true);
                    },
                    editModalProductsList(item){
                        let self = this;
                        let configuration = item;
                        this.modal_products_list.edit = true;
                        this.modal_products_list.open = true;
                        this.$set(this.modal_products_list, 'data', configuration);
                        this.$root.changePopupShowStatus('modal_products', true);
                    },
                    closeModal() {
                        this.$root.changePopupShowStatus('modal_products', false);
                        this.$root.changePopupShowStatus('modal_categories', false);
                        this.modal_products_list.open = false;
                        this.modal_categories_list.open = false;
                    },
                    getAttributes(){
                        let self = this;
                        axios.get('/admin/attributes', {
                            params: {
                                with_translate: true
                            }
                        }).then(Response=>{
                            self.$set(self, 'attributes_list', Response.data.attributes);
                        })
                        self.closeModal()
                    },
                    getCategoriesSuppliers(){
                        let self = this;
                        axios.get('/admin/suppliers-categories').then(Response => {
                            Response.data.forEach(elem => elem.to_attributes = elem.to_attributes.map(item => item.attribute_id));
                            self.$set(self, 'categories_list', Response.data)
                        })
                    },
                    getSiteCategories(){
                        let self = this;
                        axios.get('/admin/categories',{
                            params:{
                                    autocomplete: true
                                }
                        }).then(Response => {
                            self.$set(self, 'site_categories', Response.data.categories);
                        })
                    },
                    craeteSuppliersProduct(product){
                        product.refreshing = true;
                        axios.post('/admin/suppliers', {
                            supplier_id: product.id
                        }).then(Response => {
                            product.type = 2;
                            this.$root.notify(Response.data);
                            product.refreshing = true;
                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            product.refreshing = false;
                        });
                    },
                    createRelationWithProduct(type, product){
                        let self = this;
                        let request;
                        product.refreshing = true;
                        if (type === 0) {
                            request = axios.put('/admin/suppliers/' + self.modal_products_list.data.id,{
                                supplier_id: self.modal_products_list.data.id,
                                product_id: self.search_result.id
                            })
                        }
                        else if (type === 1) {
                            request = axios.put('/admin/suppliers/' + product.id,{
                                supplier_id: product.id,
                            });
                        }
                        request.then(Response=>{
                            product.type = 2;
                            this.$root.notify(Response.data);
                            product.refreshing = false;

                            this.$root.changePopupShowStatus('modal_products', false);
                            this.$root.changePopupShowStatus('modal_categories', false);
                            this.modal_products_list.open = false;
                            this.modal_categories_list.open = false;
                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                             product.refreshing = false;
                        });
                    },
                    updateSku(product){

                        product.refreshing = true;

                        axios.put('/admin/suppliers/' + product.id + '/sku', {
                            sku: product.future_sku
                        }).then(Response => {

                            this.$root.notify(Response.data);

                            product.type = Response.data.type;
                            product.sku = product.future_sku;

                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            product.refreshing = false;
                        });
                    },
                    saveModalCategoriesListChanges(){
                        let self = this;

                        axios.put('/admin/suppliers-categories/' + self.modal_categories_list.data.id,{
                            attributes: self.modal_categories_list.data.to_attributes,
                            category_id: self.modal_categories_list.data.category_id
                        }).then(Response => {
                            this.$root.notify(Response.data);
                            this.$root.changePopupShowStatus('modal_products', false);
                            this.$root.changePopupShowStatus('modal_categories', false);
                            this.modal_products_list.open = false;
                            this.modal_categories_list.open = false;
                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        });
                    },
                    getSearchProductsList: _.debounce((phrase, self, loading) => {
                         axios.get('/admin/products', {
                             params: {
                                 phrase: phrase,
                                 autocomplete: true
                             }
                         }).then(Response => {
                                self.$set(self, 'search_products_list', Response.data);
                                loading(false)
                            });
                    }, 500),
                    async loadSuppliersProducts() {
                        let self = this;

                        this.$emit('start-loading');

                        return axios.get('/admin/suppliers', {
                            params: this.serverParams
                        }).then(response => {
                             this.totalRecords = response.data.total;
                             if (response.data.data.length === 0 && this.totalRecords > 0) {
                                 this.onPageChange({currentPage: 1});
                             } else {
                                 this.serverParams.fromRecords = response.data.from;
                                 this.serverParams.toRecords = response.data.to;
                                 if (response.data.length) {
                                     response.data.data.forEach(product => {
                                         product.refreshing = false;
                                     });
                                 }

                                 response.data.data.map(elem => {
                                    elem.refreshing = false;
                                    elem.future_sku = null;

                                });

                                self.$set(self, 'rows', response.data.data)
                                 this.isLoaded = true;
                                 this.$emit('stop-loading');
                             }
                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                            this.$emit('stop-loading');
                        }).finally(() => {
                             this.$emit('stop-loading');
                        });
                    },
                    onSearchProducts(phrase = null, loading = null) {
                        if (loading !== null) loading(true);
                        this.getSearchProductsList(phrase, this, loading);
                    },
                    dropdownSelect(item){
                        this.search_result = item;
                    },
                    searchPickedDelete(){
                        this.search_result = "";
                        this.search_products_list = [];
                    },
                    updateParams(newProps) {
                        this.serverParams = Object.assign({}, this.serverParams, newProps);
                    },
                    onPageChange(params) {
                        this.updateParams({page: params.currentPage});
                        history.pushState(null, null, '/admin/products/?page=' + params.currentPage);
                        this.loadSuppliersProducts();
                    },
                    onPerPageChange(params) {
                        this.updateParams({perPage: params.currentPerPage});
                        this.loadSuppliersProducts();
                    },
                    onColumnFilter(params) {
                        this.updateParams(params);
                        this.loadSuppliersProducts();
                    },
                    expandFilter(attributes = {}){

                        this.updateParams(attributes);

                        this.loadSuppliersProducts();
                    },
                    clearFilterParam(param){
                        if(Object(this.serverParams).hasOwnProperty(param)){
                            delete this.serverParams[param];
                        }
                    },
                    debounceFunction: _.debounce(function (userFunction) {
                        userFunction();
                    }, 1500),
                    changeSort(field){
                        let server_param =  this.serverParams.sort.find(item =>item.field === field);
                            if (server_param === undefined) {
                            this.serverParams.sort.push({
                                                          field: field,
                                                          type:"asc"
                                                       });
                            }else if (server_param !== undefined){
                                if (server_param.type === 'asc') {
                                    server_param.type = 'desc'
                                }
                                else if (server_param.type === 'desc') {
                                    let index = this.serverParams.sort.findIndex(item => item.field === field);
                                    this.serverParams.sort.splice(index, 1);
                                }
                            }

                        this.loadSuppliersProducts();
                    },
                    chack_field(field){
                        let field_data = this.serverParams.sort.find(item => item.field === field);
                        if (field_data === undefined) {
                            return false;
                        }else if (field_data.type === 'desc') {
                            return true
                        }
                    }
                },
        }
</script>

<style scoped>

</style>
