<script setup lang="ts">
    import {Link} from "@inertiajs/vue3";
    import {usePage} from "@inertiajs/vue3";
    const page = usePage()
</script>

<template>
    <header class="adminuiux-header">
        <!-- Fixed navbar -->
        <nav class="navbar">
            <div class="container-fluid">
                <!-- logo -->
                <Link class="navbar-brand" href="/">
                    <img data-bs-img="light" src="/public/assets/img/logo-light.svg" alt="" />
                    <img data-bs-img="dark" src="/public/assets/img/logo.svg" alt="" />
                    <div class="">
                        <span class="h4">Investment<b>UX</b></span>
                        <p class="company-tagline">AdminUIUX HTML template</p>
                    </div>
                </Link>

                <div class="ms-auto"></div>
                <!-- right icons button -->
                <div class="ms-auto"></div>
            </div>
        </nav>
    </header>
</template>
