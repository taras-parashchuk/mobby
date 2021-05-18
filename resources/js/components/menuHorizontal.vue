<template>
    <menu class="mainMenu">
        <div class="container">
            <ul class="mainMenu__nav">
                <li class="mainMenu__item" v-for="category in children(null)">
                    <a :href="category.href" class="mainMenu__link">
                        {{category.translate.name}}
                    </a>
                    <template v-if="$root.theme === 'beauty'">
                        <div class="mainMenu__submenu submenu" v-if="children(category.id).length">
                            <div class="submenu__outer">
                                <div class="submenu__left">
                                    <div class="submenu__column" v-for="category_2 in children(category.id)">
                                        <a :href="category_2.href" class="submenu__title">
                                            {{category_2.translate.name}}
                                        </a>
                                        <template v-if="children(category_2.id).length">
                                            <div class="submenu__item" v-for="category_3 in children(category_2.id)">
                                                <a :href="category_3.href" class="submenu__link">
                                                    {{category_3.translate.name}}
                                                </a>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="submenu__right">
                                    <img src="" alt="">
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mainMenu__submenu submenu" v-if="children(category.id).length">
                            <div class="submenu__outer">
                                <div class="submenu__left">
                                    <div class="submenu__column" v-for="category_2_group in chunk(children(category.id), chunkDelim(children(category.id).length))">
                                        <div v-for="category_2 in category_2_group">
                                            <a :href="category_2.href" class="submenu__title">
                                                {{category_2.translate.name}}
                                            </a>
                                            <template v-if="children(category_2.id).length">
                                                <div class="submenu__item" v-for="category_3 in children(category_2.id)">
                                                    <a :href="category_3.href" class="submenu__link">
                                                        {{category_3.translate.name}}
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </li>
            </ul>
        </div>
    </menu>
</template>

<script>
    export default {
        name: "menuHorizontal",
        props: ['config', 'isHome', 'propsCategories', 'propsGroupsCategories'],
        data() {
            return {
                categories: []
            };
        },
        created() {
            this.categories = this.propsCategories;

            this.$set(this.$root, 'menuCategoriesGroups', this.propsGroupsCategories);
        },
        mounted() {

        },
        methods: {
            children(categoryId) {
                return this.categories.filter(category => {
                    return category.parent_id === categoryId;
                });
            },
            chunk(array, chunkSize) {
                return [].concat.apply([],
                    array.map(function (elem, i) {
                        return i % chunkSize ? [] : [array.slice(i, i + chunkSize)];
                    })
                );
            },
            chunkDelim(countChildren) {
                if (countChildren > 20) {
                    return Math.ceil(countChildren / 4);
                } else if (countChildren > 10) {
                    return Math.ceil(countChildren / 3);
                } else {
                    return Math.ceil(countChildren / 2);
                }
            }
        }
    }
</script>

<style scoped>

</style>