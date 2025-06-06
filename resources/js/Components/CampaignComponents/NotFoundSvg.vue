<template>
    <img :src="notFoundImage" alt="Not Found" class="w-75 w-md-50" loading="lazy" />
</template>

<script setup lang="ts">
    import notFoundImage from '../../../../public/assets/not-found.webp';
</script>

<style>
@media (min-width: 768px) {
    .w-md-50 {
        width: 50% !important;
    }
}
</style>
