<template>
    <div v-if="activeLoader" :class="getClass"
         @click="loadMore()">
        <template v-if="$root.theme === 'beauty'">
            <span class="loadMore__text">{{$root.trans['catalog.text.load_more']}}</span>
            <i class="loadMore__icon icon sb-icon-refresh-button"></i>
        </template>
        <template v-else>
            <i class="loadMore__icon icon sb-icon-refresh-button"></i>
            <span class="loadMore__text">{{$root.trans['catalog.text.load_more']}}</span>
        </template>
    </div>
</template>

<script>

    import {copy, helpers} from '../app'

    export default {
        name: "morePagesLoader",
        props: ['view', 'apiLink', 'pageContainer', 'activePage'],
        data() {
            return {
                loadedPageNumber: ''
            };
        },
        created() {
            this.loadedPageNumber = this.activePage;
        },
        computed: {
            activeLoader() {
                return (this.loadedPageNumber + 1 <= this.$root.paginationInfo['countPages']);
            },
            getClass(){
                let class_name = 'loadMore loadMore--' + this.view;

                if(this.$root.theme === 'beauty'){

                }else{
                    class_name += (' product product--more product-' + this.view);
                }

                return class_name;
            }
        },
        methods: {
            loadMore() {
                let self = this;

                if (this.activeLoader) {

                    let paramsForLoadMore = copy(self.$root.catalogFilter);

                    paramsForLoadMore.page = self.loadedPageNumber + 1;

                    if(!Object.keys(paramsForLoadMore.attributes).length){
                        paramsForLoadMore.attributes = null;
                    }

                    $.get(this.apiLink, paramsForLoadMore, function(response){
                        let newDataContainer = response.products.data;

                        for (let el of newDataContainer) {
                            self.$root[self.pageContainer].push(el);
                        }

                        self.loadedPageNumber++;
                    });
                }
            }
        },
        watch: {
            activePage(new_page_number) {
                this.loadedPageNumber = new_page_number;
            }
        }
    }
</script>

<style scoped>

</style>