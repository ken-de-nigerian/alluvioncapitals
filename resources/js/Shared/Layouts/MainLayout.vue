<template>
    <SearchResults />

    <PageHeader />

    <main class="content-wrapper">
        <slot />
    </main>

    <PageFooter />

    <FlashMessages />

    <ShareModal />
</template>

<script setup lang="ts">
    import SearchResults from "../../Components/Search/SearchResults.vue";
    import PageHeader from "../../Components/Navigation/PageHeader.vue";
    import PageFooter from "../../Components/Navigation/PageFooter.vue";
    import FlashMessages from "../../Components/Utilities/FlashMessages.vue";
    import ShareModal from "../../Components/Modals/ShareModal.vue";
</script>
