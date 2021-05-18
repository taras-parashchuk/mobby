<template>
    <div class="table-list-container" v-if="Object.keys(location).length">
        <div class="flex flex--justify-space-between">
            <div>
                <div class="breadcrumbs">
                    <router-link class="breadcrumbs__link" :to="{name: 'dashboard'}">{{$root.translate.columns.home}}
                    </router-link>
                    -
                    <router-link class="breadcrumbs__link" :to="{name: 'locations'}">
                        {{$root.translate.menu.localisation.items.locations}}
                    </router-link>
                </div>
                <h2 class="mainContent__heading mainContent__heading--inProduct">
                    {{location.translates.find(translate => {return translate.locale ==
                    $root.adminLanguage}).name}}</h2>
            </div>
            <div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.status}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="switcherStatus">
                            <div @click="location.status = false" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': location.status === false}">
                                {{$root.translate.columns.disabled_short}}
                            </div>
                            <div @click="location.status = true" class="switcherStatus__value"
                                 :class="{'switcherStatus__value--active': location.status === true}">
                                {{$root.translate.columns.enabled_short}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="singleForm">
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.name}}:
                </div>
                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in location.translates">
                    <div class="flex flex--align-center">
                        {{$root.languages.find((language) => {return language.locale ===
                        translate.locale}).name}}:
                        <input type="text" class="input input--inForm input--label_left"
                               v-model="translate.name">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.address}}:
                </div>
                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in location.translates">
                    <div class="flex flex--align-center">
                        {{$root.languages.find((language) => {return language.locale ===
                        translate.locale}).name}}:
                        <input type="text" class="input input--inForm input--label_left"
                               v-model="translate.address">
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translate.columns.schedule}}:
                </div>
                <div class="singleFormGroup__field inputWithTranslates" v-for="translate in location.translates">
                    <div class="flex">
                        <span class="mr--8">
                            {{$root.languages.find((language) => {return language.locale === translate.locale}).name}}:
                        </span>
                        <vue-ckeditor :config="$root.editorConfig"
                                      v-model="translate.schedule"></vue-ckeditor>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    {{$root.translateWords('Telephones')}}:
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <v-select
                                :clearable="true"
                                :no-drop="true"
                                taggable multiple push-tags
                                v-model="location.telephones"
                                class="input input--inForm input--tags vs--multiply pt--0"
                                :components="{Deselect}">
                        </v-select>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup">
                <div class="singleFormGroup__title">
                    Email:
                </div>
                <div class="singleFormGroup__field">
                    <div class="flex flex--align-center">
                        <v-select
                                :clearable="true"
                                :no-drop="true"
                                taggable multiple push-tags
                                v-model="location.emails"
                                class="input input--inForm input--tags vs--multiply pt--0"
                                :components="{Deselect}">
                        </v-select>
                    </div>
                </div>
            </div>
            <div class="singleFormGroup singleFormGroup--inlineSet">
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.latitude}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="text" class="input" v-model="location.latitude">
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup__set">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.longitude}}:
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="text" class="input" v-model="location.longitude">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <widget-actions :store="isChangedInfo ? 'applyForm': false" remove="remove"></widget-actions>
    </div>
</template>

<script>

    import VueCkeditor from 'vue-ckeditor2';

    export default {
        name: "Location",
        components: {
            VueCkeditor,
        },
        data() {
            return {
                location: {},
                refreshing: false,
                Deselect: {
                    render: createElement => createElement('icon', {
                        class: 'icon',
                        props: {
                            icon: 'error'
                        }
                    }),
                },
                savedOriginal: {}
            };
        },
        computed: {
            languageLinks() {
                let languages = [];

                for (let language of this.$root.languages) {
                    languages.push({
                        text: language.name,
                        locale: language.locale
                    });
                }

                return languages;
            },
            isChangedInfo() {

                let current = this.$root.copy(this.location);

                if(Object.keys(current).length){

                    delete current.refreshing;

                    current.translates.forEach(translate => translate.schedule !== null ? translate.schedule = translate.schedule.trim() : false);

                    let saved = this.$root.copy(this.savedOriginal);

                    saved.translates.forEach(translate => translate.schedule !== null ? translate.schedule = translate.schedule.trim() : false);

                    delete saved.refreshing;

                    return JSON.stringify(current) !== JSON.stringify(saved);
                }
            }
        },
        created() {
            axios.get('/admin/locations/' + this.$route.params.id + '/edit').then(httpResponse => {
                this.location = httpResponse.data;

                this.$set(this, 'savedOriginal', this.$root.copy(this.location));

            });
        },
        methods: {
            applyForm() {

                this.refreshing = true;

                axios.put('/admin/locations/' + this.location.id, this.location)
                    .then(httpResponse => {
                        this.$root.notify(httpResponse.data);

                        this.$set(this, 'savedOriginal', this.$root.copy(this.location));

                    }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            },
            remove(){
                this.$dialog.confirm({
                    title: this.$root.translateWords('Are you sure?'),
                    body: this.$root.translateWords('This action cannot be undone')
                }, {
                    view: 'confirm-window', // can be set globally too
                }).then(() => {
                    this.refreshing = true;

                    axios.delete('/admin/locations/'+this.location.id).then(response => {

                        this.$router.push({name: "locations"});

                    }).catch(error => {
                        if (error.response) this.$root.notify(error.response.data);
                    }).finally(() => {
                        this.refreshing = false;
                    });
                });
            }
        }

    }
</script>

<style scoped>

</style>