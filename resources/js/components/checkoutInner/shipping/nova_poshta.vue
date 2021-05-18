<template>
    <div id="new_poshta_component" v-if="Object.keys(trans).length">
        <div class="form__set js-field-set form__set--inline">
            <div class="form__group form__group--left">
                <label class="form__label form__label--required form__label--inline form__label--bold"
                       for="checkout-field-new_poshta-zone">{{trans.text.area}}</label>
            </div>
            <div class="form__group form__group--inBig">

                <v-select
                        :placeholder="trans.entry.area"
                        @input="getCities(selecteds.area.ref)"
                        v-model="selecteds.area"
                        label="value"
                        :close-on-select="true"
                        :options="lists.areas"
                        :searchable='false'
                        :clearable="false"
                        class="select select--special">
                </v-select>

                <div class="form__validation form__validation--inline js-validation">
                    {{trans.errors.choose_single}}
                </div>
            </div>
        </div>
        <div class="form__set js-field-set form__set--inline">
            <div class="form__group form__group--left">
                <label class="form__label form__label--required form__label--inline form__label--bold"
                       for="checkout-field-new_poshta-city">{{trans.text.city}}</label>
            </div>
            <div class="form__group form__group--inBig">

                <v-select
                        :placeholder="trans.entry.city"
                        @input="getDepartments(selecteds.city.ref)"
                        v-model="selecteds.city"
                        label="value"
                        :close-on-select="true"
                        :options="lists.cities[selecteds.area.ref]"
                        :searchable='false'
                        :clearable="false"
                        class="select select--special">
                </v-select>

                <div class="form__validation form__validation--inline js-validation">{{trans.errors.choose_sigle}}</div>
            </div>
        </div>
        <div class="form__set js-field-set form__set--inline">
            <div class="form__group form__group--left">
                <label class="form__label form__label--required form__label--inline form__label--bold"
                       for="checkout-field-new_poshta-dpt">{{trans.text.department}}</label>
            </div>
            <div class="form__group form__group--inBig">

                <v-select
                        :placeholder="trans.entry.department"
                        @input="onCheckSectionStatus"
                        v-model="selecteds.department"
                        label="value"
                        :close-on-select="true"
                        :options="lists.departments"
                        :searchable='false'
                        :clearable="false"
                        class="select select--special">
                </v-select>

                <div class="form__validation form__validation--inline js-validation">
                    {{trans.errors.choose_single}}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "nova_poshta",
        data() {
            return {
                trans: {},
                lists: {
                    areas: [],
                    cities: {},
                    departments: [],
                },
                selecteds: {
                    area: {},
                    city: {},
                    department: {}
                },
                area: {
                    name: '',
                    code: ''
                },
                city: {
                    name: '',
                    code: ''
                },
                department: {
                    name: '',
                    code: ''
                },
            }
        },
        created() {

            let self = this;

            axios.get('/component/shipping/nova_poshta/translation')
                .then((httpResponse) => {
                    self.trans = httpResponse.data;
                });

            axios.get('/component/shipping/nova_poshta/areas')
                .then((httpResponse) => {
                    self.lists.areas = httpResponse.data;
                });
        },
        updated() {

        },
        computed: {
            fields() {
                return {
                    area: this.selecteds.area,
                    city: this.selecteds.city,
                    department: this.selecteds.department
                }
            }
        },
        methods: {
            getCities(ref) {

                let self = this;

                self.selecteds.city = {};
                self.selecteds.department = {};
                this.$parent.fields_inner_status = false;

                if (!self.lists.cities.hasOwnProperty(ref)) {
                    axios.get('/component/shipping/nova_poshta/cities', {
                        params: {
                            ref: ref
                        }
                    }).then((httpResponse) => {
                        self.$set(self.lists.cities, ref, httpResponse.data);
                    });
                }
            },
            getDepartments(ref) {

                let self = this;

                self.selecteds.department = {};
                this.$parent.fields_inner_status = false;

                axios.get('/component/shipping/nova_poshta/departments', {
                    params: {
                        ref: ref
                    }
                }).then((httpResponse) => {
                    self.$set(self.lists, 'departments', httpResponse.data);
                });
            },
            onCheckSectionStatus() {

                this.$parent.fields_inner_status = true;

                this.$parent.checkSectionStatus();
            }
        }
    }
</script>

<style scoped>

</style>