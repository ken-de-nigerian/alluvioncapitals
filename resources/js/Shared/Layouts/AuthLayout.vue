<template>
    <!-- Pageloader -->
    <Preloader />

    <main class="flex-shrink-0 pt-0 h-100">
        <div class="container-fluid">
            <div class="auth-wrapper">
                <!--Page body-->
                <slot />

                <FlashMessages />
            </div>
        </div>
    </main>
</template>

<script setup>
    import FlashMessages from '@/Components/Utilities/FlashMessages.vue'
    import Preloader from "@/Components/Navigation/Preloader.vue";
</script>
