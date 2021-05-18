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
        <div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Show title?')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.show_title = true"
                                   :checked="module.decoded_setting.show_title === true"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.show_title = true"
                                  class="switcher__label">{{$root.translate.columns.yes}}</span>
                        </div>
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.show_title = false"
                                   :checked="module.decoded_setting.show_title === false"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.show_title = false" class="switcher__label">{{$root.translate.columns.no}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup" v-if="module.decoded_setting.show_title">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.name}}:
                </div>
                <div class="singleFormGroup__field inputWithTranslates" v-for="language in $root.languages">
                    <div class="flex flex--align-center">
                        {{language.name}}:
                        <input type="text" class="input input--inForm input--label_left"
                               v-model="module.decoded_setting.title[language.locale]">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Visible count of items')}}:
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <input type="text" class="input input--inForm"
                               v-model="module.decoded_setting.limit">
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>

    import {ModelSelect, ModelListSelect, MultiListSelect} from 'vue-search-select'

    import {mdbBtn, mdbInput, mdbAlert} from 'mdbvue'

    export default {
        name: "moduleTestimonials",
        props: ['moduleInfo', 'validationProp', 'banners'],
        data() {
            return {
                module: {
                    decoded_setting: {}
                },
            }
        },
        created() {

            this.module = this.moduleInfo;

            if (!Object(this.module.decoded_setting).hasOwnProperty('title')) {

                this.$set(this.module.decoded_setting, 'title', {});

                this.$root.languages.forEach((language) => {
                    let locale = language.locale;

                    this.$set(this.module.decoded_setting.title, locale, '');

                });
            }

            if(!this.module.id){
                this.$set(this.module.decoded_setting, 'show_title', false);
            }

        }
    }
</script>

<style scoped>

</style>