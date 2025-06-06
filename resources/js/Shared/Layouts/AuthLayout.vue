<template>
    <main class="content-wrapper w-100 px-3 ps-lg-5 pe-lg-4 mx-auto" style="max-width: 1920px">
        <slot />

        <FlashMessages />
    </main>
</template>

<script setup>
    import FlashMessages from '../../Components/Utilities/FlashMessages.vue'
    import {onMounted} from "vue";
    import initThemeSwitcher from "../../Components/Utilities/ThemeSwitcher.js";

    onMounted(() => {
        initThemeSwitcher();
    });
</script>
