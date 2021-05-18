<template>
    <div class="pagination products__pagination" v-if="countPages > 1">
        <template v-if="currentPage !== 1">
            <a class="pagination__item pagination__item--arrow pagination__item--left"
               @click.prevent="changePage(currentPage-1)"
               :href="pages[currentPage-2].link"><i class="icon sb-icon-down-arrow"></i>
            </a>
        </template>
        <template v-for="page in visiblePages">
            <template v-if="!page.separator">
                <template v-if="page.number !== currentPage">
                    <a class="pagination__item pagination__item--link"
                       @click.prevent="changePage(page.number)"
                       :href="page.link">{{page.number}}</a>
                </template>
                <template v-else>
                <span class="pagination__item pagination__item--link pagination__item--current">
                    {{currentPage}}
                </span>
                </template>
            </template>
            <template v-else>
                <span class="pagination__item pagination__item--dots">…</span>
            </template>
        </template>
        <template v-if="currentPage !== countPages">
            <a class="pagination__item pagination__item--arrow pagination__item--right"
               @click.prevent="changePage(currentPage+1)"
               :href="pages[currentPage].link"><i class="icon sb-icon-down-arrow"></i>
            </a>
        </template>
    </div>
</template>

<script>
    export default {
        name: "pagination",
        props: ['activePage', 'countPages'],
        data() {
            return {
                currentPage: ''
            }
        },
        created() {
            this.currentPage = this.activePage;
        },
        computed: {
            visiblePages() {
                let diffToStart = this.currentPage - 1;
                let diffToEnd = this.countPages - this.currentPage;

                let results = [];

                if (this.countPages > 5) {

                    if (diffToStart > 1 && diffToEnd > 1) {

                        this.setSeparator('left', results);

                        results.push(this.pages[this.currentPage - 1 - 2]);
                        results.push(this.pages[this.currentPage - 1 - 1]);
                        results.push(this.pages[this.currentPage - 1]);
                        results.push(this.pages[this.currentPage - 1 + 1]);
                        results.push(this.pages[this.currentPage - 1 + 2]);

                        this.setSeparator('right', results);
                    } else {
                        if (diffToEnd >= diffToStart) {
                            for (let i = 0; i < diffToStart; i++) {
                                results.push(this.pages[i]);
                            }

                            results.push(this.pages[this.currentPage - 1]);

                            let endPoint;

                            if (diffToEnd > (5 - results.length)) {
                                endPoint = 5 - results.length;
                            } else {
                                endPoint = diffToEnd;
                            }

                            let startPoint = results.length;
                            let steps = startPoint + endPoint;

                            for (let i = startPoint; i < steps; i++) {
                                results.push(this.pages[i]);
                            }

                            this.setSeparator('right', results);
                        } else {

                            this.setSeparator('left', results);

                            let countPagesToLeft = 4 - diffToEnd;
                            let leftPageStart = this.currentPage - countPagesToLeft;

                            for (let i = 0; i < countPagesToLeft; i++) {
                                results.push(this.pages[leftPageStart - 1 + i]);
                            }

                            results.push(this.pages[this.currentPage - 1]);

                            let steps = this.currentPage + diffToEnd;

                            for (let i = this.currentPage; i < steps; i++) {
                                results.push(this.pages[i]);
                            }
                        }
                    }
                } else {
                    for (let i = 0; i < this.countPages; i++) {
                        results.push(this.pages[i]);
                    }
                }

                return results;
            },
            pages(){
                let results = [];

                for (let number = 1; number < this.countPages + 1; number++) {
                    results.push({
                        number: number,
                        link: this.$root.makeLinkWithNewParams({page: number}),
                        viewed: false,
                        separator: false,
                    });
                }

                return results;

            }
        },
        methods: {
            changePage(number) {
                this.currentPage = number;
                this.$root.catalogFilter.page = number;

            },
            setSeparator(direction, pages) {
                switch (direction) {
                    case 'left':
                        if ((this.currentPage - 2) - 1 >= 1) {
                            pages.push(this.pages[0]);
                            pages.push({
                                separator: true
                            });
                        }
                        break;
                    case 'right':
                        if ((this.currentPage + 2) + 1 <= this.countPages) {
                            pages.push({
                                separator: true
                            });
                            pages.push(this.pages[this.countPages - 1]);
                        }
                        break;
                }
            }
        }
    };
</script>

<style scoped>

</style>