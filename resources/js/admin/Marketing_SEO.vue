<template>
    <div class="table-list-container" v-if="Object.keys(data).length">
        <mdb-container>
            <mdb-tabs
                    @activeTab="setActiveTabIndex"
                    :active="activeTab"
                    default
                    :links="[
                    { text: $root.translateWords('Home page') },
                    { text: $root.translateWords('Scripts and Tags') },
                    { text: $root.translateWords('Redirects') },
                    { text: $root.translateWords('SEO templates for tags') },
                    ]">
                <template :slot="$root.translateWords('Home page')">
                    <mdb-tabs
                            :active="0"
                            color="cyan"
                            default
                            :links="languageLinks">
                        <template v-for="language in languageLinks" :slot="language.text">
                            <mdb-input :label="$root.translate.columns['meta-title']"
                                       v-model="data.pages.home.translates.find(translate => {return language.locale === translate.locale}).meta_title"
                                       required/>
                            <mdb-input :label="$root.translate.columns['meta-description']"
                                       v-model="data.pages.home.translates.find(translate => {return language.locale === translate.locale}).meta_description"
                                       required/>
                            <mdb-input :label="$root.translate.columns['meta-keywords']"
                                       v-model="data.pages.home.translates.find(translate => {return language.locale === translate.locale}).meta_keywords"
                                       required/>
                            <div class="form-group">
                                <label>{{ $root.translate.columns.description }}</label>
                                <vue-editor
                                        v-model="data.pages.home.translates.find(translate => {return language.locale === translate.locale}).description"></vue-editor>
                            </div>
                        </template>
                    </mdb-tabs>
                </template>
                <template :slot="$root.translateWords('Scripts and Tags')">
                    <div class="form-group">
                        <textarea v-model="data.script_tags.head" class="form-control" rows="5"></textarea>
                        <p>
                            {{$root.translateWords('Insert here tags or scripts for HEAD section on the site') }}.
                            {{ $root.translateWords('For example')}}
                            <code>
                                &lt;meta name="google-site-verification"
                                content="YMY5jCYD-I_VTZusapVIs-iS8_s52EG4AC1c1c6Z8hY"/&gt;
                            </code>
                        </p>
                    </div>
                    <div class="form-group">
                        <textarea v-model="data.script_tags.start_body" class="form-control" rows="5"></textarea>
                        <p>
                            {{$root.translateWords('Insert here scripts(without script tags) for show after tag body onthe site') }}.
                            {{ $root.translateWords('For example')}}
                            <code>
                                gtag('event', 'page_view', {
                                'send_to': 'AW-791172868',
                                'user_id': 'replace with value'
                                });
                            </code>
                        </p>
                    </div>
                    <div class="form-group">
                        <textarea v-model="data.script_tags.end_body" class="form-control" rows="5"></textarea>
                        <p>
                            {{$root.translateWords('Insert here scripts(without script tags) for show before end of tag body on the site') }}.
                            {{ $root.translateWords('For example')}}
                            <code>
                                (function () {
                                var widget_id = 'FgVYxY2nJb';
                                var d = document;
                                var w = window;

                                function l() {
                                var s = document.createElement('script');
                                s.type = 'text/javascript';
                                s.async = true;
                                s.src = '//code.jivosite.com/script/widget/' + widget_id;
                                var ss = document.getElementsByTagName('script')[0];
                                ss.parentNode.insertBefore(s, ss);
                                }

                                setTimeout(function(){
                                l();
                                }, 5000);

                                })();
                            </code>
                        </p>
                    </div>
                </template>
                <template :slot="$root.translateWords('Redirects')">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <td>{{$root.translateWords('Target url')}}</td>
                            <td>{{$root.translateWords('Source url')}}</td>
                            <td>{{$root.translate.columns.actions}}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(redirectInfo, index) in redirects">
                            <td>
                                <table class="table">
                                    <tr v-for="(source, sourceIndex) in redirectInfo.sources">
                                        <td>
                                            <mdb-input class="mt-0 mb-0" size="sm"
                                                       v-model="source.url"/>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)"
                                               @click="removeRedirectSource(index, sourceIndex)">
                                                <mdb-icon icon="trash-alt"/>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                <a class="ml-2" href="javascript:void(0)" @click="addSourceUrl(index)">
                                    <font-awesome-icon icon="plus"></font-awesome-icon>
                                </a>
                            </td>
                            <td>
                                <mdb-input class="mt-0 mb-0" size="sm"
                                           v-model="redirectInfo.url"/>
                            </td>
                            <td>
                                <a class="mr-2" href="javascript:void(0)" @click="removeRedirect(index)">
                                    <mdb-icon icon="trash-alt"/>
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </template>
                <template :slot="$root.translateWords('SEO templates for tags')">
                    <p>{{$root.translateWords('You can use in template these tags:') }} <span class="text-primary">&lt;H1&gt;</span>. {{$root.translateWords('Only in products:') }} <span class="text-primary">&lt;PRICE&gt;</span>, <span class="text-primary">&lt;SKU&gt;</span>, <span class="text-primary">&lt;ATTR_#&gt;</span>.
                    {{$root.translateWords('Where # is ID of characteristic')}}</p>
                    <mdb-tabs
                            :active="0"
                            color="cyan"
                            default
                            :links="languageLinks">
                        <template v-for="language in languageLinks" :slot="language.text">
                            <section>
                                <h3>{{$root.translate.columns.category}}</h3>
                                <main>
                                    <mdb-input :label="$root.translate.columns['meta-title']"
                                               v-model="data.seo_templates.templates.category.translates.find(translate => {return language.locale === translate.locale}).meta_title"
                                               required/>
                                    <mdb-input :label="$root.translate.columns['meta-description']"
                                               v-model="data.seo_templates.templates.category.translates.find(translate => {return language.locale === translate.locale}).meta_description"
                                               required/>
                                </main>
                            </section>
                            <section>
                                <h3>{{$root.translate.columns.subcategory}}</h3>
                                <main>
                                    <mdb-input :label="$root.translate.columns['meta-title']"
                                               v-model="data.seo_templates.templates.subcategory.translates.find(translate => {return language.locale === translate.locale}).meta_title"
                                               required/>
                                    <mdb-input :label="$root.translate.columns['meta-description']"
                                               v-model="data.seo_templates.templates.subcategory.translates.find(translate => {return language.locale === translate.locale}).meta_description"
                                               required/>
                                </main>
                            </section>
                            <section>
                                <h3>{{$root.translate.columns.product}}</h3>
                                <main>
                                    <mdb-input :label="$root.translate.columns['title']"
                                               v-model="data.seo_templates.templates.product.translates.find(translate => {return language.locale === translate.locale}).h1"
                                               required/>
                                    <mdb-input :label="$root.translate.columns['meta-title']"
                                               v-model="data.seo_templates.templates.product.translates.find(translate => {return language.locale === translate.locale}).meta_title"
                                               required/>
                                    <mdb-input :label="$root.translate.columns['meta-description']"
                                               v-model="data.seo_templates.templates.product.translates.find(translate => {return language.locale === translate.locale}).meta_description"
                                               required/>
                                </main>
                            </section>
                            <section>
                                <h3>{{$root.translateWords('Category with filter')}}</h3>
                                <main>
                                    <mdb-input :label="$root.translate.columns['meta-title']"
                                               v-model="data.seo_templates.templates.filter_in_category.translates.find(translate => {return language.locale === translate.locale}).meta_title"
                                               required/>
                                    <mdb-input :label="$root.translate.columns['meta-description']"
                                               v-model="data.seo_templates.templates.filter_in_category.translates.find(translate => {return language.locale === translate.locale}).meta_description"
                                               required/>
                                </main>
                            </section>
                            <section>
                                <h3>{{$root.translateWords('Subcategory with filter')}}</h3>
                                <main>
                                    <mdb-input :label="$root.translate.columns['meta-title']"
                                               v-model="data.seo_templates.templates.filter_in_subcategory.translates.find(translate => {return language.locale === translate.locale}).meta_title"
                                               required/>
                                    <mdb-input :label="$root.translate.columns['meta-description']"
                                               v-model="data.seo_templates.templates.filter_in_subcategory.translates.find(translate => {return language.locale === translate.locale}).meta_description"
                                               required/>
                                </main>
                            </section>
                            <section>
                                <h3>{{$root.translateWords('Mix filter in category')}}</h3>
                                <main>
                                    <mdb-input :label="$root.translate.columns['meta-title']"
                                               v-model="data.seo_templates.templates.mix_filter_in_category.translates.find(translate => {return language.locale === translate.locale}).meta_title"
                                               required/>
                                    <mdb-input :label="$root.translate.columns['meta-description']"
                                               v-model="data.seo_templates.templates.mix_filter_in_category.translates.find(translate => {return language.locale === translate.locale}).meta_description"
                                               required/>
                                </main>
                            </section>
                            <section>
                                <h3>{{$root.translateWords('Mix filter in subcategory')}}</h3>
                                <main>
                                    <mdb-input :label="$root.translate.columns['meta-title']"
                                               v-model="data.seo_templates.templates.mix_filter_in_subcategory.translates.find(translate => {return language.locale === translate.locale}).meta_title"
                                               required/>
                                    <mdb-input :label="$root.translate.columns['meta-description']"
                                               v-model="data.seo_templates.templates.mix_filter_in_subcategory.translates.find(translate => {return language.locale === translate.locale}).meta_description"
                                               required/>
                                </main>
                            </section>
                            <section>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="statusSwitcher"
                                           v-model="data.seo_templates.status">
                                    <label class="custom-control-label" for="statusSwitcher">{{ $root.translate.columns.status }}</label>
                                </div>
                            </section>
                        </template>
                    </mdb-tabs>
                </template>
            </mdb-tabs>
        </mdb-container>
        <template v-if="activeTab === 2">
            <template v-if="redirects.length">
                <widget-actions create="storeRedirects" add="addRedirect"></widget-actions>
            </template>
            <template v-else>
                <widget-actions add="addRedirect"></widget-actions>
            </template>
        </template>
        <template v-else>
            <widget-actions edit="store"></widget-actions>
        </template>
    </div>
</template>

<script>

    import {mdbTabs, mdbAlert, mdbTbl, mdbTblHead, mdbTblBody, mdbIcon, mdbBtn, mdbInput} from 'mdbvue';

    export default {
        name: "Marketing_SEO",
        components: {
            mdbTabs,
            mdbTbl,
            mdbTblHead,
            mdbTblBody,
            mdbIcon,
            mdbAlert,
            mdbBtn,
            mdbInput
        },
        created() {
            axios.get('/admin/marketing-seo').then(httpResponse => {
                this.data = httpResponse.data;
            });

            axios.get('/admin/redirects').then(httpResponse => {

                httpResponse.data.forEach(redirect => {
                    redirect.refreshing = false;
                });

                this.redirects = httpResponse.data;
            });
        },
        data() {
            return {
                data: {},
                redirects: [],
                activeTab: 0,
                refreshing: false
            }
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
        },
        methods: {
            store() {
                axios.post('/admin/marketing-seo', this.data).then(httpResponse => {
                    this.$root.notify(httpResponse.data);
                }).catch((error) => {
                    if (error.response) this.$root.notify(error.response.data);
                });
            },
            addRedirect() {
                this.$root.scrollToNewRow(this.redirects, {
                    refreshing: false,
                    url: '',
                    sources: []
                });
            },
            addSourceUrl(index) {
                this.redirects[index].sources.push({
                    url: ''
                });
            },
            setActiveTabIndex(index) {
                this.activeTab = index;
            },
            storeRedirects() {

                this.refreshing = true;

                axios.post('/admin/redirects', {redirects: this.redirects}).then(httpResponse => {
                    this.$root.notify(httpResponse.data);
                }).catch((error) => {
                    if (error.response) this.$root.notify(error.response.data);
                }).finally(() => {
                    this.refreshing = false;
                });
            },
            removeRedirect(index) {
                this.redirects.splice(index, 1);
            },
            removeRedirectSource(redirectIndex, sourceIndex) {
                this.redirects[redirectIndex].sources.splice(sourceIndex, 1);
            }
        }
    }
</script>

<style scoped>

</style>