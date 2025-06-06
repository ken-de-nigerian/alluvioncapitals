<script setup lang="ts">
    import {Head, usePage, Link} from "@inertiajs/vue3";

    import Swiper from 'swiper';
    import { Navigation, Pagination } from 'swiper/modules';
    import 'swiper/css';
    import 'swiper/css/navigation';
    import 'swiper/css/pagination';

    import CampaignCard from "../../Components/CampaignComponents/CampaignCard.vue";
    import CategoryItems from "../../Components/CategoryComponents/CategoryItems.vue";

    import {onMounted} from "vue";
    import {route} from "ziggy-js";

    const page = usePage();

    const props = defineProps({
        categories: Object,
        campaigns: Object
    })

    onMounted(() => {
        new Swiper('.campaign-swiper', {
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
    });
</script>

<template>
    <Head :title="`${page.props.app.name} | Categories`" />
    <!-- Hero -->
    <section class="position-relative" style="margin-top: -76px; padding-top: 76px">
        <div class="container position-relative z-1 py-md-3 py-lg-4 py-xl-5">
            <!-- Title -->
            <div class="row pt-4 pt-sm-5 pb-4 pb-md-5 my-2 mt-sm-0 mb-sm-3 mb-md-0 mb-xl-2 mb-xxl-4">
                <div class="col-xl-10 pt-xxl-2">
                    <h1 class="display-3 fw-medium mb-md-2">Browse campaigns by category</h1>
                    <p class="lead mb-4">People around the world are raising money for what they are passionate about.</p>
                    <Link class="btn btn-primary btn-lg px-4 me-2 animate-slide-end" :href="route('campaigns.add.details')">
                        Start a Campaign
                        <i class="ci-send animate-target fs-base ms-2 me-n1"></i>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Background -->
        <div class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(90deg, rgba(203,201,233, .6) 0%, rgba(227,232,251, .6) 50%, rgba(255,224,244, .6) 100%)"></div>
        <div class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(90deg, rgba(51,51,59, .6) 0%, rgba(44,48,62, .6) 50%, rgba(57,43,52, .6) 100%)"></div>
    </section>

    <!-- Categories -->
    <CategoryItems :categories="categories" />

    <!-- Campaigns carousel -->
    <section class="container pb-5 my-xxl-3">
        <div class="d-flex align-items-center justify-content-between gap-4 pt-5 pb-3 mb-1 mb-sm-2 mb-md-3">
            <h2 class="h3 mb-0">Explore Campaigns</h2>

            <!-- Prev/next buttons -->
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-icon btn-outline-secondary animate-slide-start bg-body rounded-circle me-1 btn-prev" aria-label="Prev">
                    <i class="ci-chevron-left fs-lg animate-target"></i>
                </button>

                <button type="button" class="btn btn-icon btn-outline-secondary animate-slide-end bg-body rounded-circle btn-next" aria-label="Next">
                    <i class="ci-chevron-right fs-lg animate-target"></i>
                </button>
            </div>
        </div>

        <!-- Campaign Carousel -->
        <div v-if="props.campaigns.length > 0" class="campaign-swiper pb-5">
            <div class="swiper-wrapper">
                <div v-for="campaign in props.campaigns" :key="campaign.id" class="swiper-slide h-auto">
                    <CampaignCard :campaign="campaign" />
                </div>
            </div>
        </div>

        <!-- View all button -->
        <div class="text-center">
            <Link class="btn btn-lg btn-dark rounded-pill animate-slide-end" :href="route('campaigns.index')">
                Show More Campaigns
                <i class="ci-chevron-right animate-target fs-lg ms-1 me-n1"></i>
            </Link>
        </div>
    </section>
</template>

<style>
    .campaign-swiper {
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
