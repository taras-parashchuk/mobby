<template>
    <div class="owl-carousel" :id="($parent.sliderName+'-'+$parent.sliderId)">
        <div v-for="article in articles" class="moduleNews__item moduleNewsItem">
            <a :href="article.href">
                <img :src="article.image" alt="article.translate.name"
                     class="img-responsive">
            </a>
            <div class="moduleNewsItem__date">
                <span>
                    {{article.date_added}}
                </span>
            </div>
            <a :href="article.href" class="moduleNewsItem__title">
                {{article.translate.name}}
            </a>
        </div>
    </div>
</template>

<script>
    export default {
        name: "sliderArticles",
        props: ['articles'],
    }
</script>

<style scoped>

</style>