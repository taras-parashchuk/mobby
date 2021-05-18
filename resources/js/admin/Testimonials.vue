<template>


    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu['orders-callback'].items.testimonials}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="rows.length">
                <vue-good-table
                        @on-page-change="onPageChange"
                        :pagination-options="{
                            enabled: false
                        }"
                        mode="remote"
                        :columns="columns"
                        :rows="rows"
                        :totalRows="totalRecords"
                        :isLoading.sync="isLoading"
                        styleClass="table"
                        row-style-class="table__row">
                    <template slot="table-column" slot-scope="props">
                        <template v-if="props.column.label == 'actions'">
                            <span></span>
                        </template>
                        <span v-else>
                            {{props.column.label}}
                        </span>
                    </template>
                    <template slot="table-row" slot-scope="props">
                        <template v-if="props.column.field === 'link'">
                            <a :href="rows[props.index].href" v-if="rows[props.index].href" target="_blank"
                               class="text-link flex flex--align-center">
                                    <span class="mr--8">
                                    {{$root.translateWords('Follow the link')}}
                                    </span>
                                <icon class="icon" icon="foreign"></icon>
                            </a>
                        </template>
                        <template v-else-if="props.column.field === 'author'">
                            <div class="mb--10" v-if="rows[props.index].name">{{rows[props.index].name}}</div>
                            <div v-if="rows[props.index].email">{{rows[props.index].email}}</div>
                        </template>
                        <template v-else-if="props.column.field == 'status'">
                            <div class="flex flex--align-center">
                                <div class="switcherStatus">
                                    <div @click="update(props.index, false)" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': rows[props.index].status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="update(props.index, true)" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': rows[props.index].status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'text'">
                            <div>
                                <div>
                                    {{getShortText(props.index)}}
                                    <a @click.stop="showTestimonialModal(props.index)"
                                       class="text-link text-link--fz_inherit js-open-modal"
                                       v-if="getShortText(props.index).length < rows[props.index].text.length"
                                       href="javascript:void(0)">
                                        {{$root.translateWords('read more')}}
                                    </a>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="rows[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="remove(props.index)">
                                    <icon icon="delete" class="icon"></icon>
                                </a>
                            </template>
                        </template>
                    </template>
                </vue-good-table>
            </div>
            <div v-else class="listEmpty">
                <div class="listEmpty__heading">{{$root.translateWords('Your testimonials list is empty')}} :(</div>
            </div>
        </template>

        <modal name="testimonial-text" width="60%" height="auto" @closed="closeTextModal"
               :title="$root.translateWords('Full testimonial text')">
            <template v-slot>
                <p class="text">
                    {{longText}}
                </p>
            </template>
        </modal>
    </div>
</template>

<script>

    export default {
        name: "Testimonials",
        data() {
            return {
                rows: [],
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
                isLoading: false,
                columns: [
                    {
                        label: this.$root.translate.columns.testimonial_link,
                        field: 'link',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.author,
                        field: 'author',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.rating,
                        field: 'rating',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.created_at,
                        field: 'created_at',
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
                        label: this.$root.translate.columns.text,
                        field: 'text',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: 'actions',
                        field: 'actions',
                        thClass: 'table__heading table__heading--text_right',
                        tdClass: 'table__value table__value--text_right',
                        sortable: false,
                    },
                ],
                refreshing: false,
                longText: ''
            }
        },
        created() {
            let url = new URL(window.location.href);
            let page = parseInt(url.searchParams.get("page"));

            if (!page) page = 1;

            this.serverParams.page = page;

            history.pushState(null, null, '/admin/testimonials/?page=' + page);

            this.loadItems();
        },
        methods: {
            update(index, status) {

                let testimonial = this.rows[index];

                if(testimonial.status !== status){

                    testimonial.status = !testimonial.status;

                    testimonial.refreshing = true;

                    axios.put('/admin/testimonials/' + testimonial.id, {
                        status: testimonial.status
                    }).then(httpResponse => {
                        this.$root.notify(httpResponse.data);
                    }).finally(() => testimonial.refreshing = false);
                }
            },
            loadItems(params = null) {

                if (params !== null) {
                    this.serverParams.sort_column = params[0].field;
                    this.serverParams.sort_direction = params[0].type;
                }

                axios.get('/admin/testimonials', {
                    params: this.serverParams
                }).then(httpResponse => {

                    this.totalRecords = httpResponse.data.testimonials.total;

                    if (httpResponse.data.testimonials.data.length === 0 && this.totalRecords > 0) {
                        this.onPageChange({currentPage: 1});
                    } else {
                        this.serverParams.fromRecords = httpResponse.data.testimonials.from;
                        this.serverParams.toRecords = httpResponse.data.testimonials.to;

                        if (httpResponse.data.testimonials.data.length) {
                            httpResponse.data.testimonials.data.forEach(testimonial => {
                                testimonial.refreshing = false;
                            });
                        }

                        this.$set(this, 'rows', httpResponse.data.testimonials.data);
                        this.isLoaded = true;
                    }
                });
            },
            updateParams(newProps) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },
            onPageChange(params) {
                this.updateParams({page: params.currentPage});

                history.pushState(null, null, '/admin/testimonials/?page=' + params.currentPage);

                this.loadItems();
            },
            remove(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let testimonial = this.rows[index];

                    testimonial.refreshing = true;

                    axios.delete('/admin/testimonials/' + testimonial.id).then(httpResponse => {

                        this.$root.notify(httpResponse.data);

                        this.rows.splice(index, 1);

                    });
                });
            },
            showTestimonialModal(index) {
                this.longText = this.rows[index].text;

                this.$root.changePopupShowStatus('testimonial-text', true);
            },
            getShortText(index) {
                return this.rows[index].text.substring(0, 100);
            },
            closeTextModal() {
                this.longText = '';

                this.$root.changePopupShowStatus('testimonial-text', false);
            }
        }
    }
</script>

<style scoped>

</style>