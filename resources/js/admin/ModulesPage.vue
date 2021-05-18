<template>
    <div class="table-list-container">
        <div class="flex flex--justify-space-between">
            <div>
                <h2 class="mainContent__heading mainContent__heading--inProduct">
                    {{$root.translate.menu['design-modules'].items.modules}}
                </h2>
            </div>
        </div>
        <div class="singleForm singleForm--inModulesTpl">
            <div class="moduleTemplates">
                <menu class="menu menu--inModulesTpl">
                    <ul class="menu__list">
                        <li class="menu__item" v-for="(moduleTemplate, index) in moduleTemplates">
                            <div class="menu__heading" :class="{'menu__heading--active': openModuleTpl === index }"
                                 @click="openModuleTpl = index">
                                <span>
                                    {{$root.translate.modules[moduleTemplate.name]}}
                                </span>
                            </div>
                        </li>
                    </ul>
                </menu>
                <main class="mainContent" v-if="moduleTemplates.length">
                    <div class="listData listData--noBg" v-if="modules.length">
                        <vue-good-table
                                :columns="tableModulesColumns"
                                :rows="modules"
                                styleClass="table"
                                row-style-class="table__row">
                            <template slot="table-row" slot-scope="props">
                                <template v-if="props.column.field === 'name'">
                                    <div class="flex flex--align-center">
                                        <input type="text" class="input"
                                               v-model="modules[props.index].name">
                                    </div>
                                </template>
                                <template v-else-if="props.column.field === 'status'">
                                    <div class="flex flex--align-center">
                                        <div class="switcherStatus">
                                            <div @click="modules[props.index].status = false"
                                                 class="switcherStatus__value"
                                                 :class="{'switcherStatus__value--active switcherStatus__value--active_off': modules[props.index].status === false}">
                                                {{$root.translate.columns.disabled_short}}
                                            </div>
                                            <div @click="modules[props.index].status = true"
                                                 class="switcherStatus__value"
                                                 :class="{'switcherStatus__value--active': modules[props.index].status === true}">
                                                {{$root.translate.columns.enabled_short}}
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template v-else-if="props.column.field === 'actions'">
                                    <template v-if="modules[props.index].refreshing">
                                        <font-awesome-icon class="text-warning" icon="circle-notch"
                                                           spin></font-awesome-icon>
                                    </template>
                                    <template v-else>
                                        <a v-if="isChangedRow(modules[props.index].id)" class="table__action"
                                           href="javascript:void(0)" @click.stop="updateModule(modules[props.index])">
                                            <icon icon="floppy-disk" class="icon"></icon>
                                        </a>
                                        <a href="javascript:void(0)" class="table__action js-open-modal"
                                           @click.stop="showEditModuleForm(props.index)">
                                            <icon icon="pencil-edit-button" class="icon"></icon>
                                        </a>
                                        <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                           href="javascript:void(0)" @click.stop="removeModule(props.index)">
                                            <icon icon="delete" class="icon"></icon>
                                        </a>
                                    </template>
                                </template>
                            </template>

                        </vue-good-table>

                    </div>
                    <div v-else class="listEmpty">
                        <div class="listEmpty__heading">{{$root.translateWords('Your modules list is empty')}} :(</div>
                        <div class="listEmpty__text">
                            {{$root.translateWords('You may add them')}}
                            <a class="listEmpty__link" href="javascript:void(0)" @click.stop="createNewModule">{{$root.translateWords('manually')}}</a>
                        </div>
                    </div>

                    <modal name="module-modal" @closed="closeEditModal" width="70%"
                           :title="!editingModule.id ? $root.translateWords('Creating module with type:' ) + ' ' + moduleTemplates[openModuleTpl].name : $root.translateWords('Editing module:') + ' ' + editingModule.name">
                        <template v-slot>
                            <component :is="moduleComponent"
                                       ref="module"
                                       v-if="moduleComponent"
                                       :module-info="editingModule"
                                       :banners="lists.banners"
                                       :deselect="Deselect"
                                       :open-indicator="OpenIndicator"
                                       :products="lists.products"
                                       :on-search-products="onSearchProducts"
                                       :articles="lists.articles"
                                       :on-search-articles="onSearchArticles"
                                       v-on:set-editing-module-info="moduleInfo => editingModule = moduleInfo"
                            ></component>
                        </template>
                    </modal>

                </main>
            </div>
        </div>

        <template v-if="moduleTemplates.length">
            <template v-if="Object.keys(editingModule).length">
                <widget-actions :remove="editingModule.id ? 'removeModule' : false"
                                :store="isChangedRow(editingModule.id) || !editingModule.id ? 'createOrEditModule' : false"></widget-actions>
            </template>
            <template v-else>
                <widget-actions add="createNewModule"
                                :trans="{add: $root.translateWords('Create module')}"
                                :popups="['add']"
                ></widget-actions>
            </template>
        </template>

    </div>

</template>

<script>
import ClickOutside from 'vue-click-outside'

    export default {
        name: "ModulesPage",
        data() {
            return {
                refreshing: false,
                moduleTemplates: [],
                openModuleTpl: 0,
                tableModulesColumns: [
                    {
                        label: this.$root.translate.columns.name,
                        field: 'name',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.status,
                        field: 'status',
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
                    }
                ],
                savedOriginal: [],

                moduleComponent: {},
                editingModule: {},

                lists: {
                    banners: [],
                    products: [],
                    articles: []
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
            }
        },
        created() {

            axios.get('/admin/banners').then(httpResponse => {
                this.lists.banners = httpResponse.data.banners;
            });

            axios.get('/admin/modules').then(httpResponse => {

                httpResponse.data.forEach(moduleTpl => {
                    moduleTpl.modules.forEach(module => module.refreshing = false);
                });

                this.moduleTemplates = httpResponse.data;

                this.savedOriginal = this.$root.copy(this.moduleTemplates);
            });

            this.onSearchProducts();

            this.onSearchArticles();

        },
        computed: {
            modules() {
                return this.moduleTemplates.length ? this.moduleTemplates[this.openModuleTpl].modules : [];
            }
        },
        methods: {
            createOrEditModule() {

                let moduleInfo = this.$refs.module.module;

                if (Object(moduleInfo).hasOwnProperty('name') && moduleInfo.name.length < 100 && Object(moduleInfo).hasOwnProperty('status') && Object(moduleInfo).hasOwnProperty('decoded_setting')) {

                    this.refreshing = true;

                    if (moduleInfo.id) {
                        axios.put('/admin/modules/' + moduleInfo.id, moduleInfo).then(httpResponse => {
                            this.$root.notify(httpResponse.data);

                            this.savedOriginal = this.$root.copy(this.moduleTemplates);

                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            this.refreshing = false;
                        });
                    } else {
                        axios.post('/admin/modules', moduleInfo).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.$set(moduleInfo, 'id', httpResponse.data.id);

                            let moduleTemplate = this.moduleTemplates[this.openModuleTpl];

                            moduleTemplate.modules.push({
                                id: moduleInfo.id,
                                decoded_setting: moduleInfo.decoded_setting,
                                name: moduleInfo.name,
                                status: moduleInfo.status,
                                template_module_id: moduleInfo.template_module_id,
                                refreshing: false
                            });

                            this.savedOriginal = this.$root.copy(this.moduleTemplates);

                            this.$root.changePopupShowStatus('module-modal', false);

                        }).catch(error => {
                            if (error.response) this.$root.notify(error.response.data);
                        }).finally(() => {
                            this.refreshing = false;
                        });
                    }
                } else {
                    alert(this.$root.translate.columns.error_modules_validation);
                }
            },
            removeModule(module_index = null) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let moduleId, moduleIndex;

                    let module = Object.keys(this.editingModule).length ? this.editingModule : this.modules[module_index];

                    if (module_index !== null) {
                        moduleId = this.modules[module_index].id;
                        moduleIndex = module_index;

                        module.refreshing = true;

                    } else if (module !== null) {
                        moduleId = module.id;
                        moduleIndex = this.modules.findIndex(tpl_module => tpl_module.id === module.id);

                        this.refreshing = true;
                    }

                    if (moduleId) {
                        axios.delete('/admin/modules/' + moduleId).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.modules.splice(moduleIndex, 1);


                        }).finally(() => {
                            if (module !== null) {
                                this.refreshing = false;

                                this.$root.changePopupShowStatus('module-modal', false);

                            } else {
                                module.refreshing = false;
                            }
                        });
                    }
                });
            },

            updateModule(module) {

                module.refreshing = true;

                axios.put('/admin/modules/' + module.id, module).then(httpResponse => {
                    this.$root.notify(httpResponse.data);

                    this.savedOriginal = this.$root.copy(this.moduleTemplates);

                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    module.refreshing = false;
                });

            },
            isChangedRow(moduleId) {

                let current = this.$root.copy(this.moduleTemplates[this.openModuleTpl].modules.find(module => module.id === moduleId));

                delete current.refreshing;

                let saved = this.$root.copy(this.savedOriginal[this.openModuleTpl].modules.find(module => module.id === moduleId));

                delete saved.refreshing;

                return JSON.stringify(current) !== JSON.stringify(saved);

            },
            showEditModuleForm(moduleIndex) {
                this.editingModule = this.modules[moduleIndex];

                let moduleTemplate = this.moduleTemplates[this.openModuleTpl];

                this.moduleComponent = require(`./modules/module${moduleTemplate.name}`).default;

                this.$root.changePopupShowStatus('module-modal', true);

            },
            closeEditModal() {

                this.editingModule = {};

                this.$set(this, 'moduleComponent', {});

                this.$root.changePopupShowStatus('module-modal', false);

            },
            createNewModule() {

                let moduleTemplate = this.moduleTemplates[this.openModuleTpl];

                this.editingModule = {
                    template_module_id: moduleTemplate.id,
                    status: false,
                    name: '',
                    decoded_setting: {},
                    refreshing: false
                };

                this.moduleComponent = require(`./modules/module${moduleTemplate.name}`).default;

                this.$root.changePopupShowStatus('module-modal', true);

            },
            onSearchProducts(phrase = null, loading = null) {
                if (loading !== null) loading(true);
                this.getProducts(phrase, this, loading);
            },
            getProducts: _.debounce((phrase, self, loading) => {
                axios.get('/admin/products', {
                    params: {
                        phrase: phrase,
                        autocomplete: true,
                        exclude: self.$route.params.id
                    }
                }).then(
                    httpResponse => {

                        self.$set(self.lists, 'products', httpResponse.data);

                        if (loading !== null) loading(false);
                    }
                )
            }),
            onSearchArticles(phrase = null, loading = null) {
                if (loading !== null) loading(true);
                this.getArticles(phrase, this, loading);
            },
            getArticles: _.debounce((phrase, self, loading) => {
                axios.get('/admin/articles', {
                    params: {
                        phrase: phrase,
                        autocomplete: true,
                        exclude: self.$route.params.id
                    }
                }).then(httpResponse => {
                    self.$set(self.lists, 'articles', httpResponse.data.articles);
                    if (loading !== null) loading(false);
                });
            }, 500),
        }
    }
</script>