<template>
    <div class="listCategoriesItem__wrapper">
        <div class="listCategoriesItem" :style="'padding-left:'+lvl*20+'px'">
            <div class="flex flex--align-center listCategoriesItem__left">
                <icon @click.native="category.showChildren = !category.showChildren"
                      class="icon listCategoriesItem__dropdown"
                      :class="{'listCategoriesItem__dropdown--reverse': category.showChildren}"
                      icon="play-button"></icon>
                <div class="listCategoriesItem__name">
                    <div v-for="translate in category.translates" class="inputWithTranslates">
                        <div class="flex flex--align-center">
                            {{$root.languages.find((language) => {return language.locale ===
                            translate.locale}).name}}:
                            <input type="text" class="input input--label_left"
                                   v-model="translate.name">
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex--align-center">
                <div class="listCategoriesItem__status">
                    <div class="switcherStatus">
                        <div @click="category.status = false" class="switcherStatus__value"
                             :class="{'switcherStatus__value--active': category.status === false}">
                            {{$root.translate.columns.disabled_short}}
                        </div>
                        <div @click="category.status = true" class="switcherStatus__value"
                             :class="{'switcherStatus__value--active': category.status === true}">
                            {{$root.translate.columns.enabled_short}}
                        </div>
                    </div>
                </div>
                <div class="listCategoriesItem__actions">
                    <template v-if="category.refreshing">
                        <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                    </template>
                    <template v-else>
                        <a v-if="isDirty" class="table__action"
                           href="javascript:void(0)" @click.stop="store(category)">
                            <icon icon="floppy-disk" class="listCategoriesItem__icon icon"></icon>
                        </a>
                        <template v-if="typeof category.id === 'number'">
                            <a class="table__action" :href="category.href" target="_blank">
                                <icon icon="foreign" class="listCategoriesItem__icon icon"></icon>
                            </a>
                            <router-link class="table__action"
                                         :to="{name:'category', params: {id: category.id}}">
                                <icon icon="pencil-edit-button" class="listCategoriesItem__icon icon"></icon>
                            </router-link>
                        </template>
                        <span class="table__action" @click.stop="remove(list, category)">
                            <icon icon="delete" class="listCategoriesItem__icon icon"></icon>
                        </span>
                        <a v-if="typeof category.id === 'number'" class="table__action">
                            <icon icon="move" class="handle listCategoriesItem__icon icon"></icon>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <draggable
                class="listCategoriesItem__children"
                v-show="category.showChildren"
                v-model="category.children"
                v-bind="dragOptions"
                @start="drag = true"
                @end="drag = false"
                @change="changeHierarchy"
                handle=".handle">
            <template v-if="category.children.length">
                <tree-category
                        :change-hierarchy="changeHierarchy"
                        :list="category.children"
                        :get-original="getOriginal"
                        :original-category="getOriginal(categoryChild.id)"
                        :remove="remove" :store="store"
                        :key="'cat-'+(lvl+1)+'-'+index"
                        v-for="(categoryChild, index) in category.children"
                        :category="categoryChild"
                        :lvl="lvl+1"></tree-category>
            </template>
            <template v-else>
                <div style="width: 200px; height: 200px;" :style="'padding-left:'+lvl*20+'px'">
                    insert here
                </div>
            </template>
        </draggable>
    </div>
</template>

<script>

    import draggable from 'vuedraggable'

    export default {
        name: "tree-category",
        props: ['changeHierarchy', 'list', 'getOriginal', 'category', 'originalCategory', 'lvl', 'remove', 'store'],
        components: {
            draggable
        },
        data() {
            return {
                drag: false,
            }
        },
        computed: {
            dragOptions() {
                return {
                    animation: 200,
                    group: "description",
                    disabled: false,
                    ghostClass: "ghost"
                };
            },
            isDirty() {

                let category = this.$root.copy(this.category);
                let original = this.$root.copy(this.originalCategory);

                delete category.refreshing;
                delete category.children;
                delete category.showChildren;

                delete original.refreshing;
                delete original.children;
                delete original.showChildren;

                return JSON.stringify(category) !== JSON.stringify(original);
            }
        }
    }
</script>

<style scoped>

</style>