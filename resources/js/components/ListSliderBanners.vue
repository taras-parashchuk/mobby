<template>
    <div class="module module--inContent" :id="'banner_'+config.module_id">
        <div class="moduleBanner" v-if="!mobile">
            <div class="moduleBanner__content">
                <template v-if="$root.theme === 'beauty'">
                    <div class="moduleBanner__item" v-for="slide in slides">

                        <a v-if="slide.link" :href="slide.link" :title="slide.title"
                           class="moduleBanner__col">
                            <img :src="slide.compiled_image" :alt="slide.title"
                                 class="img-responsive">
                        </a>

                        <div v-else class="moduleBanner__col">
                            <img :src="slide.compiled_image" :alt="slide.title"
                                 class="img-responsive">
                        </div>

                    </div>
                </template>
                <template v-else>
                    <template v-for="slide in slides">

                        <a v-if="slide.link" :href="slide.link" :title="slide.title"
                           class="moduleBanner__col">
                            <img :src="slide.compiled_image" :alt="slide.title"
                                 class="img-responsive">
                        </a>

                        <div v-else class="moduleBanner__col">
                            <img :src="slide.compiled_image" :alt="slide.title"
                                 class="img-responsive">
                        </div>

                    </template>
                </template>
            </div>
        </div>
        <div v-else class="moduleSlidewhow">
            <owl-slider type="slideshow" slider-name='slideshow' :slider-id="config.module_id" :slides='slides'
                        :config='config'></owl-slider>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ListSliderBanners",
        props: ['slides', 'config'],
        components: {
            'owl-slider': require('./owlSlider.vue').default,
        },
        data() {
            return {
                mobile: false
            }
        },
        mounted() {
            if ($(window).width() > 730) {
                this.mobile = false;
            } else {
                this.mobile = true;
            }
        }
    }
</script>

<style scoped>

</style>