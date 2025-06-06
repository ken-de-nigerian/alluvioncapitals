<template>
    <SearchResults />

    <PageHeader />

    <slot />

    <FlashMessages />
</template>

<script setup>
    import SearchResults from "../../Components/Search/SearchResults.vue";
    import PageHeader from "../../Components/Navigation/PageHeader.vue";
    import FlashMessages from "../../Components/Utilities/FlashMessages.vue";
</script>
