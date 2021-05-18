<template>
    <div class="moduleSlidewhow">
        <div :id="($parent.sliderName+'-'+$parent.sliderId)" class="owl-carousel" style="opacity: 1;">
            <div class="moduleSlidewhow__item" v-for="(slide, i) in slides">
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
        </div>
        <template v-if="config.type === 1 && (config.nav || config.pagination)">
            <template v-if="$root.theme === 'beauty'">
                <div class="sliderToFull">
                    <div class="sliderToFull__container" v-if="config.nav">
                        <a class="sliderTo sliderTo--left js-point-to js-slideshow-nav"
                           @click="$parent.nav('prev')">
                            <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--left"></i>
                        </a>
                        <a class="sliderTo sliderTo--right js-point-to js-slideshow-nav"
                           @click="$parent.nav('next')">
                            <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--right"></i>
                        </a>
                    </div>
                </div>
                <div class="moduleSlidewhow__nav moduleSlidewhow__nav--full" v-if="config.pagination">
                    <div class="sliderPagination">
                        <a href="javascript:void(0)"
                           class="sliderPagination__item js-slideshow-pagination"
                           v-for="(slide, i) in slides"
                           :data-number="i"
                           @click="$parent.pagination(i)"></a>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="sliderToFull__container" v-if="config.nav">
                    <a class="sliderTo sliderTo--left sliderToFull js-point-to sliderToFull--left js-slideshow-nav"
                       @click="$parent.nav('prev')">
                        <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--left"></i>
                    </a>
                    <a class="sliderTo sliderTo--right sliderToFull js-point-to sliderToFull--right js-slideshow-nav"
                       @click="$parent.nav('next')">
                        <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--right"></i>
                    </a>
                </div>
                <div class="moduleSlidewhow__nav moduleSlidewhow__nav--full" v-if="config.pagination">
                    <div class="sliderPagination">
                        <a href="javascript:void(0)"
                           class="sliderPagination__item js-slideshow-pagination"
                           v-for="(slide, i) in slides"
                           :data-number="i"
                           @click="$parent.pagination(i)"></a>
                    </div>
                </div>
            </template>
        </template>
        <template v-else-if="config.type === 0 && (config.nav || config.pagination)">
            <div class="moduleSlidewhow__nav">
                <a class="sliderTo js-slideshow-nav" @click="$parent.nav('prev')" v-if="config.nav">
                    <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--left"></i>
                </a>
                <div class="sliderPagination">
                    <a v-for="(item, i) in slides " href="javascript:void(0)"
                       class="sliderPagination__item js-slideshow-pagination"
                       :data-number="i"
                       @click="$parent.pagination(i)"
                        v-if="config.pagination"></a>
                </div>
                <a class="sliderTo js-slideshow-nav" @click="$parent.nav('next')" v-if="config.nav">
                    <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--right"></i>
                </a>
            </div>
        </template>
    </div>
</template>

<script>
    export default {
        name: "sliderSlideshow",
        props: ['slides', 'config'],
    }
</script>

<style scoped>

</style>