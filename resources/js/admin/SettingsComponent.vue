<template>
    <div class="table-list-container" v-if="loaded_settings">
        <div class="flex flex--justify-space-between">
            <div>
                <template>
                    <h2 class="mainContent__heading mainContent__heading--inProduct">
                        {{$root.translate.menu.settings.heading}}
                    </h2>
                </template>
            </div>
        </div>
        <div class="singleForm">
            <div class="singleForm__content">
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.company_name}}:
                        <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="text" class="input input--inForm"
                                   v-model="settings.main.company_name">
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.main_location}}:
                        <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <v-select
                                    :clearable="false"
                                    :searchable="false"
                                    :options="locations"
                                    v-model="settings.main.location"
                                    :reduce="location => location.id"
                                    class="input"
                                    label="name">
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.site_language}}:
                        <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <v-select
                                    :clearable="false"
                                    :searchable="false"
                                    :options="$root.languages"
                                    v-model="settings.main.site_language"
                                    class="input"
                                    :reduce="language => language.locale"
                                    label="name">
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.admin_language}}:
                        <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <v-select
                                    :clearable="false"
                                    :searchable="false"
                                    :options="$root.languages"
                                    v-model="settings.main.admin_language"
                                    class="input"
                                    :reduce="language => language.locale"
                                    label="name">
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.currency}}:
                        <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <v-select
                                    :clearable="false"
                                    :searchable="false"
                                    :options="currencies"
                                    v-model="settings.main.currency"
                                    class="input input--currency"
                                    :reduce="currency => currency.code"
                                    label="name">
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.sender_email}}:
                        <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <input type="text" class="input input--inForm"
                                   v-model="settings.main.sender_email">
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup singleFormGroup--inlineSet">
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.user_group_before_register}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <v-select
                                        :clearable="false"
                                        :searchable="false"
                                        :options="userGroups"
                                        v-model="settings.main.user_group_before_register"
                                        class="input"
                                        :reduce="group => group.id"
                                        label="name">
                                </v-select>
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.user_group_after_register}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <v-select
                                        :clearable="false"
                                        :searchable="false"
                                        :options="userGroups"
                                        v-model="settings.main.user_group_after_register"
                                        class="input"
                                        :reduce="group => group.id"
                                        label="name">
                                </v-select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.order_status_after_create}}:
                        <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <v-select
                                    :clearable="false"
                                    :searchable="false"
                                    :options="orderStatuses"
                                    v-model="settings.main.order_status_after_create"
                                    class="input"
                                    :reduce="orderStatus => orderStatus.id"
                                    label="name">
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translate.columns.order_status_refused}}:
                        <icon icon="icon" class="icon singleFormGroup__title_helper"></icon>
                    </div>
                    <div class="singleFormGroup__field">
                        <div class="flex flex--align-center">
                            <v-select
                                    :clearable="false"
                                    :searchable="false"
                                    :options="orderStatuses"
                                    v-model="settings.main.order_status_refused"
                                    class="input"
                                    :reduce="orderStatus => orderStatus.id"
                                    label="name">
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup singleFormGroup--inlineSet">
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.link_facebook}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm"
                                       v-model="settings.main.link_facebook">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.link_instagram}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm"
                                       v-model="settings.main.link_instagram">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.link_youtube}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm"
                                       v-model="settings.main.link_youtube">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.link_telegram}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm"
                                       v-model="settings.main.link_telegram">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup singleFormGroup--inlineSet">
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.header_logo}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <upload-thumb
                                        items_type="settings"
                                        :item="$root.encodeId('settings','main')"
                                        ref="thumb"
                                        :data="settings.main"
                                        attribute-src-name="header_logo"
                                        :file_path="settings.main.header_logo"
                                        :thumb_path="thumbnails.header_logo_thumb"
                                ></upload-thumb>
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.footer_logo}}:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <upload-thumb
                                        items_type="settings"
                                        :item="$root.encodeId('settings','main')"
                                        ref="thumb"
                                        :data="settings.main"
                                        attribute-src-name="footer_logo"
                                        :file_path="settings.main.footer_logo"
                                        :thumb_path="thumbnails.footer_logo_thumb"
                                ></upload-thumb>
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            Favicon:
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <upload-thumb
                                        items_type="settings"
                                        :item="$root.encodeId('settings','main')"
                                        ref="thumb"
                                        :data="settings.main"
                                        attribute-src-name="favicon_src"
                                        :file_path="settings.main.favicon_src"
                                        :thumb_path="thumbnails.favicon_thumb"
                                ></upload-thumb>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translateWords('Google info for OAuth')}}:
                    </div>
                </div>
                <div class="singleFormGroup singleFormGroup--inlineSet">
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.id}}
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm"
                                       v-model="settings.oauth.google.id">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.key}}
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm"
                                       v-model="settings.oauth.google.key">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="singleFormGroup">
                    <div class="singleFormGroup__title">
                        {{$root.translateWords('Facebook info for OAuth')}}:
                    </div>
                </div>
                <div class="singleFormGroup singleFormGroup--inlineSet">
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.id}}
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm"
                                       v-model="settings.oauth.facebook.id">
                            </div>
                        </div>
                    </div>
                    <div class="singleFormGroup__set">
                        <div class="singleFormGroup__title">
                            {{$root.translate.columns.key}}
                        </div>
                        <div class="singleFormGroup__field">
                            <div class="flex flex--align-center">
                                <input type="text" class="input input--inForm"
                                       v-model="settings.oauth.facebook.key">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!--
        <mdb-tabs
                v-if="loaded_settings"
                :active="0"
                default
                :links="[
                    {text: $root.translate.columns.main}
                ]">
            <template :slot="$root.translate.columns.main">

                <div class="custom-control custom-switch" style="display: none;">
                    <input type="checkbox" class="custom-control-input" id="maintenanceSwitcher"
                           v-model="settings.main.maintenance">
                    <label class="custom-control-label" for="maintenanceSwitcher">
                        {{$root.translate.columns.maintenance}}
                    </label>
                </div>

            </template>
        </mdb-tabs>
        -->
        <widget-actions :store="isChangedInfo ? 'store': false"></widget-actions>
    </div>
</template>

<script>

    let UploadThumb = require('./UploadThumbComponent').default;

    export default {
        name: "SettingsComponent",
        components: {
            UploadThumb
        },
        data() {
            return {
                loaded_settings: false,
                locations: [],
                currencies: [],
                userGroups: [],
                orderStatuses: [],

                thumbnails: {},

                settings: {
                    main: {
                        location: null,
                        sender_email: null,
                        emails: '',
                        socials: [
                            {key: null, link: null}
                        ],
                        company_name: null,
                        site_language: null,
                        admin_language: null,
                        currency: null,
                        header_logo: null,
                        footer_logo: null,
                        favicon_src: null,
                        maintenance: false,
                        custom_head: null,
                        user_group_before_register: null,
                        user_group_after_register: null,
                        order_status_after_create: null,
                        order_status_refused: null,
                        link_facebook: null,
                        link_instagram: null,
                        link_youtube: null,
                        link_telegram: null
                    },
                    oauth: {
                        google: {
                            id: '',
                            key: ''
                        },
                        facebook: {
                            id: '',
                            key: ''
                        }
                    },
                    advanced: {}
                },
                refreshing: false,
                savedOriginal: {}

            }
        },
        created() {
            axios.get('/admin/order-statuses', {
                params: {
                    autocomplete: true
                }
            }).then(httpResponse => {
                this.orderStatuses = httpResponse.data.order_statuses;
            });

            axios.get('/admin/locations', {
                params: {
                    autocomplete: true
                }
            }).then(httpResponse => {
                this.locations = httpResponse.data.locations;
            });

            axios.get('/admin/currencies', {
                params: {
                    autocomplete: true
                }
            }).then(httpResponse => {
                this.currencies = httpResponse.data.currencies;
            });

            axios.get('/admin/user-groups', {
                params: {
                    autocomplete: true
                }
            }).then(httpResponse => {
                this.userGroups = httpResponse.data.user_groups;
            });

            axios.get('/admin/settings').then(httpResponse => {
                for (let setting_group in this.settings) {
                    for (let key in this.settings[setting_group]) {
                        let setting = httpResponse.data.settings.find(setting => {
                            return (setting['group'] === setting_group && setting['name'] === key);
                        });

                        if (setting) {
                            this.$set(this.settings[setting_group], key, setting.value);
                        }// else {
                        // this.$set(this.settings[setting_group], key, null);
                        //}
                    }
                }

                this.$set(this, 'thumbnails', httpResponse.data.thumbnails);

                this.loaded_settings = true;

                this.savedOriginal = this.$root.copy(this.settings);
            });
        },
        computed:{
            isChangedInfo() {

                let current = this.$root.copy(this.settings);

                delete current.refreshing;

                let saved = this.$root.copy(this.savedOriginal);

                delete saved.refreshing;

                return JSON.stringify(current) !== JSON.stringify(saved);

            },
        },
        methods: {
            store() {

                this.refreshing = true;

                axios.post('/admin/settings', this.settings).then(httpResponse => {

                    this.$set(this, 'savedOriginal', this.$root.copy(this.settings));

                    if(httpResponse.data.is_changed_language === true){
                        window.location.reload();
                    }

                    this.$root.notify(httpResponse.data);
                }).catch(error => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            }
        }
    }
</script>

<style scoped>
    .select-wrapper {
        margin-bottom: 1.5rem;
        margin-top: 1.5rem;
    }
</style>