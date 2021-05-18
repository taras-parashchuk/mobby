<template>
    <div class="table-list-container">

        <h2 class="mainContent__heading">{{$root.translate.menu.localisation.items.locations}}</h2>

        <template v-if="isLoaded">
            <div class="listData" v-if="locations.length">
                <vue-good-table
                        :columns="columns"
                        :rows="locations"
                        styleClass="table"
                        row-style-class="table__row">
                    <template slot="table-row" slot-scope="props">

                        <template v-if="props.column.field === 'name'">
                            <div v-for="translate in locations[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    <input type="text" class="input"
                                           v-model="translate.name">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'address'">
                            <div v-for="translate in locations[props.index].translates" class="inputWithTranslates">
                                <div class="flex flex--align-center">
                                    <input type="text" class="input"
                                           v-model="translate.address">
                                </div>
                            </div>
                        </template>
                        <template v-else-if="props.column.field === 'actions'">
                            <template v-if="locations[props.index].refreshing">
                                <font-awesome-icon class="text-warning" icon="circle-notch" spin></font-awesome-icon>
                            </template>
                            <template v-else>
                                <a v-if="isChangedRow(locations[props.index].id)" class="table__action"
                                   href="javascript:void(0)" @click.stop="store(props.index)">
                                    <icon icon="floppy-disk" class="icon"></icon>
                                </a>
                                <template v-if="typeof locations[props.index].id === 'number'">
                                    <router-link class="table__action"
                                                 :to="{name:'location', params: {id: locations[props.index].id}}">
                                        <icon icon="pencil-edit-button" class="icon"></icon>
                                    </router-link>
                                </template>
                                <a v-tooltip.top-start="'You have new messages.'" class="table__action"
                                   href="javascript:void(0)" @click.stop="destroy(props.index)">
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

        <widget-actions add="add" :trans="{add: $root.translateWords('Create location')}"></widget-actions>
    </div>
</template>

<script>

    export default {
        name: "Locations",
        data() {
            return {
                locations: [],
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
                        label: this.$root.translate.columns.address,
                        field: 'address',
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
            axios.get('/admin/locations/').then(httpResponse => {
                httpResponse.data.locations.forEach(location => {

                    location.refreshing = false;

                    this.locations.push(location);
                });

                this.$set(this, 'savedOriginal', this.$root.copy(this.locations));

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

                    let currentPosition = this.locations.findIndex(item => {
                        return item.id === id
                    });

                    let current = this.$root.copy(this.locations[currentPosition]);

                    delete current.refreshing;

                    let saved = this.$root.copy(this.savedOriginal[originalPosition]);

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        methods: {
            add() {

                let translates = [];

                for (let language of this.$root.languages) {
                    translates.push({
                        locale: language.locale,
                        name: '',
                        address: '',
                    });
                }

                this.$root.scrollToNewRow(this.locations, {
                    id: 'tmp-' + Math.random(100000, 10000000),
                    translates: translates,
                    refreshing: false
                });

            },
            store(index) {
                let request;

                let location = this.locations[index];

                location.refreshing = true;

                let location_id = location.id;

                let originalPosition = this.savedOriginal.findIndex(location => {
                    return location.id === location_id
                });

                if (typeof location.id === 'number') {
                    request = axios.put('/admin/locations/' + this.locations[index].id, this.locations[index]);
                } else {
                    request = axios.post('/admin/locations', this.locations[index]);
                }

                request.then(httpResponse => {

                    this.$root.notify(httpResponse.data);

                    this.locations[index].id = httpResponse.data.id;

                    if (originalPosition !== -1) {
                        this.$set(this.savedOriginal, originalPosition, this.$root.copy(location));
                    } else {
                        this.savedOriginal.push(this.$root.copy(location));
                    }

                }).catch(error => {
                    if (error.response) {
                        this.$root.notify(error.response.data);
                    }
                }).finally(() => {
                    location.refreshing = false;
                });
            },
            destroy(index) {
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    let location = this.locations[index];

                    location.refreshing = true;

                    let location_id = location.id;

                    let originalPosition = this.savedOriginal.findIndex(location => {
                        return location.id === location_id
                    });

                    if (typeof location.id === 'number') {
                        axios.delete('/admin/locations/' + location.id).then(
                            httpResponse => {
                                this.$root.notify(httpResponse.data);

                                this.savedOriginal.splice(originalPosition, 1);

                                this.locations.splice(index, 1);
                            }
                        )
                    } else {
                        this.locations.splice(index, 1);
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>