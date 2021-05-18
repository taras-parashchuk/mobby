<template>
    <div class="productInfo__slider">
        <hooper @slide="updateSlides" @updated="updateSettings" ref="carousel" :wheelControl="false"
                :trimWhiteSpace="true" :center-mode="false"
                :settings="settings">
            <slide v-for="(item, item_n) in items" :key="item_n" class="productInfo__slide js-product-gallery">
                <a :href="item.popup"
                   :thumb="item.thumb"
                   class="productInfo__addt js-change-img"
                   @click.prevent="changeImg(item_n)"
                   :class="{'productInfo__addt--active': (activeImage === item_n)}">
                    <img :src="item.small">
                </a>
            </slide>
        </hooper>
        <template v-if="showNavigation">
            <template v-if="$root.theme === 'beauty'">
                <div class="slider-control slider-control--top icon sb-icon-down-arrow"
                     :class="{'slider-control--disabled': !hasLeftItems}" @click="slidePrev"></div>
                <div class="slider-control slider-control--bottom icon sb-icon-down-arrow"
                     :class="{'slider-control--disabled': !hasRightItems}" @click="slideNext"></div>
            </template>
            <template v-else>
                <div class="slider-control slider-control--left-top icon sb-icon-down-arrow"
                     :class="{'slider-control--disabled': !hasLeftItems}" @click="slidePrev"></div>
                <div class="slider-control slider-control--right-top icon sb-icon-down-arrow"
                     :class="{'slider-control--disabled': !hasRightItems}" @click="slideNext"></div>
            </template>
        </template>
    </div>
</template>

<script>

    import {Hooper, Slide} from 'hooper';
    import 'hooper/dist/hooper.css';

    let breakpoints;

    if(window.theme === 'beauty'){
        breakpoints = {
            0: {
                itemsToShow: 4,
                vertical: false,
            },
            671: {
                itemsToShow: 6,
                vertical: false
            },
            971:{
                itemsToShow: 6,
                vertical: true
            },
            1200: {
                itemsToShow: 5,
                vertical: true
            }
        };
    }else{
        breakpoints = {
            0: {
                itemsToShow: 2,
            },
            531: {
                itemsToShow: 3,
            },
            671: {
                itemsToShow: 4,
            },
            1200: {
                itemsToShow: 5
            }
        };
    }

    export default {
        name: "product-images-slider",
        components: {
            Hooper,
            Slide
        },
        data() {
            return {
                activeImage: 0,
                freshSettings: {},
                startConfigNavigation: false,
                currentSlideIndex: 0,
                settings: {
                    itemsToShow: 5,
                    centerMode: false,
                    breakpoints: breakpoints
                }
            }
        },
        created() {

            let windowWidth = $(window).width();

            let showNavigation = false;

            for (let breakpoint in breakpoints) {
                if (windowWidth > breakpoint && this.items.length > breakpoints[breakpoint].itemsToShow) {
                    showNavigation = true;
                }
            }

            this.startConfigNavigation = showNavigation;
        },
        props: ['items'],
        computed: {
            showNavigation() {
                if (!Object.keys(this.freshSettings).length) {
                    return this.startConfigNavigation;
                } else {
                    return this.freshSettings.itemsToShow < this.items.length;
                }
            },
            hasLeftItems() {
                return this.currentSlideIndex > 0;
            },
            hasRightItems() {
                return this.currentSlideIndex + this.freshSettings.itemsToShow < this.items.length;
            }
        },
        methods: {
            changeImg(index) {
                this.$root.$refs.main_product_image_wrapper.href = this.items[index].popup;

                this.$root.$refs.main_product_image_content.src = this.items[index].thumb;

                if(this.$root.theme === 'default'){
                    this.$root.$refs.sticky_card_image_content.src = this.items[index].thumb;
                }

                this.$root.$refs.main_product_image_content.dataset.large = this.items[index].popup;
                this.activeImage = index;
            },
            slidePrev() {
                this.$refs.carousel.slidePrev();
            },
            slideNext() {
                this.$refs.carousel.slideNext();
            },
            updateSettings(data) {
                this.$set(this, 'freshSettings', data.settings);
            },
            updateSlides(data) {
                this.currentSlideIndex = data.currentSlide;
            }
        }
    }
</script>

<style scoped>
    .hooper {
        height: 100%;
    }

    .slider-control {
        position: absolute;
        left: 50%;
        right: unset;
        cursor: pointer;
    }

    .slider-control--top {
        top: 0;
        bottom: unset;
        transform: translateX(-50%) rotate(180deg);
    }

    .slider-control--bottom {
        bottom: 0;
        top: unset;
        transform: translateX(-50%) rotate(0deg);
    }

    .slider-control--right {
        right: 0;
        left: unset;
        transform: translateY(-50%) rotate(180deg);
    }

    .slider-control--left {
        left: 0;
        right: unset;
        transform: translateY(-50%) rotate(0deg);
    }

    .slider-control--right-top {
        top: 50%;
        right: 0;
        left: unset;
        transform: translateY(-50%) rotate(180deg);
    }

    .slider-control--left-top {
        top: 50%;
        left: 0;
        right: unset;
        transform: translateY(-50%) rotate(0deg);
    }


</style>