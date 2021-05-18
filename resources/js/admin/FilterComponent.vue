<template>
    <div class="filterAdvanced" :class="{'filterAdvanced--withTopName': settings.offset_top}">

        <a v-if="!selectedParams.length" href="javascript:void(0)" @click.stop="addParam"
           class="filterAdvanced__trigger">
            <icon class="icon mr--8" icon="filter"></icon>
            <span>{{$root.translateWords('Add filter parameter')}}</span>
        </a>
        <a v-else href="javascript:void(0)" @click.stop="removeParams(false)" class="filterAdvanced__trigger">
            <icon class="icon mr--8" icon="filter"></icon>
            <span>{{$root.translateWords('Clear filter parameter')}}</span>
        </a>

        <div class="filterAdvanced__options" v-if="selectedParams.length">
            <div class="grid grid--2 grid--gap_40">
                <div>
                    <div class="singleFormGroup mb--50">
                        <div class="singleFormGroup__title">
                            {{$root.translateWords('Filter criteria')}}:
                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="singleFormGroup mb--50">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.value}}:
                            <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid--2 grid--gap_40" v-for="(selectedParam, selectedParamIndex) in selectedParams">
                <div>
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center mb--10">
                                <v-select
                                        @mouseover.native="scrollFix(true)"
                                        @mouseleave.native="scrollFix(false)"
                                        :clear-search-on-select="true"
                                        :close-on-select="false"
                                        taggable
                                        :clearable="false"
                                        :searchable="false"
                                        :options="unusedOptions"
                                        :value="selectedParam.option"
                                        @input="option => selectParamFromList(option, selectedParamIndex)"
                                        class="input input--inForm"
                                        ref="filter_select"
                                        label="name"
                                        id="options-select-filter">
                                    <template v-slot:option="option">

                                        <template v-if="option.value === 'attributes'">
                                            <div class="flex flex--justify-space-between w100perc">
                                                <span>{{option.name}}</span>
                                                <template v-if="isShowingAttributesInList">
                                                    <icon class="icon filterAdvanced__arrow filterAdvanced__arrow--opened"
                                                          icon="next"></icon>
                                                </template>
                                                <template v-else>
                                                    <icon class="icon filterAdvanced__arrow" icon="next"></icon>
                                                </template>
                                            </div>
                                        </template>
                                        <template v-else-if="option.is_attribute">
                                            <div class="ml--10">{{option.only_name}}</div>
                                        </template>
                                        <template v-else>
                                            {{option.name}}
                                        </template>

                                    </template>

                                    <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                </v-select>
                                <a v-tooltip.top-start="'You have new messages.'"
                                   class="singleFormGroup__action" href="javascript:void(0)"
                                   @click.stop="removeParams(selectedParamIndex)">
                                    <icon icon="delete" class="icon"></icon>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="selectedParam.option !== null">
                    <div class="singleFormGroup">
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <template v-if="selectedParam.option.value === 'categories'">
                                    <v-select
                                            @mouseover.native="scrollFix(true)"
                                            @mouseleave.native="scrollFix(false)"
                                            @search="onSearchCategories"
                                            :components="{Deselect, OpenIndicator}"
                                            :multiple="true"
                                            :clearable="true"
                                            :searchable="true"
                                            :options="lists.categories"
                                            v-model="selectedParam.values"
                                            :reduce="category => category.id"
                                            class="input input--inForm vs--multiply"
                                            label="name">
                                        <template #search="{attributes, events}">
                                            <input
                                                    class="vs__search"
                                                    :placeholder="$root.translateWords('Add category')"
                                                    v-bind="attributes"
                                                    v-on="events"
                                            />
                                        </template>
                                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                    </v-select>
                                </template>
                                <template v-else-if="selectedParam.option.value === 'export_lists'">
                                    <v-select
                                            :components="{Deselect, OpenIndicator}"
                                            :multiple="true"
                                            :clearable="true"
                                            :searchable="false"
                                            :options="lists.export_lists"
                                            v-model="selectedParam.values"
                                            :reduce="export_list => export_list.id"
                                            class="input input--inForm vs--multiply"
                                            label="name">
                                        <template #search="{attributes, events}">
                                            <input
                                                    class="vs__search"
                                                    placeholder="Название списка"
                                                    v-bind="attributes"
                                                    v-on="events"
                                            />
                                        </template>
                                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                    </v-select>
                                </template>
                                <template v-else-if="selectedParam.option.value === 'suppliers_categories'">
                                    <v-select
                                            @search="onSearchCategories"
                                            :components="{Deselect, OpenIndicator}"
                                            :multiple="true"
                                            :clearable="true"
                                            :searchable="true"
                                            :options="lists.suppliers_categories"
                                            v-model="selectedParam.values"
                                            :reduce="category => category.id"
                                            class="input input--inForm vs--multiply"
                                            label="name">
                                        <template #search="{attributes, events}">
                                            <input
                                                    class="vs__search"
                                                    :placeholder="$root.translateWords('Add category')"
                                                    v-bind="attributes"
                                                    v-on="events"
                                            />
                                        </template>
                                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                    </v-select>
                                </template>
                                <template v-else-if="selectedParam.option.value === 'supplier_type'">
                                    <v-select
                                            :components="{Deselect, OpenIndicator}"
                                            :multiple="false"
                                            :clearable="true"
                                            :searchable="false"
                                            :options="lists.supplier_types"
                                            v-model="selectedParam.values"
                                            :reduce="type => type.value"
                                            class="input input--inForm"
                                            label="name">
                                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                    </v-select>
                                </template>
                                <template v-else-if="selectedParam.option.value === 'suppliers'">
                                    <v-select
                                            :components="{Deselect, OpenIndicator}"
                                            :no-drop="true"
                                            taggable multiple push-tags
                                            :multiple="true"
                                            :clearable="true"
                                            :searchable="true"
                                            v-model="selectedParam.values"
                                            class="input input--inForm vs--multiply">
                                    </v-select>
                                </template>
                                <template v-else-if="selectedParam.option.value === 'status'">
                                    <div class="switcherStatus">
                                        <div @click="selectedParam.values = false" class="switcherStatus__value"
                                             :class="{'switcherStatus__value--active switcherStatus__value--active_off': selectedParam.values === false}">
                                            {{$root.translate.columns.disabled_short}}
                                        </div>
                                        <div @click="selectedParam.values = true" class="switcherStatus__value"
                                             :class="{'switcherStatus__value--active': selectedParam.values === true}">
                                            {{$root.translate.columns.enabled_short}}
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="selectedParam.option.value === 'price'">
                                    <div class="singleFormGroup singleFormGroup--inlineSet">
                                        <div class="singleFormGroup__set">
                                            <div class="singleFormGroup__field">
                                                <div class="flex flex--align-center">
                                                    <input type="text" class="input input--price_inProduct"
                                                           v-model.number="selectedParam.values.from">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="singleFormGroup__set singleFormGroup__set--withDelim">
                                            <div class="singleFormGroup__field">
                                                <div class="flex flex--align-center">
                                                    <input type="text" class="input input--price_inProduct"
                                                           v-model.number="selectedParam.values.to">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="singleFormGroup__set singleFormGroup__set--delim">
                                            <div class="singleFormGroup__field h100perc">
                                                <div class="flex flex--align-center h100perc">
                                                    -
                                                </div>
                                            </div>
                                        </div>
                                        <div class="singleFormGroup__set">
                                            <div class="singleFormGroup__field">
                                                <div class="flex flex--align-center">
                                                    <v-select
                                                            :clearable="false"
                                                            :searchable="false"
                                                            :options="currencies"
                                                            v-model="selectedParam.values.currency_code"
                                                            :reduce="currency => currency.code"
                                                            class="input input--currency"
                                                            label="code">
                                                    </v-select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template v-else-if="selectedParam.option.value === 'quantity'">
                                    <div class="singleFormGroup singleFormGroup--inlineSet">
                                        <div class="singleFormGroup__set">
                                            <div class="singleFormGroup__field">
                                                <div class="flex flex--align-center">
                                                    <v-select
                                                            :clearable="false"
                                                            :searchable="false"
                                                            :options="lists.quantity_symbols"
                                                            v-model="selectedParam.values.code"
                                                            :reduce="symbol => symbol.code"
                                                            class="input input--currency"
                                                            label="name">
                                                    </v-select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="singleFormGroup__set singleFormGroup__set--withDelim">
                                            <div class="singleFormGroup__field">
                                                <div class="flex flex--align-center">
                                                    <input type="text" class="input input--price_inProduct"
                                                           :placeholder="$root.translate.columns.value"
                                                           v-model.number="selectedParam.values.from">
                                                </div>
                                            </div>
                                        </div>
                                        <template v-if="selectedParam.values.code === 'from_to'">
                                            <div class="singleFormGroup__set singleFormGroup__set--delim">
                                                <div class="singleFormGroup__field h100perc">
                                                    <div class="flex flex--align-center h100perc">
                                                        -
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="singleFormGroup__set">
                                                <div class="singleFormGroup__field">
                                                    <div class="flex flex--align-center">
                                                        <input type="text" class="input input--price_inProduct"
                                                               :placeholder="$root.translate.columns.value"
                                                               v-model.number="selectedParam.values.to">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template v-else-if="selectedParam.option.is_attribute === true">
                                    <v-select
                                            multiple
                                            :clearable="true"
                                            :searchable="true"
                                            :clear-search-on-select="true"
                                            v-model="selectedParam.values"
                                            :options="lists.attribute_values.filter(attribute_value => attribute_value.attribute_id === selectedParam.option.value && (selectedParam.values === null || selectedParam.values.indexOf(attribute_value) === -1))"
                                            class="input input--inForm vs--multiply pt--0"
                                            :components="{Deselect, OpenIndicator}"
                                            label="value">
                                        <template #search="{attributes, events}">
                                            <input
                                                    :placeholder="$root.translateWords('Add to list')"
                                                    class="vs__search"
                                                    v-bind="attributes"
                                                    v-on="events"
                                            />
                                        </template>
                                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                    </v-select>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex mt--48">
                <a class="btn btn--cancel modal__btn" href="javascript:void(0)"
                   @click="addParam">{{$root.translateWords('Add filter criteria')}}</a>
                <a class="btn btn--confirm modal__btn" href="javascript:void(0)"
                   @click.stop="filter">
                    {{$root.translate.columns.filter_action}}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FilterComponent",
        props: {
            'settings': {
                type: Object,
                required: false,
                default: function(){
                    return {};
                }
            },
            'currencies': {
                type: Array,
                required: false,
                default: function(){
                    return [];
                }
            },
            'current_options': {
                type: Array,
                required: true,
                default: function(){
                    return [];
                }
            }
        },
        data() {
            return {
                options: [
                    {
                        name: this.$root.translate.menu.catalog.items.categories,
                        value: 'categories',
                        is_attribute: false
                    },
                    {
                        name: 'Списки експорта',
                        value: 'export_lists',
                        is_attribute: false
                    },
                    {
                        name: this.$root.translate.columns.status,
                        value: 'status',
                        is_attribute: false
                    },
                    {
                        name: this.$root.translate.columns.price,
                        value: 'price',
                        is_attribute: false
                    },
                    {
                        name: this.$root.translate.columns.quantity,
                        value: 'quantity',
                        is_attribute: false
                    },
                    {
                        name: this.$root.translate.columns.attributes,
                        value: 'attributes',
                        is_attribute: false
                    },
                    {
                        name: this.$root.translate.columns.suppliers_categories,
                        value: 'suppliers_categories',
                        is_attribute: false
                    },
                    {
                        name: 'Cтатус',
                        value: 'supplier_type',
                        is_attribute: false
                    },
                    {
                        name: this.$root.translate.columns.suppliers,
                        value: 'suppliers',
                        is_attribute: false
                    },
                ],
                selectedParams: [],
                lists: {
                    categories: [],
                    export_lists: [],
                    suppliers_categories: [],
                    currencies: this.currencies,
                    quantity_symbols: [
                        {
                            code: '<',
                            name: '<'
                        },
                        {
                            code: '<=',
                            name: '<='
                        },
                        {
                            code: '>',
                            name: '>'
                        },
                        {
                            code: '>=',
                            name: '>='
                        },
                        {
                            code: '=',
                            name: '='
                        },
                        {
                            code: 'from_to',
                            name: this.$root.translate.columns.price_from + '-' + this.$root.translate.columns.price_to
                        },
                    ],
                    supplier_types: [
                        {
                            name: 'Не создан',
                            value: 0
                        },
                        {
                            name: 'Не подвязан',
                            value: 1
                        },
                        {
                            name: 'Создан',
                            value: 2
                        }
                    ],
                },
                Deselect: {
                    render: createElement => createElement('icon', {
                        class: 'icon',
                        props: {
                            icon: 'error'
                        }
                    }),
                },
                DeselectInProducts: {
                    render: createElement => createElement('icon', {
                        class: 'icon',
                        props: {
                            icon: 'delete'
                        }
                    }),
                },
                OpenIndicator: {
                    render: createElement => createElement('span', ''),
                },
                isShowingAttributesInList: false
            }
        },
        created() {
            this.onSearchCategories();
            this.onSearchSuppliersCategories();
            this.getAttributes();
            this.getAttributesValues();
            this.getExportLists();
        },
        computed: {
            unusedOptions() {
                return this.available_options.filter(option => {
                    return (!this.isShowingAttributesInList ? option.is_attribute === false : true) && this.selectedParams.map(selectedParam => selectedParam.option ? selectedParam.option.value : '').indexOf(option.value) === -1;
                })
            },
            available_options() {
                let self = this;

                return this.options.filter(option => {
                    return option.is_attribute || self.current_options.indexOf(option.value) !== -1
                });
            }
        },
        methods: {
            addParam() {
                this.selectedParams.push({
                    option: null,
                    values: null
                });
            },
            removeParams(param_value = false) {
                if (param_value !== false) {

                    try{

                        let code = this.selectedParams[param_value].option.value;

                        this.$emit('clear-filter-option', code);

                    }catch (e) {

                    }

                    this.selectedParams.splice(param_value, 1);

                    if (!this.selectedParams.length) this.$emit('filter');

                } else {

                    this.options.filter(option => !option.is_attribute).forEach(option => {
                        this.$emit('clear-filter-option', option.value);
                    });

                    this.$set(this, 'selectedParams', []);

                    this.$emit('filter');
                }
            },
            selectParamFromList(option, index) {
                //document.getElementById('options-select-filter').focus();
                //this.$refs.filter_select.focus();
                this.$nextTick(()=>{
                    this.$refs.filter_select.focus;
                });

                if (option.value === 'attributes') {
                    this.isShowingAttributesInList = !this.isShowingAttributesInList;
                } else {

                    this.$refs.filter_select[index].open = false;

                    let values;

                    switch (option.value) {
                        case 'status':
                            values = true;
                            break;
                        case 'price':
                            values = {
                                currency_code: this.$root.currency_code,
                                from: null,
                                to: null
                            };
                            break;
                        case 'quantity':
                            values = {
                                code: '>',
                                from: null,
                                to: null
                            };
                            break;
                        default:
                            values = null;
                            break;
                    }

                    this.$set(this.selectedParams, index, {
                        option: option,
                        values: values
                    });
                }
            },
            filter() {

                let params = {};

                let attributes = [];

                this.selectedParams.forEach(selectedParam => {

                    if (selectedParam.option.is_attribute) {
                        if (selectedParam.values.length) {
                            attributes.push({
                                id: selectedParam.option.value,
                                values: selectedParam.values.map(value => {
                                    return value.id;
                                })
                            });
                        }
                    } else {
                        params[selectedParam.option.value] = selectedParam.values;
                    }

                });

                if (attributes.length) {
                    params.attributes = attributes;
                }

                this.$emit('filter', params);
            },
            onSearchCategories(phrase = null, loading = null) {
                if (loading !== null) loading(true);
                this.getCategories(phrase, this, loading);
            },
            getCategories: _.debounce((phrase, self, loading) => {
                axios.get('/admin/categories', {
                    params: {
                        phrase: phrase,
                        autocomplete: true
                    }
                }).then(httpResponse => {
                    self.$set(self.lists, 'categories', httpResponse.data.categories);
                    if (loading !== null) loading(false);
                });
            }, 500),
            onSearchSuppliersCategories(phrase = null, loading = null) {
                if (loading !== null) loading(true);
                this.getSuppliersCategories(phrase, this, loading);
            },
            getSuppliersCategories: _.debounce((phrase, self, loading) => {
                axios.get('/admin/suppliers-categories', {
                    params: {
                        phrase: phrase,
                        autocomplete: true
                    }
                }).then(httpResponse => {
                    self.$set(self.lists, 'suppliers_categories', httpResponse.data);
                    if (loading !== null) loading(false);
                });
            }, 500),
            getAttributes() {
                let self = this;

                axios.get('/admin/attributes/', {
                    params: {
                        with_translate: true
                    }
                }).then(
                    httpResponse => {
                        httpResponse.data.attributes.forEach(attribute => {
                            self.options.push({
                                value: attribute.id,
                                name: self.$root.translate.columns.attribute + ': ' + attribute.name,
                                only_name: attribute.name,
                                is_attribute: true
                            });
                        });
                    });
            },
            getAttributesValues() {
                axios.get('/admin/attribute-values/', {
                    params: {
                        autocomplete: true,
                    }
                }).then(httpResponse => {
                    this.$set(this.lists, 'attribute_values', httpResponse.data.values);
                })
            },
            getExportLists(){
                axios.get('/admin/export-products-list').then(Response => {
                    this.$set(this.lists, 'export_lists', Response.data);
                })
            },
            scrollFix(on_element){
                var elem = document.querySelector('.mainContent');
                (on_element) ? elem.setAttribute('style', 'overflow:hidden !important;') :  elem.setAttribute('style', 'overflow:unset;');
            }
        }
    }
</script>

<style scoped>

</style>