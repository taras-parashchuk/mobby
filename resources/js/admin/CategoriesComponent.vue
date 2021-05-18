<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.catalog.items.categories}}</h2>

        <div class="singleForm listCategories">

            <div class="flex flex--justify-space-between listCategories__headings">
                <div class="table__heading">
                    {{$root.translate.columns.name}}
                </div>
                <div class="table__heading table__heading--lastInListCategories">
                    {{$root.translate.columns.status}}
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
            </div>

            <draggable
                    v-model="rows"
                    v-bind="dragOptions"
                    @start="drag = true"
                    @end="drag = false"
                    @change="debouncedChangeHierarchy"
                    handle=".handle">

                <tree-category :change-hierarchy="debouncedChangeHierarchy" :list="false" :get-original="getOriginal"
                               :original-category="getOriginal(category.id)"
                               :remove="removeCategory" :store="storeCategory" :key="'cat-1-'+index"
                               v-for="(category, index) in rows" :category="category" :lvl="1"></tree-category>

            </draggable>

        </div>

        <widget-actions add="addCategory" :trans="{'add':$root.translateWords('Create category')}"></widget-actions>

    </div>
</template>

<script>

    import draggable from 'vuedraggable'

    export default {
        name: "categoriesComponent",
        components: {
            'tree-category': require('./treeCategoryComponent').default,
            draggable
        },
        data() {
            return {
                rows: [],
                drag: false,
                savedOriginal: [],
                refreshing: false
            }
        },
        created() {
            this.loadData();
        },
        computed: {
            dragOptions() {
                return {
                    animation: 200,
                    group: "description",
                    disabled: false,
                    ghostClass: "ghost"
                };
            }
        },
        methods: {
            sortOrderAsc(a, b) {
                return a.sort_order - b.sort_order;
            },
            debouncedChangeHierarchy: _.debounce(function () {
                this.changeHierarchy();
            }, 500),
            changeHierarchy() {
                this.refreshing = true;

                axios.put('/admin/categories/update-hierarchy', {
                    categories: this.rows
                }).then(httpResponse => {
                    this.$root.notify(httpResponse.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            },
            getOriginal(category_id) {
                return this.savedOriginal.find(original => {
                    return original.id === category_id;
                });
            },
            async loadData() {
                let self = this;

                return axios.get('/admin/categories')
                    .then(httpResponse => {

                        this.$set(this, 'rows', []);

                        httpResponse.data.categories = httpResponse.data.categories.sort(this.sortOrderAsc);

                        this.fillShowChildren(httpResponse.data.categories);

                        httpResponse.data.categories.forEach(category => {
                            self.rows.push(category);
                        });

                        this.$set(this, 'savedOriginal', []);

                        this.fillOriginal(this.rows);

                    });
            },
            fillOriginal(rows) {

                let self = this;

                for (let index in rows) {

                    let row = rows[index];

                    let copy = self.$root.copy(row);

                    delete copy.refreshing;
                    delete copy.children;

                    self.savedOriginal.push(copy);

                    if (row.children.length) {
                        self.fillOriginal(row.children);
                    }
                }
            },
            fillShowChildren(rows) {
                let self = this;

                for (let index in rows) {

                    let row = rows[index];

                    row.showChildren = false;

                    row.refreshing = false;

                    row.children = row.children.sort(this.sortOrderAsc);

                    if (row.children.length) {
                        self.fillShowChildren(row.children);
                    }
                }
            },
            removeCategory(list, category) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    if (list === false) list = this.rows;

                    let id = category.id;

                    let index = list.findIndex(item => {
                        return item.id === category.id;
                    });

                    category.refreshing = true;

                    let originalPosition = this.savedOriginal.findIndex(category => {
                        return category.id === id
                    });

                    if (typeof id == 'number') {
                        axios.delete('/admin/categories/' + id)
                            .then(httpResponse => {

                                this.$root.notify(httpResponse.data);

                                category.refreshing = false;

                                list.splice(index, 1);

                                this.savedOriginal.splice(originalPosition, 1);
                            });
                    } else {
                        list.splice(index, 1);
                    }
                });
            },
            addCategory() {
                let category = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    sort_order: 0,
                    translates: [],
                    href: '',
                    refreshing: false,
                    children: [],
                    showChildren: false,
                    status: true
                };

                for (let language of this.$root.languages) {
                    category.translates.push({
                        name: null,
                        locale: language.locale
                    });
                }

                this.$root.scrollToNewRow(this.rows, category);
            },
            storeCategory(category) {

                let request;

                category.refreshing = true;

                let editing = false;

                if (typeof category.id === 'number') {

                    editing = true;

                    request = axios.put('/admin/categories/' + category.id + '?fast=1', category);
                } else {
                    request = axios.post('/admin/categories', category);
                }

                request.then(httpResponse => {

                    category.id = httpResponse.data.id;
                    category.href = httpResponse.data.href;

                    this.$root.notify(httpResponse.data);

                    if(editing){

                        let savedIndex = this.savedOriginal.findIndex(saved => saved.id === category.id);

                        this.$set(this.savedOriginal, savedIndex, this.$root.copy(category));

                    }else{
                        this.savedOriginal.push(this.$root.copy(category));
                    }

                }).catch(error => {
                    if (error.response) {
                        this.$root.notify(error.response.data);
                    }

                }).finally(() => {
                    category.refreshing = false;
                });
            },

        }
    }
</script>

<style scoped>

</style>