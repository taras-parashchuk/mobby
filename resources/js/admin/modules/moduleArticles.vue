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
                    {{$root.translate.columns.title_for}} desktop:
                </div>
                <div class="singleFormGroup__field inputWithTranslates" v-for="language in $root.languages">
                    <div class="flex flex--align-center">
                        {{language.name}}:
                        <input type="text" class="input input--inForm input--label_left"
                               v-model="module.decoded_setting['title-lg'][language.locale]">
                    </div>
                </div>
            </div>

            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.title_for}} mobile:
                </div>
                <div class="singleFormGroup__field inputWithTranslates" v-for="language in $root.languages">
                    <div class="flex flex--align-center">
                        {{language.name}}:
                        <input type="text" class="input input--inForm input--label_left"
                               v-model="module.decoded_setting['title-xs'][language.locale]">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Article assortment')}}:
                </div>
                <div class="singleFormGroup__field">
                    <v-select
                            :clearable="false"
                            :searchable="false"
                            :options="types"
                            v-model="module.decoded_setting.type"
                            class="input input--inForm"
                            :reduce="type => type.value"
                            label="value">
                        <template slot="selected-option" slot-scope="option">
                            {{ option.name }}
                        </template>
                        <template v-slot:option="option">
                            {{ option.name }}
                        </template>
                    </v-select>
                </div>
            </div>
            <div class="singleFormGroup" v-if="module.decoded_setting.type === 'custom'">
                <div class="singleFormGroup__field">
                    <v-select
                            @search="onSearchArticles"
                            :components="{Deselect, OpenIndicator}"
                            :multiple="true"
                            :clearable="true"
                            :searchable="true"
                            :options="articles"
                            v-model="module.decoded_setting.article"
                            class="input input--inForm vs--multiply"
                            label="name">
                        <template #search="{attributes, events}">
                            <input
                                    class="vs__search"
                                    :placeholder="$root.translateWords('Add article')"
                                    v-bind="attributes"
                                    v-on="events"
                            />
                        </template>
                        <div slot="no-options">{{$root.translateWords('Data not found')}}</div>
                    </v-select>
                </div>
            </div>
            <div class="singleFormGroup singleFormGroup--inlineSet">
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translateWords('Maximum count of items')}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.limit">
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translateWords('Visible count of items')}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="number" class="input"
                                   v-model.number="module.decoded_setting.visible">
                        </div>
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
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.autostart}}:
                    <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <div class="switcher">
                            <check @click.native="module.decoded_setting.autoplay = true"
                                   :checked="module.decoded_setting.autoplay === true"
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
                            <check @click.native="module.decoded_setting.nav = true"
                                   :checked="module.decoded_setting.nav === true"
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
                            <check @click.native="module.decoded_setting.pagination = true"
                                   :checked="module.decoded_setting.pagination === true"
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

    export default {
        name: "moduleArticles",
        props: ['moduleInfo', 'validationProp', 'Deselect', 'OpenIndicator', 'articles', 'onSearchArticles'],
        data() {
            return {
                module: {
                    decoded_setting: {}
                },
                types: [
                    {
                        value: 'custom',
                        name: this.$root.translate.columns.assortment.custom
                    },
                    {
                        value: 'auto',
                        name: this.$root.translate.columns.assortment.system_auto
                    }
                ],
            }
        },
        created() {

            this.module = this.moduleInfo;

            if (!Object(this.module.decoded_setting).hasOwnProperty('title-xs')) {

                this.$set(this.module.decoded_setting, 'title-xs', {});

                this.$root.languages.forEach((language) => {

                    let locale = language.locale;

                    this.$set(this.module.decoded_setting['title-xs'], locale, '');

                });
            }

            if (!Object(this.module.decoded_setting).hasOwnProperty('title-lg')) {

                this.$set(this.module.decoded_setting, 'title-lg', {});

                this.$root.languages.forEach((language) => {

                    let locale = language.locale;

                    this.$set(this.module.decoded_setting['title-lg'], locale, '');

                });
            }

            if (!Object(this.module.decoded_setting).hasOwnProperty('type')) {

                this.$set(this.module.decoded_setting, 'type', 'custom');

                this.$set(this.module.decoded_setting, 'article', []);

            }

            if(!this.module.id){
                this.$set(this.module.decoded_setting, 'pagination', false);
                this.$set(this.module.decoded_setting, 'nav', false);
                this.$set(this.module.decoded_setting, 'autoplay', false);
            }

        },
        watch: {
            'module.decoded_setting.type': function (newVal) {
                switch (newVal) {
                    case 'custom':
                        if (!Array.isArray(this.module.decoded_setting.article)) {
                            this.$set(this.module.decoded_setting, 'article', []);
                        }
                        break;
                    case 'auto':
                        if (typeof this.module.decoded_setting.article !== 'string') {
                            this.$set(this.module.decoded_setting, 'article', '');
                        }
                        break;
                }
            }
        }
    }
</script>

<style scoped>

</style>