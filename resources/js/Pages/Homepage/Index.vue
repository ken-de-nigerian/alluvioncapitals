<script setup lang="ts">
    import { Head, Link, usePage } from "@inertiajs/vue3";
    import { route } from "ziggy-js";
    import CampaignCard from "../../Components/CampaignComponents/CampaignCard.vue";
    import {onMounted} from "vue";

    import Swiper from 'swiper';
    import { Navigation, Pagination } from 'swiper/modules';
    import 'swiper/css';
    import 'swiper/css/navigation';
    import 'swiper/css/pagination';

    const page = usePage();

    const props = defineProps({
        categories: Object,
        top_fundraising_causes: Object,
        latest_campaigns: Object
    });

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
    <Head :title="`${page.props.app.name} | Support Causes That Matter`" />

    <!-- Hero -->
    <section class="position-relative pb-5" style="margin-top: -76px; padding-top: 76px">
        <div class="container position-relative z-1 py-md-3 py-lg-4 py-xl-5">

            <!-- Title -->
            <div class="row pt-4 pt-sm-5 pb-4 pb-md-5 my-2 mt-sm-0 mb-sm-3 mb-md-0 mb-xl-2 mb-xxl-4">
                <div class="col-xl-10 pt-xxl-2">
                    <h1 class="display-3 fw-medium mb-md-2">Empower Change with Your Support</h1>
                    <p class="fs-lg mb-4">Discover impactful fundraising campaigns and causes that need your help to make a difference.</p>
                    <Link class="btn btn-primary btn-lg animate-slide-end" :href="route('campaigns.add.details')">
                        Start a Campaign
                        <i class="ci-send animate-target fs-base ms-2 me-n1"></i>
                    </Link>
                </div>
            </div>

            <!-- Categories -->
            <template v-if="!props.categories">
                <!-- Categories Skeleton Loader with Shimmer -->
                <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-4 g-lg-3 g-xl-4 pb-2 mb-4">
                    <!-- Repeat skeleton items -->
                    <div v-for="i in 6" :key="`category-skeleton-${i}`" class="col">
                        <div class="vstack position-relative rounded-4 overflow-hidden shimmer-container">
                            <!-- Image placeholder -->
                            <div class="ratio overflow-hidden" style="--cz-aspect-ratio: calc(130 / 196 * 100%)">
                                <div class="skeleton-shimmer"></div>
                            </div>

                            <!-- Text placeholder -->
                            <div class="position-relative text-center py-3">
                                <div class="d-inline-block rounded-3 skeleton-shimmer" style="width: 80px; height: 24px;"></div>
                            </div>

                            <!-- Background layers -->
                            <span class="position-absolute top-0 start-0 w-100 h-100 d-none-dark"></span>
                            <span class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="opacity: .07"></span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Loaded state -->
            <template v-else>
                <!-- Categories -->
                <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-4 g-lg-3 g-xl-4 pb-2 mb-4">
                    <div v-for="category in props.categories" :key="category.id" class="col">
                        <Link class="vstack position-relative animate-underline hover-effect-scale rounded-4 overflow-hidden text-dark-emphasis fw-medium text-decoration-none" :href="route('categories.show', category.slug)">
                            <div class="ratio z-2 overflow-hidden" style="--cz-aspect-ratio: calc(130 / 196 * 100%)">
                                <img :src="category.image" class="hover-effect-target" alt="Cause Image" loading="lazy">
                            </div>

                            <div class="position-relative z-2 text-center py-3">
                                <div class="animate-target d-inline">{{ category.name }}</div>
                            </div>

                            <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none-dark"></span>
                            <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none d-block-dark" style="opacity: .07"></span>
                        </Link>
                    </div>
                </div>
            </template>

            <!-- Explore Top Fundraising Causes -->
            <div class="d-sm-flex align-items-center gap-4 pb-2 pb-sm-4">
                <h2 class="fs-base fw-medium mb-sm-0">Browse by most popular categories</h2>
                <div class="d-flex flex-wrap gap-4">
                    <template v-for="cause in props.top_fundraising_causes" :key="cause.id">
                        <Link :href="route('categories.show', cause.slug)" class="text-light-emphasis fs-base">{{ cause.name }}</Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Background -->
        <div class="position-absolute top-0 start-0 w-100 h-100 d-none-dark" style="background: linear-gradient(90deg, rgba(203,201,233, .6) 0%, rgba(227,232,251, .6) 50%, rgba(255,224,244, .6) 100%)"></div>
        <div class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark" style="background: linear-gradient(90deg, rgba(51,51,59, .6) 0%, rgba(44,48,62, .6) 50%, rgba(57,43,52, .6) 100%)"></div>
    </section>

    <!-- How it Works -->
    <section class="container py-2 py-sm-3 py-md-4 py-lg-5 mb-xxl-3">
        <h2 class="pt-5">How It Works</h2>
        <p class="fs-lg pb-3">Join our mission to make a difference in just five simple steps. Your support can create lasting change!</p>

        <!-- Row of items that turns into carousel on screens < 800px wide -->
        <div class="campaign-swiper pb-5">
            <div class="swiper-wrapper">
                <!-- Item -->
                <div class="swiper-slide card">
                    <div class="card-body">
                        <span class="text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                            </svg>
                        </span>
                        <h3 class="h5 pt-3 pt-md-4 mb-2">Discover Our Cause</h3>
                        <p class="mb-0">Explore our mission to uplift communities sustainably.</p>
                    </div>
                </div>

                <!-- Item -->
                <div class="swiper-slide card">
                    <div class="card-body">
                        <span class="text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-balloon-heart" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="m8 2.42-.717-.737c-1.13-1.161-3.243-.777-4.01.72-.35.685-.451 1.707.236 3.062C4.16 6.753 5.52 8.32 8 10.042c2.479-1.723 3.839-3.29 4.491-4.577.687-1.355.587-2.377.236-3.061-.767-1.498-2.88-1.882-4.01-.721zm-.49 8.5c-10.78-7.44-3-13.155.359-10.063q.068.062.132.129.065-.067.132-.129c3.36-3.092 11.137 2.624.357 10.063l.235.468a.25.25 0 1 1-.448.224l-.008-.017c.008.11.02.202.037.29.054.27.161.488.419 1.003.288.578.235 1.15.076 1.629-.157.469-.422.867-.588 1.115l-.004.007a.25.25 0 1 1-.416-.278c.168-.252.4-.6.533-1.003.133-.396.163-.824-.049-1.246l-.013-.028c-.24-.48-.38-.758-.448-1.102a3 3 0 0 1-.052-.45l-.04.08a.25.25 0 1 1-.447-.224l.235-.468ZM6.013 2.06c-.649-.18-1.483.083-1.85.798-.131.258-.245.689-.08 1.335.063.244.414.198.487-.043.21-.697.627-1.447 1.359-1.692.217-.073.304-.337.084-.398"/>
                            </svg>
                        </span>
                        <h3 class="h5 pt-3 pt-md-4 mb-2">Make a Donation</h3>
                        <p class="mb-0">Give any amount to fund education and healthcare.</p>
                    </div>
                </div>

                <!-- Item -->
                <div class="swiper-slide card">
                    <div class="card-body">
                        <span class="text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-share" viewBox="0 0 16 16">
                                <path d="M13.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.5 2.5 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5m-8.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m11 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3"/>
                            </svg>
                        </span>
                        <h3 class="h5 pt-3 pt-md-4 mb-2">Share the Campaign</h3>
                        <p class="mb-0">Share on social media to inspire others to join.</p>
                    </div>
                </div>

                <!-- Item -->
                <div class="swiper-slide card">
                    <div class="card-body">
                        <span class="text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                            </svg>
                        </span>
                        <h3 class="h5 pt-3 pt-md-4 mb-2">Join Our Community</h3>
                        <p class="mb-0">Join events or volunteer to connect with supporters.</p>
                    </div>
                </div>

                <!-- Item -->
                <div class="swiper-slide card">
                    <div class="card-body">
                        <span class="text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                            </svg>
                        </span>
                        <h3 class="h5 pt-3 pt-md-4 mb-2">Track Your Impact</h3>
                        <p class="mb-0">See how your contributions create lasting change.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="container pt-2 pt-sm-2 pt-md-2 pt-lg-2 pb-3 my-xxl-3">
        <div class="row mb-4">
            <div class="col-md-5 order-md-2 mb-4 mb-md-0">
                <div class="position-relative h-100 bg-body-tertiary rounded-5 overflow-hidden">
                    <div class="d-none d-md-block" style="height: 440px"></div>
                    <div class="d-none d-sm-block d-md-none" style="height: 350px"></div>
                    <div class="d-sm-none" style="height: 250px"></div>
                    <img src="/public/assets/images/impact-home-hero.svg" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" alt="Community Impact">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-end justify-content-end p-3 p-sm-4">
                        <Link class="btn btn-lg btn-primary stretched-link rounded-pill" :href="route('campaigns.index')">
                            <i class="ci-heart fs-lg me-2 ms-n1"></i>
                            Donate Now
                        </Link>
                    </div>
                </div>
            </div>

            <div class="col-md-7 order-md-1">
                <div class="d-flex align-items-center h-100 bg-body-tertiary rounded-5 p-4 p-xl-5">
                    <div class="p-sm-3 p-xxl-2">
                        <h2 class="mb-lg-4">Transform Lives with Your Support</h2>
                        <p class="fs-lg pb-sm-2 pb-lg-3">Your donation can make a lasting difference in our community. Every contribution helps provide essential resources, support families in need, and create opportunities for a brighter future. Together, we can build a stronger, more connected world.</p>
                        <div class="d-flex flex-column flex-sm-row gap-2 gap-lg-3">
                            <div class="d-flex align-items-center text-body-emphasis bg-body rounded-pill shadow px-3" style="padding: 10px 0">
                                <svg class="flex-shrink-0 me-2" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor">
                                    <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zM4.5 7.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm7 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM8 12a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"></path>
                                </svg>
                                <div class="fs-sm fw-medium">Empower communities</div>
                            </div>
                            <div class="d-flex align-items-center text-body-emphasis bg-body rounded-pill shadow px-3" style="padding: 10px 0">
                                <i class="ci-star me-2"></i>
                                <div class="fs-sm fw-medium">Create lasting impact</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest campaigns carousel -->
    <section class="container pb-5 my-xxl-3">
        <div class="d-flex align-items-center justify-content-between gap-4 pt-5 pb-3 mb-1 mb-sm-2 mb-md-3">
            <h2 class="h3 mb-0">These campaigns need your help</h2>

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
        <div v-if="props.latest_campaigns.length > 0" class="campaign-swiper pb-5">
            <div class="swiper-wrapper">
                <div v-for="campaign in props.latest_campaigns" :key="campaign.id" class="swiper-slide h-auto">
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

