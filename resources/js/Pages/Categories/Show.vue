<script setup lang="ts">
    import {Head, usePage, Link} from "@inertiajs/vue3";
    import { defineAsyncComponent } from 'vue';

    const FilterLinks = defineAsyncComponent(() => import('../../Components/CampaignComponents/FilterLinks.vue'));
    const CampaignSkeleton = defineAsyncComponent(() => import('../../Components/CampaignComponents/CampaignSkeleton.vue'));
    const NotFoundSvg  = defineAsyncComponent(() => import('../../Components/CampaignComponents/NotFoundSvg.vue'));

    import CampaignCard from "../../Components/CampaignComponents/CampaignCard.vue";
    import {route} from "ziggy-js";

    const page = usePage();

    const props = defineProps({
        category: Object,
        campaigns: Object
    })
</script>

<template>
    <Head :title="`${page.props.app.name} | ${category.name}`" />

    <!-- Hero -->
    <section class="position-relative mb-5" style="margin-top: -76px; padding-top: 76px; min-height: 500px;">
        <div class="container position-relative z-1 h-100">
            <div class="row align-items-center h-100 g-4 py-3 py-md-4 py-lg-5">
                <!-- Text Content (Left) -->
                <div class="col-lg-6 mb-4 mb-lg-0 pe-lg-5">
                    <h1 class="display-4 fw-medium mb-3">Discover {{ category.name.toLowerCase() }} campaigns</h1>
                    <p class="lead mb-4">Help others by donating to their campaign, or start one for someone you care about.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <Link class="btn btn-primary btn-lg px-4 animate-slide-end" :href="route('campaigns.add.details')">
                            Start a Campaign
                            <i class="ci-send animate-target fs-base ms-2 me-n1"></i>
                        </Link>
                    </div>
                </div>

                <!-- Image (Right) -->
                <div class="col-lg-6 h-100">
                    <div class="position-relative h-100" style="min-height: 300px;">
                        <img :src="category.image" :alt="category.name" class="img-fluid rounded-4 shadow-lg w-100 h-100 object-fit-cover" style="max-height: 500px;" loading="lazy">
                    </div>
                </div>
            </div>
        </div>

        <!-- Background -->
        <div class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(90deg, rgba(203,201,233, .6) 0%, rgba(227,232,251, .6) 50%, rgba(255,224,244, .6) 100%)"></div>
        <div class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(90deg, rgba(51,51,59, .6) 0%, rgba(44,48,62, .6) 50%, rgba(57,43,52, .6) 100%)"></div>
    </section>

    <div class="container pb-5 mb-2 mb-sm-3 mb-lg-4 mb-xl-5">
        <!-- Page title -->
        <h1 class="h3 pb-2">{{ category.name }} Campaigns</h1>

        <!-- Filter nav links container -->
        <FilterLinks />

        <!-- Loading state -->
        <template v-if="!campaigns">
            <!-- Campaign skeleton -->
            <CampaignSkeleton />
        </template>

        <!-- Loaded state -->
        <template v-else>
            <!-- Campaigns grid or no data message -->
            <template v-if="campaigns?.data && Array.isArray(campaigns.data) && campaigns.data.length > 0">
                <div class="row g-3 g-sm-4 g-lg-3 g-xl-4">
                    <!-- Campaign -->
                    <div v-for="campaign in campaigns.data" :key="campaign.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <CampaignCard :campaign="campaign" />
                    </div>
                </div>
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
