<template>
    <div>
        <template v-for="(option, o_lvl) in options">
            <div class="option required">
                <div class="option__header">{{option.translate.name}}:</div>
                <div class="choice js-update-price-option" v-for="value in option.values" v-if="value.visible">
                    <template v-if="$root.theme === 'beauty'">
                        <input type="radio"
                               :name="'option['+option.id+']'"
                               :id="'option-value-'+value.id"
                               :value="value.id"
                               v-model="selectedValues[option.id]"
                               @change="getOptions(o_lvl+1)"
                               class="choice__value"
                        />
                        <template v-if="value.prefer_style !== null">
                            <label :for="'option-value-'+value.id" v-if="value.prefer_style === 'image'"
                                   class="choice__label choice__label--withImg">
                                <img :src="value.image"
                                     :alt="value.translate.value" class="img-thumbnail">
                            </label>
                            <label v-else :for="'option-value-'+value.id"
                                   class="choice__label choice__label--withImg" :style="'background-color:'+value.color"
                                   :title="value.translate.value">
                            </label>
                        </template>
                        <template v-else>
                            <label :for="'option-value-'+value.id" class="choice__label choice__label--rectangle">
                                {{value.translate.value}}
                            </label>
                        </template>
                    </template>
                    <template v-else>
                        <input type="radio"
                               :name="'option['+option.id+']'"
                               :id="'option-value-'+value.id"
                               :value="value.id"
                               v-model="selectedValues[option.id]"
                               @change="getOptions(o_lvl+1)"
                               class="choice__value"
                        />
                        <label :for="'option-value-'+value.id" class="choice__label choice__label--ellipse">
                            {{value.translate.value}}
                        </label>
                    </template>
                </div>
            </div>
            <template v-if="Object(option).hasOwnProperty('helper')">
                <a href="javascript:void(0)"
                   @click.stop="openHelper(option.id)"
                   class="option__helper optionHelper">
                    <i class="icon sb-icon-school-rule"></i>
                    <span class="optionHelper__title">{{option.helper.name}}</span>
                </a>
                <div class="l-d-none">
                    <div :id="'attribute-' + option.id + 'helper-content'">
                        <div class="helperContent popup" v-html="option.helper.content"></div>
                    </div>
                </div>
            </template>
        </template>
    </div>
</template>

<script>

    let helpers = require('./../common');

    import {copy} from '../app'

    export default {
        name: "variations",
        props: ['input-options', 'mainProductId'],
        created() {
            let self = this;

            this.options = this.inputOptions;

            for (let i in this.options[0].values) {
                self.$set(self.options[0].values[i], 'visible', true);
            }

            this.$set(this.selectedValues, this.options[0].id, helpers.findObjectByKey(this.options[0].values, 'selected', true).id);

            this.getOptions(1, true);
        },
        data() {
            return {
                selectedValues: {},
                options: []
            }
        },
        computed: {
            readyNewVariant() {
                return Object.keys(this.selectedValues).length === this.options.length;
            }
        },
        methods: {
            getOptions(rowIndex, init = false, oldSelectedValues = {}) {

                if (!Object.keys(oldSelectedValues).length) {
                    oldSelectedValues = copy(this.selectedValues);
                }

                this.clearValues(rowIndex, init);

                if (this.options.length > rowIndex) {
                    axios.get('/products/' + this.mainProductId + '/attributes/' + this.options[rowIndex].id + '/get-variant-params', {params: this.selectedValues})
                        .then(httpResponse => {

                            let option = this.options[rowIndex];

                            for (let i in option.values) {
                                if (httpResponse.data.indexOf(option.values[i].id) !== -1) {
                                    this.$set(option.values[i], 'visible', true);
                                } else {
                                    this.$set(option.values[i], 'visible', false);
                                }
                            }

                            this.setSelectedValue(option, oldSelectedValues);

                            this.getOptions(rowIndex + 1, init, oldSelectedValues);

                        });
                } else if (!init && this.readyNewVariant) {
                    this.getVariantInfo();
                }
            },
            setSelectedValue(option, oldSelectedValues) {

                let valuesIds = [], userSelectedOption, userSelectedOptionValueId;

                for (let valueIndex in option.values) {
                    if (option.values[valueIndex].visible) valuesIds.push(option.values[valueIndex].id);
                }

                if (userSelectedOption = helpers.findObjectByKey(option.values, 'selected', true)) {
                    userSelectedOptionValueId = userSelectedOption.id;
                }

                let selectedValueId;

                if (userSelectedOptionValueId !== undefined) {
                    selectedValueId = userSelectedOptionValueId;
                } else if (valuesIds.indexOf(oldSelectedValues[option.id]) !== -1) {
                    selectedValueId = oldSelectedValues[option.id];
                } else {
                    for (let valueIndex in option.values) {
                        if (option.values[valueIndex].visible) {
                            selectedValueId = option.values[valueIndex].id;
                            break;
                        }
                    }
                }

                this.$set(this.selectedValues, option.id, selectedValueId);

            },

            clearValues(index, init) {
                this.options.forEach((option, o_index) => {
                    if (o_index >= index) {

                        this.$delete(this.selectedValues, option.id);

                        if (!init) {
                            for (let valueIndex in option.values) {
                                option.values[valueIndex].selected = false;
                            }
                        }
                    }
                });
            },
            getVariantInfo() {
                axios.get('/products/' + this.mainProductId + '/get-variant-info', {params: this.selectedValues})
                    .then(httpResponse => {
                        let productData = httpResponse.data;

                        //window.history.pushState(productData.translate.name, productData.translate.meta_title, productData.href);

                        //this.$root.$refs.productTitle.innerText = productData.translate.name;

                        window.location.href = productData.href;
                    });
            },
            openHelper(id){
                $.magnificPopup.open({
                    items: {
                        src: $('#attribute-' + id + 'helper-content').html(),
                        type: 'inline'
                    }
                });
            }
        },
    }
</script>

<style scoped>

</style>