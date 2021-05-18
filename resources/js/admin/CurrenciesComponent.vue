<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.localisation.items.currencies}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="currencies.length">
                <vue-good-table
                        :columns="columns"
                        :rows="currencies"
                        styleClass="table"
                        row-style-class="table__row">
                    <template slot="table-row" slot-scope="props">

                        <template v-if="props.column.field === 'name'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input"
                                       v-model="currencies[props.index].name">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'code'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--code"
                                       v-model="currencies[props.index].code">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'symbol'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--symbol"
                                       v-model="currencies[props.index].symbol">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'format'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input"
                                       v-model="currencies[props.index].format">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'exchange_rate'">
                            <div class="flex flex--align-center">
                                <input type="text" class="input"
                                       v-model="currencies[props.index].exchange_rate">
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'active'">
                            <div class="flex flex--align-center">
                                <div class="switcherStatus">
                                    <div @click="currencies[props.index].active = 0" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': currencies[props.index].active === 0}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="currencies[props.index].active = 1" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': currencies[props.index].active === 1}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="currencies[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-if="isChangedRow(currencies[props.index].id)" class="table__action"
                                   href="javascript:void(0)" @click.stop="storeCurrency(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="deleteCurrency(props.index)">
                                    <icon icon="delete" class="icon"></icon>
                                </a>
                            </template>
                        </template>
                        <span v-else>
                        {{props.formattedRow[props.column.field]}}
                    </span>
                    </template>

                </vue-good-table>

            </div>
        </template>

        <widget-actions add="addCurrency" :trans="{add: $root.translateWords('Create currency')}"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "CurrenciesComponent",
        data() {
            return {
                currencies: [],
                savedOriginal: [],
                refreshing: false,
                isLoaded: false,
                columns: [
                    {
                        label: this.$root.translate.columns.name,
                        field: 'name',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.code,
                        field: 'code',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.symbol,
                        field: 'symbol',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.format,
                        field: 'format',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.rate,
                        field: 'exchange_rate',
                        thClass: 'table__heading',
                        tdClass: 'table__value',
                        sortable: false,
                    },
                    {
                        label: this.$root.translate.columns.status,
                        field: 'active',
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
                    },
                ],
            }
        },
        created() {
            axios.get('/admin/currencies/').then(
                httpResponse => {
                    httpResponse.data.currencies.forEach(currency => {
                        currency.refreshing = false;

                        currency.id = parseInt(currency.id);
                        currency.active = parseInt(currency.active);

                        this.currencies.push(currency);
                    });

                    this.$set(this, 'savedOriginal', this.$root.copy(this.currencies));

                    this.isLoaded = true;
                }
            )
        },
        computed: {
            isChangedRow() {
                return (id) => {
                    let originalPosition = this.savedOriginal.findIndex(item => {
                        return item.id === id
                    });

                    if (originalPosition === -1) {
                        return true;
                    }

                    let currentPosition = this.currencies.findIndex(item => {
                        return item.id === id
                    });

                    let current = this.$root.copy(this.currencies[currentPosition]);

                    delete current.refreshing;

                    let saved = this.$root.copy(this.savedOriginal[originalPosition]);

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        methods: {
            addCurrency() {
                this.$root.scrollToNewRow(this.currencies, {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    name: null,
                    code: null,
                    symbol: null,
                    format: null,
                    exchange_rate: null,
                    active: 0,
                    refreshing: false
                });
            },
            storeCurrency(index) {
                let currency = this.currencies[index];

                let request;

                currency.refreshing = true;

                let currency_id = currency.id;

                let originalPosition = this.savedOriginal.findIndex(currency => {
                    return currency.id === currency_id
                });

                if (typeof currency.id === 'number') {
                    request = axios.put('/admin/currencies/' + currency.id, currency);
                } else {
                    request = axios.post('/admin/currencies', currency);
                }

                request.then(httpResponse => {

                    currency.id =  httpResponse.data.id;

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(currency));
                    } else {
                        this.savedOriginal.push(this.$root.copy(currency));
                    }

                    this.$root.notify(httpResponse.data);

                }).catch(error => {
                    if (error.response){
                        this.$root.notify(error.response.data);
                    }
                }).finally(() => {
                    currency.refreshing = false;
                });
            },
            deleteCurrency(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let currency = this.currencies[index];

                    currency.refreshing = true;

                    let code = currency.code;

                    let currency_id = currency.id;

                    let originalPosition = this.savedOriginal.findIndex(currency => {
                        return currency.id === currency_id
                    });

                    if (typeof currency.id === 'number') {
                        axios.delete('/admin/currencies/' + code).then(httpResponse => {

                            this.$root.notify(httpResponse.data);

                            this.currencies.splice(index, 1);

                            this.savedOriginal.splice(originalPosition, 1);

                        }).catch(error => {
                            if (error.response){
                                this.$root.notify(error.response.data);
                            }
                        }).finally(() => currency.refreshing = false);
                    }else{
                        this.currencies.splice(index, 1);
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>