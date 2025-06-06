<template>
    <SearchResults />

    <PageHeader />

    <main class="content-wrapper">
        <div class="container pt-4 pt-lg-5 pb-5">
            <div class="row pt-sm-2 pt-md-3 pt-lg-0 pb-2 pb-sm-3 pb-md-4 pb-lg-5">
                <AdminSideBarNav />

                <slot />
            </div>
        </div>
    </main>

    <PageFooter />

    <OffcanvasMenuToggle />

    <FlashMessages />

    <ShareModal />
</template>

<script setup>
    import SearchResults from "../../Components/Search/SearchResults.vue";
    import PageHeader from "../../Components/Navigation/PageHeader.vue";
    import PageFooter from "../../Components/Navigation/PageFooter.vue";
    import AdminSideBarNav from "../../Components/Navigation/AdminSideBarNav.vue";
    import OffcanvasMenuToggle from "../../Components/Navigation/OffcanvasMenuToggle.vue";
    import FlashMessages from '../../Components/Utilities/FlashMessages.vue'
    import ShareModal from "../../Components/Modals/ShareModal.vue";
</script>
