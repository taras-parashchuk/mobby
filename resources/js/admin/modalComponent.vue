<template>
    <transition name="modal-fade">
        <div class="modal-backdrop" v-show="isShow">
            <div class="modal" :style="modalStyle" v-click-outside="onClickOutside">
                <div class="modal-inner">
                    <header class="modal__header">
                        <slot name="header">
                            <slot name="title">
                                <span>{{title}}</span>
                            </slot>
                            <icon class="icon icon-close" icon="cancel" v-if="hasCloseIcon"
                                  @click.native="close()"></icon>
                        </slot>
                    </header>
                    <section class="modal__body" v-if="isShow" v-bar="{
                    useScrollbarPseudo: true,
                    preventParentScroll: true
                }">
                        <div class="modal__content">
                            <slot>
                                I'm the default body!
                            </slot>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>

    import ClickOutside from 'vue-click-outside'

    export default {
        name: "modal-test",
        props: {
            name: {
                type: String,
                required: true
            },
            title: {
                type: String,
                required: false,
            },
            useBackdropClose: {
                type: Boolean,
                required: false,
                default: true
            },
            hasCloseIcon: {
                type: Boolean,
                required: false,
                default: true
            },
            width: {

            }
        },
        directives: {
            ClickOutside
        },
        computed: {
            isShow() {
                return this.$root.popups.hasOwnProperty(this.name) && this.$root.popups[this.name];
            },
            modalStyle(){
                let style = {};

                style.width = this.width;

                return style;

            }
        },
        methods: {
            close() {
                this.$root.changePopupShowStatus(this.name, false);

                this.$emit('closed');

                this.opened = false;
            },
            open() {
                this.$emit('opened');
            },
            onClickOutside(event) {
                
                 if(event.target.tagName !== 'svg' && event.target.className !== 'js-open-modal' && this.isShow && this.useBackdropClose) this.close();
                 
            }
        },
        watch: {
            isShow(newValue) {
                if (newValue) this.open();
            }
        }
    }
</script>