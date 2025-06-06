<script setup lang="ts">
    import {Link} from "@inertiajs/vue3";
    import {route} from "ziggy-js";

    const props = defineProps({
        campaign: Object,
    });
</script>

<template>
    <div class="d-flex justify-content-between gap-3 position-relative z-2 mb-3 mb-lg-4">
        <!-- Campaign title -->
        <h1 class="mb-0 fw-medium fs-3">{{ campaign.title }}</h1>

        <div class="d-flex gap-2">
            <!-- Campaign category -->
            <div class="d-none d-sm-flex align-items-center gap-2 me-2">
                <i class="ci-layers-2"></i>
                <Link class="text-capitalize text-light-emphasis" :href="route('categories.show', campaign.category.slug)">{{ campaign.category.slug }}</Link>
            </div>

            <!-- Share Campaign -->
            <div data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="Share Campaign">
                <button type="button" class="btn btn-icon btn-ghost btn-secondary animate-scale rounded-circle" data-bs-toggle="modal" data-bs-target=".share-modal" aria-label="Share Campaign" :data-url="campaign.show_route" :data-title="campaign.title">
                    <i class="ci-share-2 animate-target fs-base"></i>
                </button>
            </div>

            <!-- Report Campaign -->
            <button type="button" class="btn btn-icon btn-secondary animate-pulse rounded-circle" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="Report Campaign" aria-label="Report Campaign">
                <i class="ci-flag animate-target fs-base"></i>
            </button>
        </div>
    </div>

    <!-- Campaign meta visible on screens < 992px wide (lg breakpoint) -->
    <div class="d-lg-none mb-4 mt-4">
        <div class="d-flex flex-wrap gap-2 gap-sm-3 fs-sm text-nowrap">
            <!-- Campaign category -->
            <div class="d-flex align-items-center gap-2 me-2">
                <i class="ci-layers-2" style="margin-bottom: -5px;"></i>
                <Link class="text-capitalize text-white" :href="route('categories.show', campaign.category.slug)">{{ campaign.category.slug }}</Link>
            </div>
        </div>
    </div>
</template>
