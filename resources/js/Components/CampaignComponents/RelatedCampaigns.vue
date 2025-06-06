<script setup lang="ts">
    import {Link, usePage} from "@inertiajs/vue3";
    import {route} from "ziggy-js";
    import {onMounted, ref} from "vue";

    import Swiper from 'swiper';
    import { Navigation, Pagination } from 'swiper/modules';
    import 'swiper/css';
    import 'swiper/css/navigation';
    import 'swiper/css/pagination';

    import CampaignCard from "../../Components/CampaignComponents/CampaignCard.vue";

    const page = usePage();

    const props = defineProps({
        relatedCampaigns: Object,
    })

    const hasMultipleCampaigns = ref(props.relatedCampaigns?.length > 1);

    onMounted(() => {
        if (hasMultipleCampaigns.value) {
            new Swiper('.related-swiper', {
                modules: [Navigation, Pagination],
                slidesPerView: 1,
                spaceBetween: 24,
                loop: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    prevEl: '.btn-prev',
                    nextEl: '.btn-next',
                },
                breakpoints: {
                    550: {
                        slidesPerView: 2,
                    },
                    850: {
                        slidesPerView: 3,
                    },
                    1200: {
                        slidesPerView: 4,
                    },
                },
            });
        }
    });
</script>

<template>
    <section class="container py-2 py-sm-3 py-md-4 py-lg-5 my-xxl-3">
        <div class="d-flex align-items-start justify-content-between gap-4 pb-3 mb-2 mb-sm-3">
            <h2 class="mb-0">More causes you might love</h2>
            <div class="nav">
                <Link class="nav-link position-relative text-nowrap py-1 px-0" :href="route('campaigns.index')">
                    <span class="hover-effect-underline stretched-link me-1">View all</span>
                    <i class="ci-chevron-right fs-lg"></i>
                </Link>
            </div>
        </div>

        <!-- Swiper Slider (for multiple campaigns) -->
        <div v-if="hasMultipleCampaigns" class="related-swiper pb-5">
            <div class="swiper-wrapper">
                <div v-for="campaign in props.relatedCampaigns" :key="campaign.id" class="swiper-slide h-auto">
                    <CampaignCard :campaign="campaign" />
                </div>
            </div>
            <div class="swiper-pagination position-static mt-3 mt-lg-4"></div>
        </div>

        <!-- Grid Layout (for single campaign) -->
        <div v-else class="row">
            <div v-for="campaign in props.relatedCampaigns" :key="campaign.id" class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <CampaignCard :campaign="campaign" />
            </div>
        </div>
    </section>
</template>

<style>
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

    .related-swiper {
        margin-left: auto;
        margin-right: auto;
        position: relative;
        overflow: hidden;
        list-style: none;
        padding: 0;
        z-index: 1;
        display: block
    }
</style>
