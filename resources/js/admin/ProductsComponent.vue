<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.catalog.items.products}}</h2>

        <div class="filter">
            <div class="filter__name">
                <input @input="debounceFunction(onColumnFilter)" type="text" class="input"
                       v-model="serverParams.name_sku"
                       placeholder="Пошук товарів по назві, артикулу, коду">
                <icon icon="search" class="icon filter__name_icon"></icon>
            </div>
            <advanced-filter :current_options="['categories', 'export_lists', 'status', 'price', 'quantity', 'attributes']"
                             :settings="{offset_top: true}" :currencies="currencies" @filter="expandFilter"
                             v-on:clear-filter-option="clearFilterParam"></advanced-filter>
        </div>

        <template v-if="isLoaded">
            <div class="listData" v-if="rows.length || totalRecords">

                <div v-if="selecteds.length"
                     class="massActionsHeader massActionsHeader--products">
                    <div class="massActionsHeader__item">Выбрано {{countCheckedItems}} <span
                            class="massActionsHeader__vertical-line"></span></div>
                    <div class="massActionsHeader__item ">
                        <v-select
                                @click.native.stop="()=>{return false}"
                                :close-on-select="true"
                                @input="changeMassActionType"
                                v-model="mass_operations.header_dropdown_selected_value"
                                :clearable="true"
                                :searchable="true"
                                :options="products_component_options"
                                :slot-scope="products_component_options"
                                class="input massActionsHeader__input"
                                label="title">
                            <template slot="option" slot-scope="option">
                                <div
                                        :class="{'border-bottom': option.type === 'off'}">
                                    <icon v-if="option.icon" :icon="option.icon" class="icon"></icon>
                                    {{ option.title }}
                                </div>
                            </template>
                        </v-select>
                    </div>
                    <div class="massActionsHeader__link-end-line pr--25"><a @click="clearCheckedItems()"
                                                                            href="javascript:void(0)"
                                                                            class="text-link text-link--fz_inherit">
                        Отменить выделение</a></div>
                </div>


                <!-- <perfect-scrollbar> -->

                <vue-good-table
                        style="min-width: 1200px"
                        @on-page-change="onPageChange"
                        @on-sort-change="onSortChange"
                        @on-column-filter="onColumnFilter"
                        @on-per-page-change="onPerPageChange"
                        :pagination-options="{
                            enabled: false
                        }"
                        mode="remote"
                        :columns="columns"
                        :rows="rows"
                        :totalRows="totalRecords"
                        :isLoading.sync="isLoading"
                        styleClass="table table--labels"
                        :row-style-class="getValueRowClass"
                        :fixed-header="true">


                    <template slot="table-column" slot-scope="props">
                        <template v-if="props.column.label == 'check'">
                            <check :checked="selectedAll" @click.native="changeSelectAll"></check>
                        </template>
                        <template v-else-if="props.column.field == 'supplier'">
                            <div class="flex">
                                <span>{{props.column.label}}</span> 
                                <span class="flex flex--align-center product-colors product-colors--offset-left">
                                   (<span class="product-colors__name">Н</span>x
                                    <span class="product-colors__quantity">К</span>x
                                    <span class="product-colors__rrc">Р</span>x
                                    <span class="product-colors__entrance-price">В</span>)
                                </span>
                            </div>
                        </template>
                        <span v-else>
                            {{props.column.label}}
                        </span>
                    </template>
                    <template slot="table-row" slot-scope="props">
                        <template v-if="props.column.field === 'check' && rows[props.index].type !== 3">
                            <div v-if="rows[props.index].visual_labels"
                                 class="labels">
                                 <div v-for="label in rows[props.index].visual_labels">
                                    <div v-if="label === 'zamanyha'" class="labels__item labels__item--zamanuha">Замануха</div>
                                    <div v-if="label === 'status'" class="labels__item labels__item--disabled">Отключен</div>
                                    <div v-if="label === 'date_available'" class="labels__item labels__item--data">{{rows[props.index].date_available_day}}</div>
                                    <div v-if="label === 'quantity' && rows[props.index].quantity <= 0" class="labels__item labels__item--ended">Закончился</div>
                                 </div>
                            </div>
                            <check :checked="selecteds.indexOf(rows[props.index].id) !== -1"
                                   @click.native="changeSelect(rows[props.index].id)"></check>
                        </template>
                        <template v-else-if="props.column.field === 'id'">
                            <div class="flex flex--align-center">
                                <div class="copy">
                                    <input disabled type="text" class="input input--name"
                                           v-model="rows[props.index].id">
                                    <button class="copy__button copy__button--little"
                                            v-clipboard:copy="rows[props.index].id">
                                        <icon icon="copy-item" class="icon copy__icon"></icon>
                                    </button>
                                    <input ref="copyInput" type="text" class="input input--invisible"
                                           v-model="rows[props.index].id">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'image'">

                            <upload-thumb
                                    :is_tmp="typeof rows[props.index].id !== 'number'"
                                    items_type="products"
                                    :item="$root.encodeId('products', rows[props.index].type === 2 ? rows[props.index].main_id : rows[props.index].id)"
                                    :data="rows[props.index]"
                                    :file_path="rows[props.index].image"
                                    :thumb_path="rows[props.index].filemanager_thumb"
                                    img-style="max-width: 57px; border-radius: 5px;"
                                    @remove="rows[props.index].image = ''"
                            ></upload-thumb>

                        </template>
                        <template v-else-if="props.column.field === 'name'">
                            <div v-for="translate in rows[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    {{$root.languages.find((language) => {return language.locale ===
                                    translate.locale}).name}}:
                                    <input type="text" class="input input--label_left"
                                           v-model="translate.name">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'supplier'">
                            <div v-for="supplier in rows[props.index].suppliers">
                                <div class="flex flex--align-center product-colors">
                                    <span class="product-colors__name">{{supplier.supplier_code}}</span>x
                                    <span class="product-colors__quantity">{{supplier.quantity}}</span>x
                                    <span class="product-colors__rrc">{{supplier.rrc_price}}</span>x
                                    <span class="product-colors__entrance-price">{{supplier.price}}</span>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field == 'vendor_price'">
                            <template v-if="typeof rows[props.index].id === 'number'">
                                <div class="flex flex--align-center">
                                    <input type="number" class="input input--price input--label_right"
                                           disabled
                                           v-model.number="rows[props.index].vendor_price">
                                    {{rows[props.index].currency_code}}
                                </div>
                            </template>
                            <template v-else-if="props.column.field == 'vendor_price'">
                                <div class="flex flex--align-center">
                                    <input type="number" class="input input--price input--label_right"
                                           v-model.number="rows[props.index].warehouse_price">
                                    {{rows[props.index].currency_code}}
                                </div>
                            </template>
                            <!-- <template v-else-if="props.column.field == 'quantity'">

                                <div class="flex flex--align-center">
                                    <input type="number" class="input input--price input--label_right"
                                           v-model.number="rows[props.index].warehouse_price">
                                    {{rows[props.index].currency_code}}
                                </div>
                            </template> -->
                        </template>
                        <template v-else-if="props.column.field == 'quantity'">
                            <div class="flex flex--align-center">
                                <input v-if="typeof rows[props.index].id === 'number'" type="number"
                                       class="input input--qty" disabled
                                       v-model.number="rows[props.index].quantity">
                                <input v-else type="number" class="input input--qty"
                                       v-model.number="rows[props.index].warehouse_quantity">
                            </div>
                        </template>
                        <template v-else-if="props.column.field == 'export_lists'">
                            <ul style="list-style: circle">
                                <li v-for="list in rows[props.index].export_lists">
                                    {{list.name}}
                                </li>
                            </ul>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="rows[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch"
                                                   spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-if="isChangedRow(rows[props.index].id)" class="table__action"
                                   href="javascript:void(0)" @click.stop="storeProduct(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
                                <template v-if="typeof rows[props.index].id === 'number'">
                                    <a class="table__action" :href="rows[props.index].href" target="_blank">
                                        <icon icon="foreign" class="icon"></icon>
                                    </a>
                                    <a class="table__action"
                                       href="javascript:void(0)" @click.stop="openEditProduct(rows[props.index])">
                                        <icon icon="pencil-edit-button" class="icon"></icon>
                                    </a>
                                </template>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="removeProduct(props.index)">
                                    <icon icon="delete" class="icon"></icon>
                                </a>
                            </template>
                        </template>
                        <span v-else>
                        {{props.formattedRow[props.column.field]}}
                    </span>
                    </template>

                    <template slot="pagination-bottom" slot-scope="props">

                    </template>

                </vue-good-table>

                <!-- </perfect-scrollbar> -->

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
            <div v-else class="listEmpty">
                <div class="listEmpty__heading">{{$root.translateWords('Your products list is empty')}} :(</div>
                <div class="listEmpty__text">
                    {{$root.translateWords('You may add them')}}
                    <a class="listEmpty__link" href="javascript:void(0)" @click.stop="addProduct">{{$root.translateWords('manually')}}</a>
                    {{$root.translate.columns.or}}
                    <router-link class="listEmpty__link" :to="{name: 'excel'}">{{$root.translateWords('use import')}}
                    </router-link>
                </div>
            </div>
        </template>

        <mass-edit @refresh="loadItems" :products="selecteds" ref="massEdit"></mass-edit>


        <widget-actions v-if="selecteds.length || !modalConfigurationProductsComponent.open" add="addProduct"
                        remove="removeProducts" copy="copyProducts"
                        :trans="{add: $root.translateWords('Create product')}"></widget-actions>
        <widget-actions v-else add="addProduct" :trans="{add: $root.translateWords('Create product')}"></widget-actions>

        <modal name="modal_add_to_export_list" width="80%"
               v-if="Object.keys(modalConfigurationProductsComponent).length"
               :title="'Добавить товары в список експорта'"
               @closed="closeModalConfiguration">
            <template v-slot>


                <!--
                    v-if="to_attribute.attribute !== null && to_attribute.attribute.id"

                    attribute_values.filter(attribute_value => attribute_value.attribute_id === to_attribute.attribute.id && to_attribute.values.indexOf(attribute_value) === -1)

                    @input="clearAttributeValueSearchPhrase(to_attribute.id)"
                    v-for="(item) in products_list" :key="item.id"
                    -->
                <div class="singleFormGroup">
                    <div class="singleFormGroup__field">
                        <p class=" singleFormGroup__field__title color--fff mb--30">Виберите списки:</p>
                        <div class="flex flex--align-center">
                            <template>
                                <v-select
                                        multiple
                                        :clearable="true"
                                        :searchable="true"
                                        :clear-search-on-select="true"
                                        :options="mass_operations.products_list.filter(item => mass_operations.add_export_list_selected_goods_lists.indexOf(item)===-1)"
                                        v-model="mass_operations.add_export_list_selected_goods_lists"
                                        @input="mass_operations.add_export_list_input_value = ''"
                                        class="input input--inForm vs--multiply pt--0"
                                        :components="{Deselect, OpenIndicator}"
                                        :ref="'modal_input'"
                                        label="name">
                                    <template #search="{attributes, events}">
                                        <input
                                                :placeholder="$root.translateWords('Create value')"
                                                @input="setProductValueSearchPhrase()"
                                                v-model="mass_operations.add_export_list_input_value"
                                                class="vs__search"
                                                v-bind="attributes"
                                                v-on="events">
                                    </template>
                                    <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                </v-select>
                                <template v-if="mass_operations.refreshing">
                                    <font-awesome-icon class="text-warning color--fff-important" icon="circle-notch"
                                                       spin></font-awesome-icon>
                                </template>
                                <template v-else>
                                    <a v-if="mass_operations.add_export_list_input_value.length && !checkValueExist()"
                                       v-tooltip.top-start="'You have new messages.'"
                                       class="singleFormGroup__action" href="javascript:void(0)"
                                       @click.stop="createProductsListValue()">
                                        <icon icon="floppy-disk" class="icon"></icon>
                                        <!--
                                        v-if="to_attribute.values.length || (Object(to_attribute).hasOwnProperty('attributeValueSearchPhrase') && to_attribute.attributeValueSearchPhrase.length)"
                                        -->
                                    </a>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex flex--justify-end mt--48">
                    <a href="javascript:void(0)" class="btn btn--confirm"
                       @click.stop="addSelectedGoods">
                        Сохранить изменения
                    </a>
                </div>


            </template>
        </modal>
    </div>
</template>

<script>
    //$root.translateWords(modalConfigurationProductsComponent.edit ? 'Editing configuration': 'Creating configuration')        
    let UploadThumb = require('./UploadThumbComponent').default;

    import {PerfectScrollbar} from 'vue2-perfect-scrollbar'
    // Vue.directive('scroll', {
    //   inserted: function (el, binding) {
    //     let f = function (evt) {
    //       if (binding.value(evt, el)) {
    //         window.removeEventListener('scroll', f)
    //       }
    //     }
    //     window.addEventListener('scroll', f)
    //   }
    // })
    //Vue.directive('clipscroll', {
    //  inserted: function(el, binding) {
    //    let f = function(evt) {
    //     // var hasRun = false;
    //      if (window.scrollY > binding.value.end) {
    //        window.removeEventListener('scroll', f);
    //      }
    //    }
    //    window.addEventListener('scroll', f);
    //  }
    //});
    export default {
        name: "ProductsComponent",
        components: {
            UploadThumb,
            'mass-edit': require('./ProductMassEditModal').default,
            'pagination': require('./paginationComponent').default,
            'advanced-filter': require('./FilterComponent').default,
            PerfectScrollbar
        },
        data() {
            return {
                scrollOffset: 0,
                totalRecords: 0,
                serverParams: {
                    columnFilters: {},
                    sort_column: null,
                    sort_direction: null,
                    page: 1,
                    perPage: 100,
                    fromRecords: null,
                    toRecords: null
                },
                isLoaded: false,
                products_component_options: [{
                    title: "Удалить",
                    icon: "delete-light",
                    type: "delete"
                },
                    {
                        title: "Дублировать",
                        icon: "copy-item",
                        type: "copy"
                    },
                    // {
                    //     title: "Выключить",
                    //     icon: "turn-off",
                    //     type: "off"
                    // },
                    // {
                    //     title: "Редактировать цену",
                    //     type: "price"
                    // },
                    // {
                    //     title: "Редактировать количество",
                    //     type: "quantity"
                    // },
                    // {
                    //     title: "Редактировать характеристики",
                    //     type: "characteristic"
                    // },
                    // {
                    //     title: "Редактировать категорий",
                    //     type: "category"
                    // },
                    // {
                    //     title: "Редактировать статус",
                    //     type: "status"
                    // },
                    {
                        title: "Добавить в список експорта",
                        type: "add_export"
                    }
                ],
                columns: [
                    {
                        label: 'check',
                        field: 'check',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: 'Код товара',
                        field: 'id',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.image_short,
                        field: 'image',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.name,
                        field: 'name',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.suppliers,
                        field: 'supplier',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.price,
                        field: 'vendor_price',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.quantity,
                        field: 'quantity',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: 'Списки експорта',
                        field: 'export_lists',
                        thClass: 'table__heading',
                        tdClass: 'table__value'
                    },
                    {
                        label: this.$root.translate.columns.actions,
                        field: 'actions',
                        thClass: 'table__heading table__heading--text_right',
                        tdClass: 'table__value table__value--text_right',
                        sortable: false,
                    },
                ],
                modalConfigurationProductsComponent: {
                    edit: false,
                    open: false
                },
                rows: [],
                isLoading: false,
                currencies: [],
                selecteds: [],
                refreshing: false,
                selectedAll: false,
                savedOriginal: [],
                modal_add_to_export_list: [],
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

                mass_operations: {
                    products_list: [],
                    add_export_list_selected_goods_lists: [],
                    add_export_list_input_value: "",
                    refreshing: false,
                    header_dropdown_selected_value: {}

                },
                scrolls_info: ""

            }
        },
        computed: {
            isProductsChanget(){
                if (this.$route.params !== undefined && this.$route.params) {
                    return this.$route.params;
                }
            },
            countCheckedItems() {
                if (this.selecteds.length) {
                    return this.selecteds.length
                }
            },

            isSelected() {
                return (product_id) => {
                    return this.selecteds.indexOf(product_id) !== -1;
                }
            },
            // editedProduct(){
            //     if (this.$route.params.product !== undefined) {
            //         return this.rows.indexOf(this.$route.params.product.id) !== -1;
            //     }
            // },
            isChangedRow() {
                return (product_id) => {
                    let originalPosition = this.savedOriginal.findIndex(product => {
                        return product.id === product_id
                    });

                    if (originalPosition === -1) {
                        return true;
                    }

                    let currentPosition = this.rows.findIndex(product => {
                        return product.id === product_id
                    });

                    let currentProduct = this.$root.copy(this.rows[currentPosition]);

                    delete currentProduct.refreshing;

                    let savedProduct = this.$root.copy(this.savedOriginal[originalPosition]);

                    delete savedProduct.refreshing;

                    return JSON.stringify(currentProduct) !== JSON.stringify(savedProduct);
                }
            },
            scrollChange() {
                return this.$root.scroll_info;
            },
            selectedCheckBox() {
                return this.selecteds;
            }
        },
        created() {
            axios.get('/admin/currencies/').then(
                httpResponse => {
                    this.currencies = httpResponse.data.currencies;
                });

            let url = new URL(window.location.href);
            let page = parseInt(url.searchParams.get("page"));

            if (!page) page = 1;

            this.serverParams.page = page;

            history.pushState(null, null, '/admin/products/?page=' + page);
    
            this.loadItems();
        },
        methods: {
            addProduct() {
                let product = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    image: '',
                    vendor_price: 0,
                    quantity: 0,
                    sort_order: 0,
                    translates: [],
                    refreshing: false,
                    href: ''
                };

                for (let language of this.$root.languages) {
                    product.translates.push({
                        name: null,
                        locale: language.locale
                    });
                }

                this.$root.scrollToNewRow(this.rows, product);
            },
            storeProduct(index) {

                let product = this.rows[index];

                let product_id = product.id;

                let originalPosition = this.savedOriginal.findIndex(product => {
                    return product.id === product_id
                });

                let request;

                product.refreshing = true;

                if (typeof product.id === 'number') {

                    request = axios.put('/admin/products/' + product.id + '?fast=1', product);

                } else {
                    request = axios.post('/admin/products', product);
                }

                request.then(httpResponse => {

                    if (typeof product.id !== 'number') {

                        product.id = httpResponse.data.id;

                        product.sku = httpResponse.data.sku;

                        product.href = httpResponse.data.href;

                        product.vendor_price = httpResponse.data.vendor_price;

                        product.quantity = httpResponse.data.quantity;

                    }

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(product));
                    } else {
                        this.savedOriginal.push(this.$root.copy(product));
                    }

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    product.refreshing = false;
                });

            },
            removeProduct(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let product = this.rows[index];

                    let product_id = product.id;

                    let originalPosition = this.savedOriginal.findIndex(product => {
                        return product.id === product_id
                    });

                    product.refreshing = true;

                    let idForRemove = product.type === 2 ? product.main_id : product.id;

                    if (typeof idForRemove === 'number') {

                        axios.delete('/admin/products/' + idForRemove).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.rows.splice(index, 1);

                            this.savedOriginal.splice(originalPosition, 1);

                        });

                    } else {
                        this.rows.splice(index, 1);
                    }
                });
            },
            removeProducts() {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let self = this;

                    this.refreshing = true;

                    axios.delete('/admin/products', {params: {selecteds: this.selecteds}}).then(httpResponse => {

                        this.$root.notify(httpResponse.data);

                        if (this.selecteds.length < this.rows.length) {
                            this.selecteds.forEach(product_id => {

                                self.rows.splice(self.rows.findIndex(item => {
                                    return item.id === product_id;
                                }), 1);

                                self.savedOriginal.splice(this.savedOriginal.findIndex(product => {
                                    return product.id === product_id
                                }), 1);

                            });

                            this.refreshing = false;

                            this.$set(this, 'selecteds', []);

                            this.$set(this, 'selectedAll', false);

                        } else {

                            this.loadItems().then(function () {

                                self.refreshing = false;

                                self.$set(self, 'selecteds', []);

                                self.$set(self, 'selectedAll', false);

                            });
                        }
                    });
                });
            },
            copyProducts() {

                let self = this;

                this.refreshing = true;

                axios.post('/admin/products/copy', {selecteds: this.selecteds}).then(httpResponse => {

                    this.$root.notify(httpResponse.data);

                    this.loadItems().then(function () {
                        self.refreshing = false;
                    });
                });
            },
            async loadItems(params = null) {

                if (params !== null) {
                    this.serverParams.sort_column = params[0].field;
                    this.serverParams.sort_direction = params[0].type;
                }

                this.$emit('start-loading');

                return axios.get('/admin/products', {
                    params: this.serverParams
                }).then(response => {

                    this.totalRecords = response.data.products.total;

                    if (response.data.products.data.length === 0 && this.totalRecords > 0) {
                        this.onPageChange({currentPage: 1});
                    } else {
                        this.serverParams.fromRecords = response.data.products.from;
                        this.serverParams.toRecords = response.data.products.to;

                        if (response.data.products.data.length) {
                            response.data.products.data.forEach(product => {
                                product.refreshing = false;
                            });
                        }

                        this.$set(this, 'rows', response.data.products.data);
                        this.$set(this, 'savedOriginal', this.$root.copy(response.data.products.data));
                        this.isLoaded = true;

                        this.$emit('stop-loading');
                    }
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                });
            },
            changeSelectAll() {

                this.selectedAll = !this.selectedAll;

                this.$set(this, 'selecteds', []);

                if (this.selectedAll) {

                    for (let i in this.rows) {
                        this.selecteds.push(this.rows[i].id);
                    }
                }
            },
            changeSelect(product_id) {
                let pos = this.selecteds.indexOf(product_id);

                if (pos === -1) {
                    this.selecteds.push(product_id);
                } else {
                    this.selecteds.splice(pos, 1);
                }
            },
            getValueRowClass(row) {
                let rowClass = 'table__row';

                if (this.isSelected(row.id)) rowClass += ' table__row--selected';

                if (!!this.isProductsChanget && this.$route.params.product !== undefined && this.$route.params.product.id === row.id) rowClass += ' table__row--was-changet';

                return rowClass;
            },
            openMassEdit() {
                this.$refs.massEdit.open();
            },
            updateParams(newProps, is_filter = false) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },

            onPageChange(params) {
                this.updateParams({page: params.currentPage});

                history.pushState(null, null, '/admin/products/?page=' + params.currentPage);

                this.loadItems();
            },

            onPerPageChange(params) {
                this.updateParams({perPage: params.currentPerPage});
                this.loadItems();
            },

            onSortChange(params) {
                this.serverParams.sort_direction = params[0].type;
                this.serverParams.sort_column = params[0].field;

                this.loadItems();
            },

            onColumnFilter(params) {
                this.updateParams(params);
                this.loadItems();
            },
            debounceFunction: _.debounce(function (userFunction) {
                userFunction();
            }, 1500),
            paramText(variant) {

                let names = [];

                variant.variant_attribute_values.forEach(function (value) {
                    names.push(value.value);
                });

                return names.join(' & ');
            },
            expandFilter(attributes = {}) {
                this.$set(this.serverParams, 'columnFilters', attributes);
                this.loadItems();
            },
            clearFilterParam(param) {
                if (Object(this.serverParams).hasOwnProperty(param)) {
                    delete this.serverParams[param];
                }
            },
            clearCheckedItems() {
                this.selectedAll = false;
                this.selecteds = [];
            },
            modalOpenProductsComponent(event) {
                this.modalConfigurationProductsComponent.open = true;
                this.modalConfigurationProductsComponent.edit = true;
                this.$root.changePopupShowStatus('modal_add_to_export_list', true);
            },
            getGoodsList() {
                let self = this;
                console.log("start");

                axios.get('/admin/export-products-list').then(Response => {
                    self.$set(self.mass_operations, 'products_list', Response.data);
                })
            },
            createProductsListValue() {
                let self = this;
                let empty_element = {
                    name: this.mass_operations.add_export_list_input_value,
                    id: "no id",
                    refreshing: false,
                    products: []
                };
                this.mass_operations.refreshing = true;
                axios.post('/admin/export-products-list', empty_element).then(Response => {
                    empty_element.id = Response.data.id;
                    self.mass_operations.add_export_list_selected_goods_lists.push(empty_element);
                    self.mass_operations.products_list.push(empty_element);
                    self.mass_operations.add_export_list_input_value = "";
                    self.$refs.modal_input.search = "";
                    this.$root.notify(Response.data);
                    self.mass_operations.refreshing = false;
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    self.mass_operations.refreshing = false;
                })
            },

            addSelectedGoods() {
                let self = this;
                // this.mass_operations.add_export_list_selected_goods_lists.forEach(item => {
                //     self.$set(self.mass_operations.item, 'products', self.selecteds);
                // axios.put('/admin/export-products-list', item)

                let save_item_id = this.mass_operations.add_export_list_selected_goods_lists.map(item => item.id);
                axios.put('/admin/export-products-list/products', {
                        export_products_lists: save_item_id,
                        products: self.selecteds
                    }
                ).then(Response => {
                    this.$root.notify(Response.data);
                    self.$set(self.mass_operations, 'add_export_list_selected_goods_lists', []);
                    self.closeModalConfiguration();
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                });

                // axios.put('/admin/export-products-list', (self.$root.copy(self.mass_operations.add_export_list_selected_goods_lists))).then(Response=>{
                //     this.$root.notify(Response.data);
                //     self.$set(self.mass_operations, 'add_export_list_selected_goods_lists', []);
                //     self.closeModalConfiguration();
                // }).catch(error=> {
                //     if (error.response) this.$root.notify(error.response.data);
                // });
                //self.$set(self.mass_operations, 'add_export_list_selected_goods_lists', []);
                //self.closeModalConfiguration();


                //(self.$root.copy(item)
                // })

                //console.log(save_item_id, this.selecteds);

                //axios.post('/admin/', save_item_id, this.selecteds).then(R=>{
                //self.closeModalConfiguration();
                //self.$set(self.mass_operations, 'add_export_list_selected_goods_lists', []);
                //});


            },
            setProductValueSearchPhrase() {
                this.$set(this.mass_operations, 'add_export_list_input_value', event.target.value);
            },
            checkValueExist() {
                let self = this;
                if (this.mass_operations.products_list.filter(item => item.name === self.mass_operations.add_export_list_input_value).length) {
                    return true
                }
                return false
            },
            closeModalConfiguration() {


                this.$root.changePopupShowStatus('modal_add_to_export_list', false);
                this.modalConfigurationProductsComponent.open = false;


                // this.$set(this.modalConfiguration, 'data', {
                //     name: '',
                //     refreshing: false,
                //     settings: customSettings
                // });

            },
            changeMassActionType(elem) {
                let self = this;
                switch (elem.type) {
                    case "delete":
                        self.removeProducts();
                        self.$set(self.mass_operations, "header_dropdown_selected_value", {})
                        break;
                    case "copy" :

                        self.copyProducts();
                        self.$set(self.mass_operations, "header_dropdown_selected_value", {})

                        break
                    case "add_export":
                        setTimeout(function () {
                            self.getGoodsList();
                            self.modalOpenProductsComponent();
                            self.$set(self.mass_operations, "header_dropdown_selected_value", {});
                        }, 1000)


                        break;

                    default:
                        break;
                }
            },
            scrollToElem(scrolls){
                let table_offset_top
                let table_positions 
                if ($(".vgt-inner-wrap").offset() !== undefined) {
                    table_offset_top = $(".vgt-inner-wrap").offset().top;
                    table_positions = $(".vgt-inner-wrap").position().top;  
                }
                                              
               if (table_offset_top <= -30) {
                    $(".vgt-fixed-header").css( "display","flex");
                    if (this.selecteds.length) {
                        $(".massActionsHeader--products").addClass("massActionsHeader--fixed");
                    }
                } else {
                    $(".massActionsHeader--fixed").css("transform", "translateY(0px)");
                    $(".vgt-fixed-header").css("display", "none");
                    $(".massActionsHeader--products").removeClass("massActionsHeader--fixed");
                    $(".massActionsHeader").removeAttr("style")


                }
                let fixed_translate;

                if (!this.selecteds.length) {
                    fixed_translate = -278;
                } else {
                    fixed_translate = -242;
                }
                if (table_positions >= 250) {
                    fixed_translate -= (table_positions - 165);
                    let fixed_header = -220 - (table_positions - 164);
                    $(".massActionsHeader--fixed").css("transform", "translateY(" + fixed_header + "px)");
                    table_positions = $(".vgt-inner-wrap").position().top;
                    fixed_header = -220 - (table_positions - 166);
                    $(".massActionsHeader--fixed").css("transform", "translateY(" + fixed_header + "px)");
                }
                $(".vgt-fixed-header").css("transform", "translateY(" + fixed_translate + "px)");


                //    if (!this.selecteds.length) {
                //         $(".vgt-fixed-header").css( "transform","translateY(-50px)");
                //    }else{
                //         $(".vgt-fixed-header").css( "transform","translateY(-20px)");
                //    }
            },
            openEditProduct(item){
                this.scrollOffset = document.getElementById('perfect-scroll-offset').scrollTop;
                this.$router.push({ name: 'product', params: {id: item.type === 2 ? item.main_id : item.id} });
            }
        },
        watch: {
            scrollChange: function (value) {
                this.scrollToElem(value)
            },
            selectedCheckBox: function (count) {
                let value = this.$root.scroll_info;
                if ($(".vgt-inner-wrap").offset() !== undefined) {
                    if ($(".vgt-inner-wrap").offset().top <= -30) {
                        setTimeout(() => {
                            $(".massActionsHeader--products").addClass("massActionsHeader--fixed");
                            this.scrollToElem(value);
                        }, 1);
                        this.scrollToElem(value);
                    }
                }
            },
            isProductsChanget: function (value){
                let self = this;
                if (value !== undefined && value.product !== undefined) {
                    let index = this.rows.findIndex(product => {
                        return product.id === value.product.id;
                    });
                    let originalPosition = this.savedOriginal.findIndex(product => {
                        return product.id === value.product.id;
                    });
                    if (value.product.type === "delete") {
                        this.rows.splice(index, 1);
                        this.savedOriginal.splice(originalPosition, 1);
                    }else if (value.product.type === "save") {
                        this.$set(this.rows[index], "translates" , value.product.name);
                        this.$set(this.rows[index], "vendor_price" , value.product.price);
                        this.$set(this.rows[index], "quantity" , value.product.quantity);
                        this.rows[index].filemanager_thumb = value.product.img_thumb;
                        this.rows[index].image = value.product.img;
                        this.$set(this.rows[index], "visual_labels" , value.product.visual_labels);
                        this.$set(this.rows[index], "date_available_day" , value.product.date_available_day);

                        
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(this.rows[index]));
                    }

                }
                if (document.getElementById('perfect-scroll-offset') && value.scrollActivate === true) {
                    let offset = this.scrollOffset;
                    setTimeout(() => {
                        document.getElementById('perfect-scroll-offset').scrollTop = offset;
                        value.scrollActivate = false;
                    }, 100);
                }
            },
        }
    }
</script>

<style scoped>

</style>