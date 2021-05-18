<template>
    <div class="module module--inContent">
        <template v-if="type === 'products'">
            <slider-products :products='products'></slider-products>
        </template>
        <template v-else-if="type === 'slideshow'">
            <slider-slideshow :slides='slides' :config="config"></slider-slideshow>
        </template>
        <template v-else-if="type === 'articles'">
            <slider-articles :articles='articles'></slider-articles>
        </template>
        <template v-else-if="type === 'slider-default'">
            <slider-default :slides="slides" :config="config"></slider-default>
        </template>
    </div>
</template>

<script>
    export default {
        name: "owlSlider",
        props: ['type', 'slides', 'products', 'articles', 'sliderId', 'sliderName', 'config'],
        data(){
          return {
              currentSlide: 0
          }
        },
        components: {
            'slider-slideshow': require('./../components/sliderSlideshow.vue').default,
            'slider-products': require('./../components/sliderProducts.vue').default,
            'slider-articles': require('./../components/sliderArticles').default,
            'slider-default': require('./../components/sliderDefault').default
        },
        computed: {
            getSliderTarget() {
                return '#' + this.sliderName + '-' + this.sliderId;
            }
        },
        mounted() {

            let self = this;

            let settings = {
                autoplay: !!self.config.autoplay,
                autoplayTimeout: self.config.speed || 4000,
                smartSpeed: self.config.speed || 3000,
                dots: self.config.type,
                responsiveClass: true,
                autoplayHoverPause: true,
                loop: true,
                items: parseInt(self.config.visible || 1),
                onInitialized: function (e) {

                    let totalCount = e.item.count;
                    let totalSize = e.page.size;

                    if (totalSize >= totalCount) {
                        $(e.target).closest('.js-container-slider').find(".js-slideshow-nav").addClass("sliderTo--hidden");
                    }
                },
                onChanged: function (e) {

                    if(e.item.index === null) return;

                    if(self.currentSlide < e.item.count-1){
                        self.currentSlide++;
                    }else{
                        self.currentSlide = 0;
                    }
                }
            };

            if(this.type === 'products'){
                settings.stagePadding = 1;

                if(this.$root.theme === 'beauty'){
                    settings.margin = 7;
                }

                if(this.products.length <= this.config.visible){
                    settings.loop = false;
                }

                //settings.onInitialized;

                settings.responsive = {
                    0: {
                        items: 2
                    },
                    670: {
                        items: 3
                    },
                    1200: {
                        items: parseInt(self.config.visible)
                    }
                };
            }else if(this.type === 'slideshow'){
                settings.items = 1;

                settings.onChanged = function (e) {

                    if(e.item.index === null) return;

                    let $carousel = $(e.target).closest('.moduleSlidewhow');

                    $carousel.find('.sliderPagination__item').removeClass('sliderPagination__item--active');
                    $carousel.find('.sliderPagination__item[data-number = ' + self.currentSlide + ']')
                        .addClass('sliderPagination__item--active');

                    if(self.currentSlide < e.item.count-1){
                        self.currentSlide++;
                    }else{
                        self.currentSlide = 0;
                    }
                };

                settings.onInitialized = function (e) {

                    let $carousel = $(e.target).closest('.moduleSlidewhow');

                    $carousel.find('.sliderPagination__item').removeClass('sliderPagination__item--active');
                    $carousel.find('.sliderPagination__item').first().addClass('sliderPagination__item--active');
                }

            } else if(this.type === 'articles'){

                settings.stagePadding = 1;

                if(this.articles.length <= this.config['visible']){
                    settings.loop = false;
                }

                settings.responsive = {
                    0: {
                        items: 2
                    },
                    670: {
                        items: 3
                    },
                    1200: {
                        items: parseInt(self.config.visible)
                    }
                };

            }else if(this.type === 'slider-default'){
                if(this.config.design_type === 1) settings.margin = 7;

                if(this.slides.length <= this.config.visible){
                    settings.loop = false;
                }

                settings.responsive = {
                    0: {
                        items: 2
                    },
                    670: {
                        items: 3
                    },
                    1200: {
                        items: parseInt(self.config.visible)
                    }
                };
            }

            $(this.getSliderTarget).owlCarousel(settings);
        },
        methods:{
            nav(direction){
                $(this.getSliderTarget).trigger(direction + '.owl.carousel', [300]);
            },
            pagination(number){
                $(event.target).siblings().removeClass('sliderPagination__item--active');
                $(this.getSliderTarget).trigger('to.owl.carousel', [number, 300]);
                $(event.target).addClass('sliderPagination__item--active');
            }
        }
    }
</script>
