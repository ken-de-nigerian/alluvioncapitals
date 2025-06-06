<script setup>
    import {usePage} from "@inertiajs/vue3";
    const page = usePage()
</script>

<template>
    <div class="d-none d-lg-block w-100 py-4 ms-auto" style="max-width: 1034px">
        <div class="d-flex flex-column justify-content-end h-100 rounded-5 overflow-hidden">
            <span class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
            <span class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
            <div class="ratio position-relative z-2" style="--cz-aspect-ratio: calc(1030 / 1032 * 100%)">
                <img src="/public/assets/images/impact-home-hero.svg" alt="Girl" loading="lazy">
            </div>
        </div>
    </div>
</template>
