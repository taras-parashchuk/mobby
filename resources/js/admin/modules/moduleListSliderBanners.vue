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
                    {{$root.translate.columns.banner}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <v-select
                                :clearable="false"
                                :searchable="false"
                                :options="banners"
                                v-model="module.decoded_setting.banner_id"
                                class="input input--inForm"
                                :reduce="banner => banner.id"
                                label="id">
                            <template slot="selected-option" slot-scope="option">
                                {{ option.name }}
                            </template>
                            <template v-slot:option="option">
                                {{ option.name }}
                            </template>
                        </v-select>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup singleFormGroup--inlineSet">
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.width}}(px):
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.width">
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.height}}(px):
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.height">
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup singleFormGroup--inlineSet">
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.count_columns}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.cols">
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.offset_items}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.offset">
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.autostart}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.autoplay = true" :checked="module.decoded_setting.autoplay === true"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.autoplay = true"
                                  class="switcher__label">{{$root.translate.columns.yes}}</span>
                        </div>
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.autoplay = false"
                                   :checked="module.decoded_setting.autoplay === false"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.autoplay = false" class="switcher__label">{{$root.translate.columns.no}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup" v-if="module.decoded_setting.autoplay">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Autostart after time in ms(1000ms = 1s)')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <input type="number" class="input input--inForm"
                               v-model.number="module.decoded_setting.autoplayTimeout">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Speed flipping in ms(1000ms = 1s)')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <input type="number" class="input input--inForm"
                               v-model.number="module.decoded_setting.smartSpeed">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Show navigation?')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.nav = true" :checked="module.decoded_setting.nav === true"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.nav = true"
                                  class="switcher__label">{{$root.translate.columns.yes}}</span>
                        </div>
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.nav = false"
                                   :checked="module.decoded_setting.nav === false"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.nav = false" class="switcher__label">{{$root.translate.columns.no}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Show pagination?')}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.pagination = true" :checked="module.decoded_setting.pagination === true"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.pagination = true"
                                  class="switcher__label">{{$root.translate.columns.yes}}</span>
                        </div>
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.pagination = false"
                                   :checked="module.decoded_setting.pagination === false"
                                   class="switcher__icon"></check>
                            <span @click="module.decoded_setting.pagination = false" class="switcher__label">{{$root.translate.columns.no}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>

    import { ModelSelect, ModelListSelect } from 'vue-search-select'

    import {mdbBtn, mdbInput, mdbAlert} from 'mdbvue'

    export default {
        name: "moduleListSliderBanners",
        props: ['moduleInfo', 'validationProp', 'banners'],
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

            if(!this.module.id){
                this.$set(this.module.decoded_setting, 'pagination', false);
                this.$set(this.module.decoded_setting, 'nav', false);
                this.$set(this.module.decoded_setting, 'autoplay', false);
            }

        },
    }
</script>

<style scoped>

</style>