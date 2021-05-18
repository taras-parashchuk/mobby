<template>
    <modal name="hello-world">
        <v-select
                @input="onChangeControl"
                :clearable="false"
                :searchable="false"
                :options="controls"
                v-model="activeControl"
                class="input"
                :reduce="control => control.type"
                label="title">
        </v-select>
        <template v-if="activeControl !== null">
            <template v-if="activeControl === 'name'">

                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.name}}:
                    </div>
                    <div class="singleFormGroup__field inputWithTranslates" v-for="language in $root.languages">
                        <div class="flex flex--align-center">
                            {{language.name}}:
                            <input type="text" class="input input--inForm input--label_left"
                                   v-model="settings.name[language.locale]">
                        </div>
                    </div>
                </div>

            </template>
            <a href="javascript:void(0)" @click="apply">save</a>
        </template>
    </modal>
</template>

<script>
    export default {
        name: "ProductMassEditModal",
        props: ['products'],
        data() {
            return {
                show: false,
                controls: [
                    {
                        type: 'name',
                        title: this.$root.translate.columns.name
                    }
                ],
                activeControl: null,
                settings: {
                    name: {}
                }
            }
        },
        created() {

        },
        methods: {
            open() {
                this.$modal.show('hello-world');
            },
            hide() {
                this.$modal.hide('hello-world');
            },
            apply() {
                axios.put('/admin/products/mass-edit', {
                    type: this.activeControl,
                    settings: this.settings[this.activeControl],
                    products: this.products
                }).then(httpResponse => {
                    this.$emit('refresh');
                });
            },
            onChangeControl() {

                let self = this;

                switch (this.activeControl) {
                    case 'name':
                        this.$root.languages.forEach(language => {
                            self.$set(self.settings.name, language.locale, '');
                        });
                        break;
                }
            }
        }
    }
</script>