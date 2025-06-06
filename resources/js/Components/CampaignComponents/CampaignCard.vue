<script setup lang="ts">
    import {route} from "ziggy-js";
    import {Link} from "@inertiajs/vue3";
    import {usePage} from "@inertiajs/vue3";
    import {onMounted, onUnmounted, ref} from "vue";

    import GLightbox from "glightbox";
    import 'glightbox/dist/css/glightbox.min.css';

    const page = usePage();

    const props = defineProps({
        campaign: Object,
    })

    const getVideoHost = (url: string | null) => {
        if (!url) return null;
        if (url.includes('youtube.com') || url.includes('youtu.be')) return 'youtube';
        if (url.includes('vimeo.com')) return 'vimeo';
        return null;
    };

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG');
    };

    let lightbox: any;
    const isModalOpen = ref(false);

    const handleLightboxOpen = () => {
        isModalOpen.value = true;
    };

    const handleLightboxClose = () => {
        isModalOpen.value = false;
    };

    onMounted(() => {
        lightbox = GLightbox({
            selector: '[data-glightbox]',
            plyr: {
                config: {
                    youtube: {
                        noCookie: true,
                        origin: window.location.origin
                    },
                    vimeo: {
                        byline: false,
                        portrait: false,
                        title: false,
                        transparent: false
                    }
                }
            }
        });

        document.addEventListener('glightbox_open', handleLightboxOpen);
        document.addEventListener('glightbox_close', handleLightboxClose);
    });

    onUnmounted(() => {
        if (lightbox) {
            lightbox.destroy();
        }

        document.removeEventListener('glightbox_open', handleLightboxOpen);
        document.removeEventListener('glightbox_close', handleLightboxClose);
    });
</script>

<template>
    <div class="card h-100 animate-underline hover-effect-opacity hover-effect-scale rounded-4 overflow-hidden">
        <div class="card-img-top position-relative bg-body-tertiary overflow-hidden">
            <!-- Featured badges -->
            <div v-if="campaign.featured === 'yes'" class="d-flex flex-column gap-2 align-items-start position-absolute top-0 start-0 z-1 pt-1 pt-sm-0 ps-1 ps-sm-0 mt-2 mt-sm-3 ms-2 ms-sm-3">
                <span class="badge text-bg-info d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="This campaign has been verified for authenticity.">
                    Verified
                    <i class="ci-shield ms-1"></i>
                </span>
                <span class="badge text-bg-warning" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="This campaign is featured for its popularity or importance.">
                    Featured
                </span>
            </div>

            <!-- Campaign image/video -->
            <template v-if="!campaign.campaign_video">
                <Link :href="campaign.show_route" class="ratio d-block hover-effect-target" style="--cz-aspect-ratio: calc(220 / 304 * 100%)">
                    <img :src="campaign.first_image" :alt="campaign.title" loading="lazy" class="w-100 h-100 object-fit-cover">
                </Link>
            </template>

            <template v-else>
                <div v-if="getVideoHost(campaign.campaign_video) === 'youtube'" class="ratio d-block hover-effect-target" style="--cz-aspect-ratio: calc(220 / 304 * 100%)">
                    <a :href="campaign.campaign_video" class="hover-effect-opacity ratio d-flex overflow-hidden" style="--cz-aspect-ratio: calc(600 / 856 * 100%)" data-glightbox data-gallery="video" :inert="isModalOpen" aria-label="Play YouTube video">
                        <div class="position-absolute d-flex flex-column align-items-center top-0 start-0 w-100 h-100 z-2 text-white p-4">
                            <span class="btn btn-icon btn-lg position-absolute top-50 translate-middle-y bg-white text-dark rounded-circle">
                                <i class="ci-play-filled"></i>
                            </span>
                        </div>
                        <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
                        <img :src="campaign.first_image" :alt="campaign.title" loading="lazy" class="w-100 h-100 object-fit-cover">
                    </a>
                </div>

                <div v-else-if="getVideoHost(campaign.campaign_video) === 'vimeo'" class="ratio d-block hover-effect-target" style="--cz-aspect-ratio: calc(220 / 304 * 100%)">
                    <a :href="campaign.campaign_video" class="hover-effect-opacity ratio d-flex overflow-hidden" style="--cz-aspect-ratio: calc(600 / 856 * 100%)" data-glightbox data-gallery="video" :inert="isModalOpen" aria-label="Play Vimeo video">
                        <div class="position-absolute d-flex flex-column align-items-center top-0 start-0 w-100 h-100 z-2 text-white p-4">
                            <span class="btn btn-icon btn-lg position-absolute top-50 translate-middle-y bg-white text-dark rounded-circle">
                                <i class="ci-play-filled"></i>
                            </span>
                        </div>
                        <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
                        <img :src="campaign.first_image" :alt="campaign.title" loading="lazy" class="w-100 h-100 object-fit-cover">
                    </a>
                </div>
            </template>

            <!-- Share button -->
            <div class="position-absolute top-0 end-0 z-2 hover-effect-target opacity-0 pt-1 pt-sm-0 pe-1 pe-sm-0 mt-2 mt-sm-3 me-2 me-sm-3" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="Share Campaign">
                <button type="button" data-bs-toggle="modal" data-bs-target=".share-modal" class="btn btn-sm btn-icon btn-secondary border-0 rounded-circle animate-pulse" :data-url="campaign.show_route" :data-title="campaign.title" aria-label="Share this campaign">
                    <i class="ci-share-2 animate-target fs-sm"></i>
                </button>
            </div>
        </div>

        <!-- Card body content -->
        <div class="card-body p-3">
            <div class="d-flex min-w-0 justify-content-between gap-2 gap-sm-3 mb-3">
                <h3 class="nav min-w-0 mb-0">
                    <Link class="nav-link text-truncate p-0" :href="campaign.show_route" :aria-label="campaign.title">
                        <span class="text-truncate animate-target">{{ campaign.title }}</span>
                    </Link>
                </h3>
                <div class="h6 mb-0">₦{{ formatCurrency(campaign.goal) }}</div>
            </div>

            <!-- Success progress bar -->
            <div class="fs-sm mb-2">₦{{ formatCurrency(campaign.funds_raised) }} raised</div>
            <div class="progress" role="progressbar" :aria-label="`${campaign.progress}% of goal reached`" :aria-valuenow="campaign.progress" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
                <div class="progress-bar bg-success rounded-pill" :style="{ width: `${campaign.progress}%` }"></div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                <div class="nav align-items-center gap-1 fs-xs">
                    <ul class="list-unstyled flex-row flex-wrap justify-content-end fs-sm mb-2">
                        <li v-if="campaign.status_badge.text !== 'Completed' && campaign.days_left_text !== 'Expired'" class="d-flex align-items-center me-2 me-md-3">
                            <i class="ci-clock fs-base me-1"></i>
                            {{ campaign.days_left_text }}
                        </li>

                        <li v-else class="d-flex align-items-center me-2 me-md-3">
                            <i class="ci-clock fs-base me-1"></i>
                            Expired
                        </li>
                    </ul>
                </div>
                <div class="fs-xs text-body-secondary">
                    <Link class="nav-link" :href="route('categories.show', campaign.category.slug)" :aria-label="`View ${campaign.category.name} category`">
                        <span class="text-truncate animate-target">{{ campaign.category.name }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
