<template>
    <div>
        <div class="flex flex--justify-space-between">
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.name}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="text" class="input input--inForm"
                                   v-model="module.name">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="switcherStatus">
                            <div @click="module.status = false" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active switcherStatus__value--active_off': module.status === false}">
                                {{$root.translate.columns.disabled_short}}
                            </div>
                            <div @click="module.status = true" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': module.status === true}">
                                {{$root.translate.columns.enabled_short}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="">
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.menu_type}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <v-select
                                :clearable="false"
                                :searchable="false"
                                :options="types"
                                v-model="module.decoded_setting.type"
                                class="input input--inForm"
                                :reduce="type => type.type"
                                label="label">
                            <template v-slot:option="option">
                                {{ option.label }}
                            </template>
                        </v-select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        name: "moduleMenu",
        props: ['moduleInfo', 'validationProp'],
        data() {
            return {
                types: [
                    {
                        type: 'vertical',
                        label: this.$root.translate.columns.vertical,
                    },
                    {
                        type: 'horizontal',
                        label: this.$root.translate.columns.horizontal,
                    }
                ],
                module: {}
            }
        },
        created() {

            this.module = this.moduleInfo;

            if (!this.module.decoded_setting.type) this.$set(this.module.decoded_setting, 'type', 'vertical');

        },

    }
</script>

<style scoped>

</style>