<template>
    <div class="widgetActions">
        <template v-if="!$parent.refreshing">
            <div v-if="edit"
                 class="bg-primary widgetActions__action widgetActions__action--hidden widgetActions__action--edit"
                 @click="$parent[edit]()">
                <font-awesome-icon icon="pencil-alt"></font-awesome-icon>
            </div>
            <div v-if="create"
                 class="bg-primary widgetActions__action widgetActions__action--hidden widgetActions__action--edit"
                 @click="$parent[create]()">
                <font-awesome-icon icon="save"></font-awesome-icon>
            </div>
            <div v-if="foreign" class="widgetActions__action">
                <a :href="foreign" target="_blank">
                    <icon icon="foreign" class="icon"></icon>
                </a>
            </div>
            <div v-if="remove"
                 class="bg-danger widgetActions__action widgetActions__action--hidden widgetActions__action--delete"
                 @click="$parent[remove]()">
                <icon icon="delete" class="icon"></icon>
            </div>
            <div v-if="copy"
                 class="bg-primary widgetActions__action widgetActions__action--hidden widgetActions__action--copy"
                 @click="$parent[copy]()">
                <icon icon="copy-item" class="icon"></icon>
            </div>
            <template v-if="others && others.length">
                <div v-for="other in others" v-if="other !== null" class="widgetActions__action widgetActions__action--long"
                     :class="{'widgetActions__action--additional': hasAdditionalStyle('other'), 'js-open-modal': isPopup(other.method)}"
                     @click.stop="$parent[other.method]()">
                    <icon :icon="other.icon" class="icon"></icon>
                    <span>{{other.actionName}}</span>
                </div>
            </template>
            <div v-if="add" @click.stop="$parent[add]()" class="widgetActions__action widgetActions__action--long"
                 :class="{'widgetActions__action--additional': hasAdditionalStyle('add'), 'js-open-modal': isPopup('add')}">
                <i>+</i> <span>{{trans.add}}</span>
            </div>
            <div v-if="store" @click="$parent[store]()" class="widgetActions__action widgetActions__action--long"
                 :class="{'widgetActions__action--store_inProduct': $route.name === 'product'}">
                <icon icon="floppy-disk" class="icon"></icon>
                <span>{{$root.translateWords('Save changes')}}</span>
            </div>
        </template>
        <div v-else class="widgetActions__action widgetActions__action--refreshing">
            <font-awesome-icon icon="circle-notch" spin></font-awesome-icon>
        </div>
    </div>
</template>

<script>
    export default {
        name: "widgetActions",
        props: {
            'popups': {
                type: Array,
                default: () => []
            },
            'additional-styles': {},
            'refreshing': {},
            'foreign': {},
            'store': {},
            'add': {},
            'create': {},
            'remove': {},
            'copy': {},
            'edit': {},
            'others': {},
            'trans': {}
        },
        methods: {
            hasAdditionalStyle(type) {
                return this.additionalStyles && this.additionalStyles.indexOf(type) !== -1 || false;
            },
            isPopup(type) {
                return this.popups.indexOf(type) !== -1;
            }
        }
    }
</script>

<style scoped>

</style>