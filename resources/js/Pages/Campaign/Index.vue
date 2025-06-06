<script setup lang="ts">
    import { Head, usePage } from "@inertiajs/vue3";
    import { defineAsyncComponent } from 'vue';

    const NotFoundSvg  = defineAsyncComponent(() => import('../../Components/CampaignComponents/NotFoundSvg.vue'));
    const FilterLinks = defineAsyncComponent(() => import('../../Components/CampaignComponents/FilterLinks.vue'));

    import CampaignSkeleton from "../../Components/CampaignComponents/CampaignSkeleton.vue";
    import BreadCrumbs from "../../Components/CampaignComponents/BreadCrumbs.vue";
    import CampaignPagination from "../../Components/CampaignComponents/CampaignPagination.vue";
    import MainFilter from "../../Components/CampaignComponents/MainFilter.vue";
    import CampaignCard from "../../Components/CampaignComponents/CampaignCard.vue";

    const page = usePage();

    const props = defineProps({
        campaigns: {
            type: Object,
            default: null
        }
    });
</script>

<template>
    <Head :title="`${page.props.app.name} | Campaigns`" />

    <div class="container pb-5 mb-2 mb-sm-3 mb-lg-4 mb-xl-5">
        <!-- Breadcrumb -->
        <BreadCrumbs :title="'Campaigns'" />

        <!-- Page title -->
        <h1 class="h3 pb-2">Campaigns</h1>

        <!-- Filter nav links container -->
        <FilterLinks />

        <!-- Loading state -->
        <template v-if="!campaigns">
            <!-- Filter skeleton -->
            <section class="pt-0 pb-4">
                <div class="row g-3 g-sm-4 g-lg-3 g-xl-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="skeleton-shimmer bg-light rounded" style="width: 120px; height: 38px;"></div>
                            <div class="skeleton-shimmer bg-light rounded" style="width: 200px; height: 20px;"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Campaign skeleton -->
            <CampaignSkeleton />
        </template>

        <!-- Loaded state -->
        <template v-else>
            <!-- Campaigns grid or no data message -->
            <template v-if="campaigns?.data && Array.isArray(campaigns.data) && campaigns.data.length > 0">
                <!-- Main filter container -->
                <MainFilter :campaigns="campaigns" />

                <div class="row g-3 g-sm-4 g-lg-3 g-xl-4">
                    <!-- Campaign -->
                    <div v-for="campaign in campaigns.data" :key="campaign.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <CampaignCard :campaign="campaign" />
                    </div>
                </div>

                <CampaignPagination :campaigns="campaigns" />
            </template>

            <template v-else>
                <div class="d-flex flex-column align-items-center justify-content-center py-5 pb-0 text-center">
                    <NotFoundSvg />
                    <h3 class="h4 mb-3">No Campaigns Available</h3>
                    <p class="text-muted mb-4">Looks like there are no campaigns at the moment. Please check back later or start your own!</p>
                    <a href="/" class="btn btn-primary">
                        Back to Homepage
                    </a>
                </div>
            </template>
        </template>
    </div>
</template>

<style scoped>
    .skeleton-shimmer {
        background: linear-gradient(90deg, #999999 25%, #e0e0e0 50%, #999999 75%);
        background-size: 200% 100%;
        animation: shimmer 2.5s infinite;
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }
        100% {
            background-position: 200% 0;
        }
    }

    [inert] {
        pointer-events: none;
        cursor: default;
    }

    [inert], [inert] * {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }

    a:focus, button:focus {
        outline: 2px solid currentColor;
        outline-offset: 2px;
    }
</style>
