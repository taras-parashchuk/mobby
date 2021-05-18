<template>
    <div>
        <div class="flex">
            <div class="pagination">
                <div class="pagination__control" @click="goTo('first')" :class="{'pagination__control--hidden': isFirst}">
                    <icon class="icon pagination__icon pagination__icon--reverse" icon="play-and-pause-button"></icon>
                </div>
                <div class="pagination__control" @click="goTo('prev')" :class="{'pagination__control--hidden': !hasPrev}">
                    <icon class="icon pagination__icon" icon="left-arrow"></icon>
                </div>
                <div class="pagination__numbers">
                    <div class="pagination__number" @click="goTo(page)" v-for="page in getPages" :class="{'pagination__number--active': page === currentPage}">{{page}}</div>
                </div>
                <div class="pagination__control" @click="goTo('next')" :class="{'pagination__control--hidden': !hasNext}">
                    <icon class="icon pagination__icon pagination__icon--reverse" icon="left-arrow"></icon>
                </div>
                <div class="pagination__control" @click="goTo('last')" :class="{'pagination__control--hidden': isLast}">
                    <icon class="icon pagination__icon" icon="play-and-pause-button"></icon>
                </div>
            </div>
        </div>
        <div class="flex mt--18 color--6c759c fz--16">
            {{fromRecords}} - {{toRecords}} {{$root.translateWords('Total of items')}}: {{total}}
        </div>
    </div>
</template>

<script>
    export default {
        name: "paginationComponent",
        props: [
            'total',
            'page-changed',
            'per-page-changed',
            'per-page',
            'current-page',
            'from-records',
            'to-records'
        ],
        computed: {
            hasNext() {
                return this.currentPage * this.perPage < this.total;
            },
            hasPrev() {
                return this.currentPage > 1;
            },
            totalPages() {
                return Math.ceil(this.total / this.perPage);
            },
            isLast() {
                return this.currentPage === this.totalPages;
            },
            isFirst() {
                return this.currentPage === 1;
            },
            getPages(){
                let i = 1;
                let freePagesCount = this.totalPages - 1;
                let pages = [];
                let direction = 1;
                let maxCountPages = 7;

                pages.push(this.currentPage);

                while (freePagesCount > 0 && pages.length < maxCountPages){

                    let maybePage = this.currentPage + i*direction;

                    if(maybePage > 0 && maybePage < this.totalPages + 1){
                        pages.push(maybePage);
                        freePagesCount--;
                    }

                    if(freePagesCount > 0){
                        maybePage = this.currentPage + i*(-direction);

                        if(maybePage > 0 && maybePage < this.totalPages + 1){
                            pages.push(maybePage);
                            freePagesCount--;
                        }
                    }

                    i++;
                }

                return pages.sort(function(a, b){return a-b});

            }
        },
        methods: {
            goTo(param) {
                switch (param) {
                    case 'next':
                        this.pageChanged({currentPage: this.currentPage + 1});
                        break;
                    case 'prev':
                        this.pageChanged({currentPage: this.currentPage - 1});
                        break;
                    case 'first':
                        this.pageChanged({currentPage: 1});
                        break;
                    case 'last':
                        this.pageChanged({currentPage: this.totalPages});
                        break;
                    default:
                        this.pageChanged({currentPage: param});
                        break;
                }
            }
        }
    }
</script>