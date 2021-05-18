<template>
    <div class="module module--inContent">
        <div class="moduleProducts js-container-slider">
            <div class="moduleTitle moduleTitle--inContent">
                {{$parent.config.title}}
                <div class="moduleProducts__nav">
                    <a class="sliderTo sliderTo--inContent sliderTo--left js-slideshow-nav" @click="$parent.nav('prev')">
                        <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--left"></i>
                    </a>
                    <a class="sliderTo sliderTo--inContent sliderTo--right js-slideshow-nav" @click="$parent.nav('next')">
                        <i class="icon sb-icon-down-arrow sliderTo__icon sliderTo__icon--right"></i>
                    </a>
                </div>
            </div>
            <div class="owl-carousel moduleProducts__content moduleProducts__content--inContent" :id="($parent.sliderName+'-'+$parent.sliderId)">
                <slider-product v-for="product in products" :key="product.id" :product="product"></slider-product>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "sliderProducts",
        props: ['products'],
        components: {
            'slider-product': require('./../components/sliderProduct.vue').default
        },
        methods:{
            showProductRating: function (rating) {
                let html = '';

                var i = 0;
                while (i < 5) {
                    if (i < rating) html += '<i class="icon sb-icon-star rating--fill"></i>';
                    else {
                        html += '<i class="icon sb-icon-star rating--null"></i>';
                        i++;
                    }
                }

                return html;
            }
        }
    }
</script>

<style scoped>

</style>
