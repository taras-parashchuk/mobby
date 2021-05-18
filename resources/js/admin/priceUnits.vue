<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.localisation.items['product-price-unit']}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="priceUnits.length">
                <vue-good-table
                        :columns="columns"
                        :rows="priceUnits"
                        styleClass="table"
                        row-style-class="table__row">
                    <template slot="table-row" slot-scope="props">

                        <template v-if="props.column.field === 'name'">
                            <div v-for="translate in priceUnits[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    {{$root.languages.find((language) => {return language.locale ===
                                    translate.locale}).name}}:
                                    <input type="text" class="input input--label_left"
                                           v-model="translate.name">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'display'">
                            <div class="flex flex--align-center">
                                <div class="switcher">
                                    <check @click.native="priceUnits[props.index].display = !priceUnits[props.index].display" :checked="priceUnits[props.index].display === true"
                                           class="switcher__icon"></check>
                                    <span @click="priceUnits[props.index].display = !priceUnits[props.index].display"
                                          class="switcher__label">{{$root.translate.columns.yes}}</span>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'status'">
                            <div class="flex flex--align-center">
                                <div class="switcherStatus">
                                    <div @click="priceUnits[props.index].status = false" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active switcherStatus__value--active_off': priceUnits[props.index].status === false}">
                                        {{$root.translate.columns.disabled_short}}
                                    </div>
                                    <div @click="priceUnits[props.index].status = true" class="switcherStatus__value"
                                         :class="{'switcherStatus__value--active': priceUnits[props.index].status === true}">
                                        {{$root.translate.columns.enabled_short}}
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="priceUnits[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-if="isChangedRow(priceUnits[props.index].id)" class="table__action"
                                   href="javascript:void(0)" @click.stop="storePriceUnit(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="deletePriceUnit(props.index)">
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
            <div v-else class="listEmpty">
                <div class="listEmpty__heading">{{$root.translateWords('Your price units list is empty')}} :(</div>
                <div class="listEmpty__text">
                    {{$root.translateWords('You may add them')}}
                    <a class="listEmpty__link" href="javascript:void(0)" @click.stop="addPriceUnit">{{$root.translateWords('manually')}}</a>
                </div>
            </div>
        </template>

        <widget-actions add="addPriceUnit" :trans="{add: $root.translateWords('Create price unit')}"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "PriceUnits",
        data() {
            return {
                priceUnits: [],
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
                        label: this.$root.translate.columns['showing-on-site'],
                        field: 'display',
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
                    },
                ],
            }
        },
        created() {
            axios.get('/admin/price-units/').then(
                httpResponse => {
                    httpResponse.data.price_units.forEach(priceUnit => {
                        priceUnit.refreshing = false;

                        this.priceUnits.push(priceUnit);
                    });

                    this.$set(this, 'savedOriginal', this.$root.copy(this.priceUnits));

                    this.isLoaded = true;
                });
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

                    let currentPosition = this.priceUnits.findIndex(item => {
                        return item.id === id
                    });

                    let current = this.$root.copy(this.priceUnits[currentPosition]);

                    delete current.refreshing;

                    let saved = this.$root.copy(this.savedOriginal[originalPosition]);

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        methods: {
            addPriceUnit() {
                let priceUnit = {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    status: true,
                    display: false,
                    translates: [],
                    refreshing: false
                };

                for (let language of this.$root.languages) {
                    priceUnit.translates.push({
                        locale: language.locale,
                        title: ''
                    });
                }

                this.$root.scrollToNewRow(this.priceUnits, priceUnit);

            },
            deletePriceUnit(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let priceUnit = this.priceUnits[index];

                    priceUnit.refreshing = true;

                    let priceUnit_id = priceUnit.id;

                    let originalPosition = this.savedOriginal.findIndex(priceUnit => {
                        return priceUnit.id === priceUnit_id
                    });

                    if (typeof priceUnit.id === 'number') {
                        axios.delete('/admin/price-units/' + priceUnit.id).then(
                            httpResponse => {

                                this.$root.notify(httpResponse.data);

                                this.priceUnits.splice(index, 1);

                                this.savedOriginal.splice(originalPosition, 1);
                            }
                        )
                    }else{
                        this.priceUnits.splice(index, 1);
                    }
                });
            },
            storePriceUnit(index) {
                let priceUnit = this.priceUnits[index];
                let request;

                priceUnit.refreshing = true;

                let priceUnit_id = priceUnit.id;

                let originalPosition = this.savedOriginal.findIndex(priceUnit => {
                    return priceUnit.id === priceUnit_id
                });

                if (typeof priceUnit.id === 'number') {
                    request = axios.put('/admin/price-units/' + priceUnit.id, priceUnit);
                } else {
                    request = axios.post('/admin/price-units', priceUnit);
                }

                request.then(httpResponse => {

                    this.$root.notify(httpResponse.data);

                    priceUnit.id = httpResponse.data.id;

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(priceUnit));
                    } else {
                        this.savedOriginal.push(this.$root.copy(priceUnit));
                    }

                }).catch(error => {
                    if(error.response) this.$root.notify(error.response.data);

                }).finally(() => {
                    priceUnit.refreshing = false;
                });
            }
        }

    }
</script>

<style scoped>

</style>