<script setup lang="ts">
    import {onMounted, onUnmounted, ref} from "vue";
    import GLightbox from "glightbox";
    import 'glightbox/dist/css/glightbox.min.css';

    const props = defineProps({
        campaign: {
            type: Object,
            required: true,
        },
    });

    const getVideoHost = (url: string | null) => {
        if (!url) return null;
        if (url.includes('youtube.com') || url.includes('youtu.be')) return 'youtube';
        if (url.includes('vimeo.com')) return 'vimeo';
        return null;
    };

    let lightbox: any;
    const isModalOpen = ref(false);

    // Create handler functions so we can properly remove them later
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
    <!-- Youtube Video display -->
    <template v-if="getVideoHost(campaign.campaign_video) === 'youtube'">
        <div class="ratio d-block mt-1 mb-5" style="--cz-aspect-ratio: calc(176 / 304 * 100%)">
            <a class="hover-effect-opacity ratio d-flex rounded-4 overflow-hidden" :href="campaign.campaign_video" style="--cz-aspect-ratio: calc(480 / 856 * 100%)" data-glightbox data-gallery="video" :inert="isModalOpen" aria-label="Play YouTube video">
                <div class="position-absolute d-flex flex-column align-items-center top-0 start-0 w-100 h-100 z-2 text-white p-4">
                    <span class="btn btn-icon btn-lg position-absolute top-50 translate-middle-y bg-white text-dark rounded-circle">
                        <i class="ci-play-filled"></i>
                    </span>
                </div>
                <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
                <img :src="`${campaign.first_image}`" :alt="campaign.title" loading="lazy" />
            </a>
        </div>
    </template>

    <!-- Vimeo Video display -->
    <template v-else>
        <div class="ratio d-block mt-1 mb-5" style="--cz-aspect-ratio: calc(176 / 304 * 100%)">
            <a class="hover-effect-opacity ratio d-flex rounded-4 overflow-hidden" :href="campaign.campaign_video" style="--cz-aspect-ratio: calc(480 / 856 * 100%)" data-glightbox data-gallery="video" :inert="isModalOpen" aria-label="Play Vimeo video">
                <div class="position-absolute d-flex flex-column align-items-center top-0 start-0 w-100 h-100 z-2 text-white p-4">
                    <span class="btn btn-icon btn-lg position-absolute top-50 translate-middle-y bg-white text-dark rounded-circle">
                        <i class="ci-play-filled"></i>
                    </span>
                </div>
                <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
                <img :src="`${campaign.first_image}`" :alt="campaign.title" loading="lazy" />
            </a>
        </div>
    </template>
</template>
