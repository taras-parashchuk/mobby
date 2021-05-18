<template>
    <div :class="containerClass">
        <div class="moduleTitle moduleTitle--inContent">
            {{$parent.config.title}}
            <div class="moduleProducts__nav" v-if="$root.theme !== 'beauty'">
                <a class="sliderTo sliderTo--inContent sliderTo--left js-slideshow-nav" @click="$parent.nav('prev')">
                    <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--left"></i>
                </a>
                <a class="sliderTo sliderTo--inContent sliderTo--right js-slideshow-nav" @click="$parent.nav('next')">
                    <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--right"></i>
                </a>
            </div>
        </div>
        <div :class="config.design_type == 2 ? 'moduleCarousel__content': 'moduleTopCategories__content moduleTopCategories__content--inContent' ">
            <div :id="($parent.sliderName+'-'+$parent.sliderId)" class="owl-carousel" style="opacity: 1;">
                <template v-if="config.design_type == 1">
                    <div class="moduleTopCategories__item  moduleTopCategoriesItem moduleTopCategoriesItem--inSlider"
                         v-for="(slide, i) in slides">
                        <a :href="slide.link">
                            <div class="moduleTopCategoriesItem__inner">
                                <img :src="slide.compiled_image"
                                     :alt="slide.title"
                                     class="img-responsive"/>
                            </div>
                        </a>
                        <a :href="slide.link" class="moduleTopCategoriesItem__title"
                           :title="slide.title">
                            {{slide.title}}
                        </a>
                    </div>
                </template>
                <template v-else-if="config.design_type == 2">
                    <div class="moduleCarousel__item" v-for="(slide, i) in slides">
                        <template v-if="slide.link">
                            <a :href="slide.link">
                                <img v-if="slide.compiled_image" :src="slide.compiled_image"
                                     :alt="slide.title"
                                     class="img-responsive"/>

                                <video v-else width="100%" height="240" controls="">
                                    <source :src="slide.video" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </a>
                        </template>
                        <template v-else>
                            <img v-if="slide.compiled_image" :src="slide.compiled_image"
                                 :alt="slide.title"
                                 class="img-responsive"/>
                            <video v-else width="100%" height="240" controls="">
                                <source :src="slide.video" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </template>
                    </div>
                </template>
            </div>
            <div class="" v-if="$root.theme === 'beauty'">
                <a class="sliderTo sliderTo--inContent sliderTo--left js-slideshow-nav" @click="$parent.nav('prev')">
                    <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--left"></i>
                </a>
                <a class="sliderTo sliderTo--inContent sliderTo--right js-slideshow-nav" @click="$parent.nav('next')">
                    <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--right"></i>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "sliderDefault",
        props: ['slides', 'config'],
        computed: {
            containerClass() {
                let className = 'js-container-slider ';

                switch (this.config.design_type) {
                    case 1:
                        className += 'moduleTopCategories';
                        break;
                    case 2:
                        className += 'moduleCarousel';
                        break;

                }

                return className;
            }
        }
    }
</script>

<style scoped>

</style>