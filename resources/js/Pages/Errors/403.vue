<script setup>
import { Head } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Unauthorized" />
    <div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
        <div>
            <h1 class="text-4xl font-bold text-red-600 mb-4">403</h1>
            <h2 class="text-2xl font-semibold">Unauthorized</h2>
            <p class="text-gray-700 mt-2">You don't have permission to access this page.</p>
            <a href="/" class="mt-4 inline-block text-blue-500 hover:underline">Go back home</a>
        </div>
    </div>
</template>
