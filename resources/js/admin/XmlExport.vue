<template>
    <div>
        <div class="flex flex--justify-space-between">
                <div>
                    <h2 class="mainContent__heading">
                        Экспорт товаров
                    </h2>
                </div>
            </div>
        <div class="singleForm singleForm--inModulesTpl">
            <div class="XmlExport">
                <menu class="menu menu--inModulesTpl">
                    <ul class="menu__list">
                        <li class="menu__heading mb--16" v-for="item in export_menu_elements" :key="item.name"
                            :class="{'menu__heading--active': item.name ===show_menu_content}"
                            @click="showMenuElement(item.name)">
                            <span>
                              {{item.name}}
                            </span>
                        </li>
                    </ul>
                </menu>
                <div v-if="show_menu_content === $root.translateWords('Products lists')"
                     class="XmlExport__goods-list">
                    <table class="table">
                        <thead>
                        <tr class="table__row">
                            <td class="table__heading table__heading--inConfiguration_start">
                                <div class="flex flex--align-center">
                                    <span>{{$root.translateWords('List name')}}</span>
                                    <icon icon="icon" class="icon helper-icon"></icon>
                                </div>
                            </td>
                            <td> </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="table__row"
                            v-for="(item) in export_goods_lists" :key="item.id">
                            <td class="table__value table__value--inConfiguration table__value--inConfiguration_start">
                                <input type="text" class="input" v-model="item.name">
                            </td>
                            <td class="table__value table__value--inConfiguration table__value--text_right">
                                <template v-if="item.refreshing">
                                    <font-awesome-icon class="text-warning color" icon="circle-notch"
                                                       spin></font-awesome-icon>
                                </template>
                                <template v-else>
                                    <icon v-if="isChangedConfigurationRow(item.id, 0)"
                                          @click.native.stop="saveConfigurationsGoodsListChanges(item)"
                                          icon="floppy-disk" class="icon icon--export"></icon>
                                    <icon @click.native.stop="editModalConfigurationGoodsList(item)"
                                          icon="pencil-edit-button" class="icon icon--export"></icon>
                                    <icon @click.native="deleteGoodsListElement(item)" icon="delete"
                                          class="icon icon--export"></icon>
                                </template>
                            </td>
                        </tr>
                        </tbody>
                    </table>
    
                    <widget-actions v-if="!modalGoodsLists.open"
                                    :add="'addGoodsListConfiguration'"
                                    :trans="{add: $root.translateWords('Create action')}"></widget-actions>
                </div>
    
                <div v-else-if="show_menu_content === $root.translate.columns.configurations" class="XmlExport__configuration">
                    <table class="table table--padding-table">
                        <thead>
                        <tr class="table__row">
                            <td class="table__heading table__heading--inConfiguration_start">{{$root.translate.columns.configuration_name}}:</td>
                            <td class="table__heading">{{$root.translate.columns.products_list_name}}:</td>
                            <td class="table__heading">{{$root.translate.columns.link_to_file}}:</td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="table__row"
                            v-for="(item, index) in export_configuration" :key="item.id">
                            <td class="table__value table__value--inConfiguration table__value--inConfiguration_start">
                                <input type="text" class="input" v-model="item.name">
                            </td>
                            <td class="table__value table__value--inConfiguration">
                                <!-- XmlExport-configuration-element__input -->
                                <v-select
                                        :clearable="true"
                                        :searchable="true"
                                        :options="export_goods_lists"
                                        v-model="item.products_list_id"
                                        :reduce="export_goods_lists => export_goods_lists.id"
                                        class="input input--inForm"
                                        label="name">
                                    <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                                </v-select>
                            </td>
                            <td class="table__value table__value--inConfiguration">
                                <div class="copy">
                                    <!-- input--border-radius input--export
    
    
                                    @click.stop="copyLink(index)"  -->
                                    <input disabled type="text" class="input" v-model="item.link">
                                    <button class="copy__button" v-clipboard:copy="item.link">
                                        {{$root.translate.columns.copy_action}}
                                    </button>
                                    <input ref="copyInput" type="text" class="input input--invisible" v-model="item.link">
                                </div>
                            </td>
                            <td class="table__value table__value--inConfiguration">
                                <template v-if="item.refreshing">
                                    <font-awesome-icon class="text-warning table__value--text_right" icon="circle-notch"
                                                       spin></font-awesome-icon>
                                </template>
                                <template v-else style="position: relative">
                                    <icon v-if="isChangedConfigurationRow(item.id, 1)"
                                          @click.native.stop="saveConfigurationsExportChenge(item)"
                                          icon="floppy-disk" class="icon icon--export"></icon>
                                    <icon @click.native.stop="editModalConfigurationExport(index)" icon="pencil-edit-button"
                                          class="icon icon--export"></icon>
                                    <icon @click.native="deleteConfigurationElement(item)"
                                          icon="delete" class="icon icon--export"></icon>
                                </template>
                            </td>
                        </tr>
                        </tbody>
                    </table>
    
                    <widget-actions v-if="!modalConfiguration.open"
                                    :add="'openModalConfigurationExport'"
                                    :trans="{add: $root.translateWords('Create action')}"></widget-actions>
                </div>
            </div>
    
            <modal name="xml-GoodsList" width="80%" v-if="Object.keys(modalConfiguration).length"
                   :title="$root.translateWords(modalConfiguration.edit ? 'Editing configuration': 'Creating configuration')"
                   @closed="closeModalConfiguration">
                <template v-slot>
                    <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.name}}
                    </div>
                    <input type="text" class="input" v-model="modalGoodsLists.data.name">
                    </div>
                    <div v-if="countCheckedGoods" class="massActionsHeader massActionsHeader--products">
                        <div class="massActionsHeader__item">{{$root.translate.columns.selected_count}} {{countCheckedGoods}} <span
                                class="massActionsHeader__vertical-line"></span></div>
                        <div class="massActionsHeader__item ">
                            <v-select
                                    @input="deletePickedElement"
                                    :clearable="true"
                                    :searchable="true"
                                    :options="menu_drop_down_options"
                                    :reduce="item => item.type"
                                    :slot-scope="menu_drop_down_options"
                                    class="input massActionsHeader__input"
                                    label="title">
                                <template slot="option" slot-scope="option">
                                    <icon :icon="option.icon" class="icon"></icon>
                                    {{ option.title }}
                                </template>
                            </v-select>
                        </div>
                        <div class="massActionsHeader__link-end-line pr--25">
                            <a href="javascript:void(0)"
                               @click.stop="unCheckAllGoods()"
                               class="text-link text-link--fz_inherit">{{$root.translate.columns.cancel_selected}}
                            </a>
                        </div>
                    </div>
                    <div v-if="modalGoodsLists.data.products.length"
                         class="flex switcher">
                        <check :checked="modalGoodsLists.checked_all  === true"
                               @click.native.stop="selectAllGoods()"
                               class="switcher__icon"></check>
                        <span class="switcher__check-all-text">{{$root.translate.columns.select_all_action}}</span>
                    </div>

                    <perfect-scrollbar>

                        <div class="searchResults searchResults--goods-list">
                            <div>
                                <div v-for="(item, index) in modalGoodsLists.data.products"
                                     class="searchResultsItem searchResultsItem--related searchResultsItem--modal-goods-list searchResultsItem--padding-goods-list ">

                                    <div class="switcher">
                                        <check @click.native.stop="item.is_checked = !item.is_checked"
                                               :checked="item.is_checked === true"
                                               class="switcher__icon mr--16"></check>
                                        <img class="searchResultsItem__img searchResultsItem__img--goods_list"
                                             :src="item.filemanager_thumb" alt=""
                                             width="50px">
                                        <div class="flex flex--column flex--justify-space-between">
                                            <div class="searchResultsItem__name">{{item.translate.name}}</div>
                                            <div class="searchResultsItem__sku">
                                                {{item.sku}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="searchResults__delete-item-element">
                                        <icon @click.native.stop="deleteListElement(item.id ,index, modalGoodsLists.data.products)"
                                              icon="delete" class="icon--export"></icon>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </perfect-scrollbar>
    
    
                    <div class="flex flex--justify-end"
                         v-if="isChangedConfigurationRow(modalGoodsLists.data.id, 0)">
                        <a @click.stop="saveGoodsListChengesPopup"
                           href="javascript:void(0)" class="btn btn--confirm">
                            {{$root.translateWords('Save changes')}}
                        </a>
                    </div>

                </template>
            </modal>
    
            <modal name="xml-configuration" width="90%" v-if="Object.keys(modalGoodsLists).length"
                   :title="$root.translateWords(modalGoodsLists.edit ? 'Editing configuration': 'Creating configuration')"
                   @closed="closeModalConfiguration">
                <template v-slot>
                
                    <div class="configuration">
                    
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
    
                        <div class="configuration__section configurationSection">
                            <div class="configurationSection__title">
                                {{$root.translate.columns.products_list_name}}
                            </div>
                            <v-select
                                    :clearable="true"
                                    :searchable="true"
                                    :options="export_goods_lists"
                                    v-model="modalConfiguration.data.products_list_id"
                                    :reduce="export_goods_lists => export_goods_lists.id"
                                    class="input mt--20"
                                    label="name">
                                <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                            </v-select>
                        </div>
    
                        <div class="configuration__section configurationSection">
                        
                            <div class="flex flex--justify-space-between">
                                <div class="configurationSection__title">
                                    {{$root.translateWords('Path settings for data')}}
                                </div>
                                <a @click.stop="checkForDefaultSettings() ? setListPathsDefault() : false"
                                   href="javascript:void(0)" class="btn"
                                   :class="{'btn--default-settings':!checkForDefaultSettings(), 'btn--confirm btn--confirm-default':checkForDefaultSettings()}">
                                    {{$root.translateWords('Standard settings')}}
                                </a>
                            </div>
    
                            <table class="table">
                                <thead class="table__row">
                                <tr>
                                    <td class="table__heading table__heading--xml-export_data">{{$root.translate.columns.data}}:</td>
                                    <td class="table__heading table__heading--xml-export_data">{{$root.translate.columns.use_in_export}}?</td>
                                    <td class="table__heading">{{$root.translate.columns.path}}:</td>
                                    <td class="table__heading table__heading--xml-export_last-el">{{$root.translate.columns.last_element}}:</td>
                                </tr>
                                </thead>
                                <tbody
                                        v-for="(path, path_code) in modalConfiguration.data.settings.paths"
                                        v-if="canFillPath(path)">
                                <tr class="table__row">
                                    <td class="table__value table__value--inConfiguration">
                                        {{$root.translate.sync[path_code]}}
                                    </td>
                                    <td class="table__value table__value--inConfiguration">
                                        <div class="switcherStatus switcherStatus--inline">
                                            <div @click="path.status = false" class="switcherStatus__value"
                                                 :class="{'switcherStatus__value--active switcherStatus__value--active_off': path.status === false}">
                                                {{$root.translate.columns.no}}
                                            </div>
                                            <div @click="path.status = true" class="switcherStatus__value"
                                                 :class="{'switcherStatus__value--active': path.status === true}">
                                                {{$root.translate.columns.yes}}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="table__value table__value--inConfiguration">
                                        <v-select
                                                :clearable="true"
                                                :no-drop="true"
                                                placeholder="input"
                                                taggable multiple push-tags
                                                v-model="path.value"
                                                label="title"
                                                class="input input--inForm input--tags vs--multiply vs--path pt--0 pb--0"
                                                :create-option="makeNewPathItem"
                                                :components="{Deselect}">
                                            <template v-slot:selected-option-container="item">
                                                <div class="flex flex--align-center mb--12 mt--4 mr--8">
                                                    <span class="vs__selected"
                                                          :class="{'vs__selected--disabled': item.option.disabled}">
                                                        {{item.option.title}}
                                                        <button class="vs__deselect" v-if="!item.option.disabled"
                                                                @click.stop="path.value.splice(path.value.indexOf(item.option.label),1)">
                                                                <icon class="icon" icon="error"></icon>
                                                        </button>
                                                    </span>
                                                    <icon v-if="item.option !== path.value[path.value.length - 1]"
                                                          class="icon vs__selected--next" icon="next"></icon>
                                                </div>
                                            </template>
                                            <template #search="{ attributes, events }">
                                                <input
                                                        autocomplete="off"
                                                        type="search"
                                                        class="vs__search vs__search--for-path"
                                                        :placeholder="$root.translateWords('Add to list')"
                                                        v-bind="attributes"
                                                        v-on="events">
                                            </template>
                                        </v-select>
                                    </td>
                                    <td class="table__value table__value--inConfiguration">
                                        <template v-if="path.is_attribute !== null">
                                            <div class="flex flex--align-center">
                                                <div class="switcher">
                                                    <check @click.native.stop="path.is_attribute = false"
                                                           :checked="path.is_attribute  === false"
                                                           class="switcher__icon switcher__icon--fix-width"></check>
                                                    <span class="switcher__label">Тег</span>
                                                </div>
                                            </div>
                                            <div class="flex flex--align-center">
                                                <div class="switcher">
                                                    <check @click.native.stop="path.is_attribute = true"
                                                           :checked="path.is_attribute === true"
                                                           class="switcher__icon switcher__icon--fix-width"></check>
                                                    <span class="switcher__label">{{$root.translate.columns.path_attribute}}</span>
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="flex flex--align-center">
                                                <div class="switcher">
                                                    <check :checked="true"
                                                           class="switcher__icon switcher__icon--fix-width"></check>
                                                    <span class="switcher__label">{{$root.translate.columns.tag}}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                                </tbody>
    
                            </table>
    
                        </div>
    
                        <div class="configuration__section configurationSection">
                        
                            <div class="configurationSection__title">
                                {{$root.translate.columns.extra_settings}}
                            </div>
    
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.settings_of_characteristics}}
                                </div>
                                <div class="flex mb--30">
                                    <div class="switcher switcher--flex">
                                        <check @click.native.stop="modalConfiguration.data.settings.attributes.is_selected_main = false"
                                               :checked="modalConfiguration.data.settings.attributes.is_selected_main === false"
                                               class="switcher__icon"></check>
                                        <span class="switcher__label">{{$root.translate.columns.dont_select_main}}</span>
                                        <icon icon="icon" class="icon switcher__help"></icon>
                                    </div>
                                    <div class="switcher switcher--flex">
                                        <check @click.native.stop="modalConfiguration.data.settings.attributes.is_selected_main = true"
                                               :checked="modalConfiguration.data.settings.attributes.is_selected_main === true"
                                               class="switcher__icon"></check>
                                        <span class="switcher__label">{{$root.translate.columns.select_main}}</span>
                                        <icon icon="icon" class="icon switcher__help"></icon>
                                    </div>
                                </div>
    
                                <div v-if="modalConfiguration.data.settings.attributes.is_selected_main === true"
                                     class="singleFormGroup">
                                    <div class="singleFormGroup__title">{{$root.translate.columns.main_characteristics}}:</div>
                                    <div class="singleFormGroup__field">
                                        <v-select
                                                multiple
                                                :clearable="true"
                                                :searchable="true"
                                                :clear-search-on-select="true"
                                                :options="attributes_list.filter(item=> modalConfiguration.data.settings.attributes.list.indexOf(item)=== -1)"
                                                v-model="modalConfiguration.data.settings.attributes.list"
                                                class="input input--inForm vs--multiply pt--0"
                                                :reduce="attribute => attribute.id"
                                                :components="{Deselect, OpenIndicator}"
                                                label="name"
                                                :placeholder="$root.translateWords('Create value')"/>
                                    </div>
                                </div>
                            </div>
    
                            <div class="singleFormGroup">
                                <div class="singleFormGroup__title">
                                    {{$root.translate.columns.include_parent_categories}}?
                                </div>
                                <div class="flex mb--30">
                                    <div class="switcher switcher--flex" style="">
                                        <check @click.native.stop="modalConfiguration.data.settings.has_include_parent_categories = false"
                                               :checked="modalConfiguration.data.settings.has_include_parent_categories === false"
                                               class="switcher__icon"></check>
                                        <span class="switcher__label">{{$root.translate.columns.no}}</span>
                                    </div>
                                    <div class="switcher switcher--flex" style="">
                                        <check @click.native.stop="modalConfiguration.data.settings.has_include_parent_categories = true"
                                               :checked="modalConfiguration.data.settings.has_include_parent_categories === true"
                                               class="switcher__icon"></check>
                                        <span class="switcher__label">{{$root.translate.columns.yes}}</span>
                                    </div>
                                </div>
    
                            </div>
    
    
    
                        </div>
    
                    </div>
    
                    <div class="flex flex--justify-end"
                    v-if="isChangedConfigurationRow(modalConfiguration.data.id, 1)">
                        <a @click.stop="saveConfigurationsChangesPopup"
                           href="javascript:void(0)" class="btn btn--confirm">
                            {{$root.translateWords('Save changes')}}
                        </a>
                    </div>
    
    
                </template>
            </modal>
        </div>
    </div>
</template>

<script>

    import {PerfectScrollbar} from 'vue2-perfect-scrollbar'

    export default {
        name: "ExlExport",
        components:{
            PerfectScrollbar
        },
        data() {
            return {
                export_goods_lists: [],
                menu_drop_down_options: [{
                    title: this.$root.translate.columns.delete,
                    icon: "delete-light",
                    type: "delete"
                }],
                export_configuration: [],
                default_paths: {

                    company_name: {
                        status: true,
                        is_attribute: false,
                        access_fill_if: null,
                        value: [
                            {
                                title: 'shop',
                                disabled: false
                            },
                            {
                                title: 'company',
                                disabled: false
                            }
                        ]
                    },
                    site_url: {
                        status: true,
                        is_attribute: false,
                        access_fill_if: null,
                        value: [
                            {
                                title: 'shop',
                                disabled: false
                            },
                            {
                                title: 'url',
                                disabled: false
                            }
                        ]
                    },

                    currencies_container: {
                        access_fill_if: null,
                        status: true,
                        is_attribute: null,
                        value: [
                            {
                                title: 'shop',
                                disabled: false
                            },
                            {
                                title: 'currencies',
                                disabled: false
                            },
                        ]
                    },
                    currency_tag: {
                        access_fill_if: null,
                        status: true,
                        is_attribute: null,
                        value: [
                            {
                                title: this.$root.translate.sync.currencies_container,
                                disabled: true
                            },
                            {
                                title: 'currency',
                                disabled: false
                            },
                        ]
                    },
                    currency_code: {
                        access_fill_if: 'currency_tag',
                        status: true,
                        is_attribute: true,
                        value: [
                            {
                                title: this.$root.translate.sync.currency_tag,
                                disabled: true
                            },
                            {
                                title: 'name',
                                disabled: false
                            },
                        ]
                    },
                    currency_rate: {
                        access_fill_if: 'currency_tag',
                        status: true,
                        is_attribute: true,
                        value: [
                            {
                                title: this.$root.translate.sync.currency_tag,
                                disabled: true
                            },
                            {
                                title: 'rate',
                                disabled: false
                            },
                        ]
                    },

                    categories_container: {
                        access_fill_if: null,
                        status: true,
                        is_attribute: null,
                        value: [
                            {
                                title: 'shop',
                                disabled: false
                            },
                            {
                                title: 'categories',
                                disabled: false
                            },
                        ]
                    },
                    category_tag: {
                        access_fill_if: null,
                        status: true,
                        is_attribute: null,
                        value: [
                            {
                                title: this.$root.translate.sync.categories_container,
                                disabled: true
                            },
                            {
                                title: 'category',
                                disabled: false
                            },
                        ]
                    },
                    category_id: {
                        access_fill_if: 'category_tag',
                        status: true,
                        is_attribute: true,
                        value: [
                            {
                                title: this.$root.translate.sync.category_tag,
                                disabled: true
                            },
                            {
                                title: 'id',
                                disabled: false
                            }
                        ]
                    },
                    category_name: {
                        access_fill_if: 'category_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.category_tag,
                                disabled: true
                            },
                            {
                                title: 'name',
                                disabled: false
                            }
                        ]
                    },
                    category_parent_id: {
                        access_fill_if: 'category_tag',
                        status: true,
                        is_attribute: true,
                        value: [
                            {
                                title: this.$root.translate.sync.category_tag,
                                disabled: true
                            },
                            {
                                title: 'parent_id',
                                disabled: false
                            }
                        ]
                    },

                    products_container: {
                        access_fill_if: null,
                        status: true,
                        is_attribute: null,
                        value: [
                            {
                                title: 'shop',
                                disabled: false
                            },
                            {
                                title: 'offers',
                                disabled: false
                            },
                        ]
                    },
                    product_tag: {
                        access_fill_if: null,
                        status: true,
                        is_attribute: null,
                        value: [
                            {
                                title: this.$root.translate.sync.products_container,
                                disabled: true
                            },
                            {
                                title: 'offer',
                                disabled: false
                            },
                        ]
                    },
                    product_id: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: true,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'id',
                                disabled: false
                            }
                        ]
                    },
                    product_quantity_status: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: true,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'available',
                                disabled: false
                            }
                        ]
                    },
                    product_url: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'url',
                                disabled: false
                            }
                        ]
                    },
                    product_vendor: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'vendor',
                                disabled: false
                            }
                        ]
                    },
                    product_quantity: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'stock_quantity',
                                disabled: false
                            }
                        ]
                    },
                    product_actual_price: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'price',
                                disabled: false
                            }
                        ]
                    },
                    product_old_price: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'old_price',
                                disabled: false
                            }
                        ]
                    },
                    product_currency: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'currencyId',
                                disabled: false
                            }
                        ]
                    },
                    product_sku: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'vendor_code',
                                disabled: false
                            }
                        ]
                    },
                    product_description: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'description',
                                disabled: false
                            }
                        ]
                    },
                    product_category_id: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'categoryId',
                                disabled: false
                            }
                        ]
                    },
                    product_images: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: null,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'picture',
                                disabled: false
                            }
                        ]
                    },
                    product_name: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'name',
                                disabled: false
                            }
                        ]
                    },
                    product_attribute: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: null,
                        value: [
                            {
                                title: this.$root.translate.sync.product_tag,
                                disabled: true
                            },
                            {
                                title: 'param',
                                disabled: false
                            }
                        ]
                    },
                    product_attribute_name: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: true,
                        value: [
                            {
                                title: this.$root.translate.sync.product_attribute,
                                disabled: true
                            },
                            {
                                title: 'name',
                                disabled: false
                            }
                        ]
                    },
                    product_attribute_value: {
                        access_fill_if: 'product_tag',
                        status: true,
                        is_attribute: false,
                        value: [
                            {
                                title: this.$root.translate.sync.product_attribute,
                                disabled: true
                            },
                        ]
                    },
                },
                export_menu_elements: [
                    {
                        name: this.$root.translateWords('Products lists'),
                        isActive: false,
                    },
                    {
                        name: this.$root.translate.columns.configurations,
                        isActive: false,
                    }
                ],
                show_menu_content: this.$root.translateWords('Products lists'),
                modalGoodsLists: {
                    edit: false,
                    open: false
                },
                modalConfiguration: {
                    edit: false,
                    open: false
                },
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
                attributes_list: "",
                // checked_all: false,

                saved_original_export_Goods_List: [],
                saved_original_export_configurations: [],
            }
        },
        created() {
            let self = this;
            this.getGoodsList();
            this.getConfigurationData();
            this.getAttributes();
        },
        computed: {
            countCheckedGoods() {
                return this.modalGoodsLists.data.products.filter(element => element.is_checked === true).length;
            }
        },
        methods: {
            copyLink(index) {
                this.$refs.copyInput[index].select();
                document.execCommand("copy");
            },
            showMenuElement(name) {
                this.show_menu_content = name;
            },
            editModalConfigurationGoodsList(item) {
                let self = this;
                let configuration = item;
                this.modalGoodsLists.edit = true;
                this.modalGoodsLists.open = true;
                this.$set(this.modalGoodsLists, 'data', configuration);
                this.$root.changePopupShowStatus('xml-GoodsList', true);
                this.$set(this.modalGoodsLists, "checked_all", false);

            },
            editModalConfigurationExport(index) {
                let configuration = this.export_configuration[index];
                this.modalConfiguration.edit = true;
                this.modalConfiguration.open = true;
                this.$set(this.modalConfiguration, 'data', configuration);
                this.$root.changePopupShowStatus('xml-configuration', true);
            },
            openModalConfigurationExport() {
                let configuration = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    name: "",
                    link: "",
                    refreshing: false,
                    products_list_id: null,
                    settings: {
                        paths: [],
                        attributes: {
                            is_selected_main: false,
                            list: []
                        },
                        has_include_parent_categories: false
                    }
                };
                this.export_configuration.push(configuration);
                this.modalConfiguration.edit = true;
                this.modalConfiguration.open = true;
                this.$set(this.modalConfiguration, 'data', configuration);
                this.modalConfiguration.data.settings.paths = this.$root.copy(this.default_paths)
                this.$root.changePopupShowStatus('xml-configuration', true);
            },
            closeModalConfiguration() {
                this.$root.changePopupShowStatus('xml-configuration', false);
                this.$root.changePopupShowStatus('xml-GoodsList', false);
                this.modalGoodsLists.open = false;
                this.modalConfiguration.open = false;
            },
            selectAllGoods() {
                this.modalGoodsLists.checked_all = !this.modalGoodsLists.checked_all;
                this.modalGoodsLists.data.products.forEach(element => element.is_checked = this.modalGoodsLists.checked_all)
            },
            unCheckAllGoods() {
                this.modalGoodsLists.data.products.forEach(element => element.is_checked = false);
                this.modalGoodsLists.checked_all = false;
            },
            isChangedConfigurationRow(id, type) {
                let save;
                let export_data;
                if (type === 0) {
                    save = this.saved_original_export_Goods_List
                    export_data = this.export_goods_lists;
                } else if (type === 1) {
                    save = this.saved_original_export_configurations
                    export_data = this.export_configuration;
                }
                let originalPosition = save.findIndex(item => {
                    return item.id === id;
                });
                if (originalPosition === -1) {
                    return true;
                }
                let currentPosition = export_data.findIndex(item => {
                    return item.id === id;
                });

                let current = this.$root.copy(export_data[currentPosition]);
                delete current.refreshing;

                let saved = this.$root.copy(save[originalPosition]);
                delete saved.refreshing;

                return JSON.stringify(current) !== JSON.stringify(saved);
            },

            deleteGoodsListElement(item) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let self = this;
                    item.refreshing = true;

                    if (typeof item.id === 'number') {
                        axios.delete('/admin/export-products-list/' + item.id).then(Response => {
                            let index = self.export_goods_lists.findIndex(elem => elem.id === item.id);
                            let save_index = self.saved_original_export_Goods_List.findIndex(elem => elem.id === item.id);
                            self.export_goods_lists.splice(index, 1);
                            self.saved_original_export_Goods_List.splice(save_index, 1);
                            this.$root.notify(Response.data);
                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            item.refreshing = false;
                        });
                    } else {
                        let index = this.export_goods_lists.findIndex(elem => elem.id === item.id);

                        let save_index = this.saved_original_export_Goods_List.findIndex(elem => elem.id === item.id);
                        self.export_goods_lists.splice(index, 1);
                        if (save_index !== -1) {
                            self.saved_original_export_Goods_List.splice(save_index, 1);
                        }
                    }
                });
            },
            deleteConfigurationElement(item) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let self = this;
                    item.refreshing = true;

                    if (typeof item.id === 'number') {
                        axios.delete('/admin/export-configurations/' + item.id).then(Response => {
                            let index = self.export_configuration.findIndex(elem => elem.id === item.id);
                            let save_index = this.saved_original_export_configurations.findIndex(elem => elem.id === item.id);
                            self.export_configuration.splice(index, 1);
                            self.saved_original_export_configurations.splice(save_index, 1);
                            item.refreshing = false;
                            this.$root.notify(Response.data);
                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            item.refreshing = false;
                        });
                    } else {
                        let index = self.export_configuration.findIndex(elem => elem.id === item.id);
                        let save_index = this.saved_original_export_configurations.findIndex(elem => elem.id === item.id);
                        self.export_configuration.splice(index, 1);
                        if (save_index !== -1) {
                            self.saved_original_export_configurations.splice(save_index, 1);
                        }

                    }
                });
            },

            deleteListElement(id, index, arr) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    if (arr[index].id === id) {
                        arr.splice(index, 1);
                    }
                });
            },

            saveConfigurationsGoodsListChanges(item) {
                let self = this;
                let index = self.saved_original_export_Goods_List.findIndex(elem => elem.id === item.id);
                let request;

                let push_item = self.$root.copy(item);
                push_item.products = push_item.products.map(elem => elem.id);
                item.refreshing = true
                if (typeof (item.id) === 'number') {
                    request = axios.put('/admin/export-products-list/' + item.id, push_item);
                } else {
                    request = axios.post('/admin/export-products-list', push_item);
                }
                request.then(Response => {
                    if (typeof item.id !== 'number') {
                        item.id = Response.data.id;
                    }
                    if (index !== -1) {
                        self.saved_original_export_Goods_List.splice(index, 1);
                    }
                    self.saved_original_export_Goods_List.push(self.$root.copy(item));
                    item.refreshing = false
                    this.$root.notify(Response.data);

                    if (self.modalGoodsLists.open === true) {
                        self.modalGoodsLists.open = false;
                        self.$root.changePopupShowStatus('xml-GoodsList', false);
                    }
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    item.refreshing = false;
                })
            },

            saveConfigurationsExportChenge(item) {
                let self = this;
                let index = this.saved_original_export_configurations.findIndex(elem => elem.id === item.id);
                let request;
                item.refreshing = true;

                let modified_configuration = this.$root.copy(item);

                for (let key in modified_configuration.settings.paths) {

                    delete modified_configuration.settings.paths[key].access_fill_if;

                    modified_configuration.settings.paths[key] = {
                        value: modified_configuration.settings.paths[key].value.filter(item => !item.disabled).map(item => item.title),
                        is_attribute: modified_configuration.settings.paths[key].is_attribute,
                        status: modified_configuration.settings.paths[key].status,
                    };

                }

                if (typeof item.id === 'number') {
                    request = axios.put('/admin/export-configurations/' + item.id, modified_configuration);
                } else {
                    request = axios.post('/admin/export-configurations', modified_configuration);
                }
                request.then(Response => {
                    if (typeof item.id !== 'number') {
                        item.id = Response.data.id;
                        item.link = Response.link;
                    }
                    if (index !== -1) {
                        self.saved_original_export_configurations.splice(index, 1);
                    }
                    self.saved_original_export_configurations.push(self.$root.copy(item));
                    item.refreshing = false

                    this.$root.notify(Response.data);

                    if (self.modalConfiguration.open === true) {
                        self.$root.changePopupShowStatus('xml-configuration', false);
                        self.modalConfiguration.open = false;
                    }

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    item.refreshing = false;
                });
            },
            deletePickedElement(type) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let self = this;
                    if (type === "delete") {
                        this.$set(this.modalGoodsLists.data, "products", this.modalGoodsLists.data.products.filter(item => !item.is_checked));
                    }
                });
            },
            canFillPath(path) {
                return path.access_fill_if === null || (this.modalConfiguration.data.settings.paths[path.access_fill_if].status === true && this.modalConfiguration.data.settings.paths[path.access_fill_if].value.filter(item => !item.disabled).length);
            },
            makeNewPathItem(text) {
                return {
                    title: text,
                    disabled: false,
                };
            },
            addGoodsListConfiguration() {
                let self = this;
                let empty_element = {
                    name: "",
                    id: 'tmp-' + Math.random(100000, 10000000),
                    refreshing: false,
                    products: []
                };
                this.export_goods_lists.push(empty_element);
            },
            checkForDefaultSettings() {
                return JSON.stringify(this.modalConfiguration.data.settings.paths) !== JSON.stringify(this.default_paths);
            },

            setListPathsDefault() {
                this.$set(this.modalConfiguration.data.settings, 'paths', this.$root.copy(this.default_paths));
            },

            saveConfigurationsChangesPopup() {
                let self = this;
                let item_index = this.export_configuration.findIndex(elem => elem.id === self.modalConfiguration.data.id);
                let item = this.export_configuration[item_index];

                this.saveConfigurationsExportChenge(item)

            },
            saveGoodsListChengesPopup() {
                let self = this;

                let item_index = this.export_goods_lists.findIndex(elem => elem.id === self.modalGoodsLists.data.id);
                let item = this.export_goods_lists[item_index];

                this.saveConfigurationsGoodsListChanges(item);


            },
            getGoodsList() {
                let self = this;
                axios.get('/admin/export-products-list').then(Response => {
                    Response.data.map(elem => {
                        elem.refreshing = false;
                    });
                    Response.data.forEach(item => item.products.map(item => {
                            self.$set(item, "is_checked", false)
                        })
                    );

                    self.$set(self, 'export_goods_lists', Response.data);

                    self.$set(self, 'saved_original_export_Goods_List', self.$root.copy(self.export_goods_lists));
                });
            },
            getConfigurationData() {
                let self = this;
                axios.get('/admin/export-configurations').then(Response => {

                    Response.data.map(elem => elem.refreshing = false);

                    for (let configuration of Response.data) {
                        let copyPaths = this.$root.copy(this.default_paths);

                        let force_updating = false;

                        for (let path_code in copyPaths) {
                            if (Object(configuration.settings.paths).hasOwnProperty(path_code)) {

                                copyPaths[path_code].value = copyPaths[path_code].value.filter(value => value.disabled);

                                configuration.settings.paths[path_code].value.forEach(value => copyPaths[path_code].value.push({
                                    title: value,
                                    disabled: false
                                }));

                                copyPaths[path_code].is_attribute = configuration.settings.paths[path_code].is_attribute;

                                copyPaths[path_code].status = configuration.settings.paths[path_code].status;


                            } else {
                                force_updating = true;
                            }
                        }

                        configuration.settings.paths = copyPaths;
                    }

                    self.$set(self, 'export_configuration', Response.data);
                    self.$set(self, 'saved_original_export_configurations', self.$root.copy(self.export_configuration));
                });
            },
            getAttributes() {
                let self = this;
                axios.get('/admin/attributes', {
                    params: {
                        with_translate: true
                    }
                }).then(Response => {
                    self.$set(self, 'attributes_list', Response.data.attributes);
                })
            }
        }
    }
</script>

<style lang="stylus" scoped>

</style>