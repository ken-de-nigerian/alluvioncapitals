<script setup lang="ts">
    import {Link} from "@inertiajs/vue3";
    const props = defineProps({
        title: String,
    })
</script>

<template>
    <ol class="breadcrumb pt-3 mt-2 mt-md-3 mb-md-4">
        <li class="breadcrumb-item"><Link href="/">Home</Link></li>
        <li class="breadcrumb-item active" aria-current="page">{{ props.title }}</li>
    </ol>
</template>
