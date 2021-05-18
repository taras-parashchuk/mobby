require('./bootstrap');
window.Vue = require('vue');

import Notifications from 'vue-notification'
import velocity from 'velocity-animate'

Vue.use(Notifications, {velocity});

import {library} from '@fortawesome/fontawesome-svg-core'
import {
    faChevronDown,
    faChevronUp,
    faPaperPlane,
    faBars,
    faPlus,
    faSave,
    faPencilAlt,
    faTrashAlt,
    faCircleNotch,
    faClone,
    faRetweet
} from '@fortawesome/free-solid-svg-icons'
import {} from '@fortawesome/free-brands-svg-icons'

import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'

import vSelect from 'vue-select'

let dropdownSettings = {
    class: {
        'icon': true
    },
    props: {
        icon: 'play-button'
    }
};

vSelect.props.components.default = () => ({
    OpenIndicator: {
        render: createElement => createElement('icon', dropdownSettings),
    },
});

Vue.component('v-select', vSelect);

import 'vue-select/dist/vue-select.css';

Vue.component('font-awesome-icon', FontAwesomeIcon);

library.add([faChevronDown, faChevronUp, faPaperPlane, faBars, faPlus, faSave, faPencilAlt, faTrashAlt, faCircleNotch, faClone, faRetweet]);

Vue.component('widget-actions', require('./admin/widgetActions').default);

Vue.component('icon', require('./admin/Icon').default);

Vue.component('check', require('./admin/CheckComponent').default);

import VueGoodTablePlugin from './packages/vue-good-table';
//\packages\vue-good-table

Vue.use(VueGoodTablePlugin);

import {VTooltip, VPopover, VClosePopover} from 'v-tooltip'

Vue.directive('tooltip', VTooltip);
Vue.directive('close-popover', VClosePopover);
Vue.component('v-popover', VPopover);

import VuejsDialog from 'vuejs-dialog';

Vue.use(VuejsDialog, {
    html: false,
    loader: false,
    okText: window.translate.words['Continue'],
    cancelText: window.translate.words['Back'],
    backdropClose: false
});

import ConfirmComponentView from './admin/ConfirmComponent.vue';

const VIEW_NAME = 'confirm-window';
Vue.dialog.registerComponent(VIEW_NAME, ConfirmComponentView);

import Echo from "laravel-echo";

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 1234,
    cluster: 'eu',
    wsHost: window.location.hostname,
    wsPort: 6001,
    disableStats: true,
    encrypted: false,
    enabledTransports: ['ws'],
});

import VueClipboard from 'vue-clipboard2'

Vue.use(VueClipboard);

import * as trans_ru from './admin/translates/translates-ru.json';
import * as trans_uk from './admin/translates/translates-uk.json';

import VueI18n from 'vue-i18n'

Vue.use(VueI18n);

const messages = Object.assign({
    "uk": {
        ...trans_uk.default
    },
    "ru": {
        ...trans_ru.default
    }
});

const i18n = new VueI18n({
    locale: window.adminLanguage, // set locale
    messages, // set locale messages
});

new Vue({
    el: '#app',
    data: {
        adminLanguage: window.adminLanguage,
        currency_code: window.currency_code,
        languages: window.languages,
        translate: window.translate,
        popups: {},
        scroll_info:"",
        waiting_server_action: {
            status:false,
            text:""
        },
    },
    components: {
        'app': require('./admin/app').default
    },
    i18n,
    computed: {
        editorConfig() {
            let routeName = this.$refs.app.$route.name;

            let items_type = '';
            let id = this.$refs.app.$route.params.id;

            switch (routeName) {
                case 'article':
                    items_type = 'articles';
                    break;
                case 'information':
                    items_type = 'informations';
                    break;
                case 'category':
                    items_type = 'categories';
                    break;
                case 'product':
                    items_type = 'products';
                    break;
            }

            return {
                extraPlugins: 'youtube',
                filebrowserImageBrowseUrl: '/admin/filemanager?in_description=1&items_type=' + items_type + '&item=' + this.$root.encodeId(items_type, id) + '&type=imageFileManager',
                allowedContent: true
            };
        }
    },
    methods: {
        copy(o) {
            if (o === null) return null;

            var output, v, key;
            output = Array.isArray(o) ? [] : {};
            for (key in o) {
                v = o[key];
                output[key] = (typeof v === "object") ? this.copy(v) : v;
            }
            return output;
        },
        notify(httpResponseData) {

            let self = this;

            if (Object(httpResponseData).hasOwnProperty('errors')) {
                for (let error_name in httpResponseData.errors) {
                    httpResponseData.errors[error_name].forEach(error => {
                        self.$notify({
                            group: 'error',
                            type: 'error',
                            text: error
                        });
                    });
                }
            } else if (Object(httpResponseData).hasOwnProperty('message')) {
                self.$notify({
                    title: 500,
                    group: 'error',
                    type: 'error',
                    text: httpResponseData.message,
                    classes: ['notification']
                });
            } else {
                self.$notify({
                    group: 'success',
                    type: 'success',
                    text: httpResponseData.text,
                    classes: ['notification']
                });
            }
        },
        scrollToNewRow(els, el) {

            let $parent = $('.table-list-container');

            let containerTop = $parent.position().top;

            let containerBottom = containerTop + $parent.outerHeight();

            let currentScroll = document.documentElement.scrollTop || document.body.scrollTop;

            if (Math.abs(currentScroll - containerBottom) > currentScroll - containerTop) {

                els.unshift(el);

                $("html, body").animate({scrollTop: containerTop + "px"});

            } else {

                els.push(el);

                $("html, body").animate({scrollTop: containerBottom + "px"});
            }

        },
        encodeId(type, id) {
            if (id === null) id = '';

            return btoa(type + '-' + id);
        },
        translateWords(key) {
            if (Object(this.translate.words).hasOwnProperty(key)) {
                return this.translate.words[key];
            } else {
                return key;
            }
        },
        changePopupShowStatus(popupName, status = null) {

            console.log(popupName, status);

            if (Object(this.popups).hasOwnProperty(popupName)) {
                if (status === null) {
                    this.popups[popupName] = !this.popups[popupName]
                } else {
                    this.popups[popupName] = status;
                }
            } else {
                this.$set(this.popups, popupName, status === null ? true : status);
            }
        },
        waitingServerActionInfo(value, text = ""){
            this.waiting_server_action.text = text;
            this.waiting_server_action.status = value;
        }
    }
});