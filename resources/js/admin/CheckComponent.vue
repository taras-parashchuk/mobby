<template>
    <div class="listData__checked">
        <icon v-if="checked" class="icon" icon="check-mark"></icon>
    </div>
</template>

<script>
    export default {
        name: "CheckComponent",
        props: ['checked'],
    }
</script>