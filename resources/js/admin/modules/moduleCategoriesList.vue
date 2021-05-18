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
    </div>
</template>

<script>

    export default {
        name: "moduleCategoriesList",
        props: ['moduleInfo', 'validationProp'],
        data(){
            return {
                module: {
                    decoded_setting: {

                    }
                }
            }
        },
        created(){
            this.module = this.moduleInfo;
        },
    }
</script>

<style scoped>

</style>