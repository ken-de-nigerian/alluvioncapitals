<script setup>
import { Head } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Page Not Found" />
    <div class="min-h-screen flex flex-col justify-center items-center">
        <h1 class="text-6xl font-bold text-gray-700">404</h1>
        <p class="text-xl mt-4">Sorry, the page you are looking for could not be found.</p>
        <a href="/" class="mt-6 text-blue-600 underline">Return to homepage</a>
    </div>
</template>
