<script setup lang="ts">
    import { onMounted } from 'vue'
    import Swiper from 'swiper'
    import { Navigation, Thumbs } from 'swiper/modules'
    import 'swiper/css'
    import 'swiper/css/navigation'
    import 'swiper/css/thumbs'

    const props = defineProps({
        campaign: {
            type: Object,
            required: true,
        },
        images: {
            type: Array,
            default: null,
        }
    })

    onMounted(() => {
        if (props.images?.length > 1) {
            // Initialize thumbnail swiper first
            const thumbsSwiper = new Swiper('#thumbs', {
                loop: true,
                spaceBetween: 16,
                slidesPerView: 3,
                watchSlidesProgress: true,
                breakpoints: {
                    340: { slidesPerView: 4 },
                    500: { slidesPerView: 5 },
                    600: { slidesPerView: 6 },
                    768: { slidesPerView: 4 },
                    992: { slidesPerView: 5 },
                    1200: { slidesPerView: 5 }
                }
            })

            // Initialize main swiper
            new Swiper('.main-swiper', {
                modules: [Navigation, Thumbs],
                spaceBetween: 16,
                loop: true,
                navigation: {
                    prevEl: '.btn-prev',
                    nextEl: '.btn-next'
                },
                thumbs: {
                    swiper: thumbsSwiper
                }
            })
        }
    })
</script>

<template>
    <!-- Multiple images (Swiper slider) -->
    <div v-if="images?.length > 1">
        <div class="swiper main-swiper hover-effect-opacity">
            <div class="swiper-wrapper">
                <div v-for="(image, index) in images" :key="index" class="swiper-slide">
                    <div class="bg-body-tertiary rounded overflow-hidden" style="--fn-aspect-ratio: calc(480 / 856 * 100%)">
                        <img :src="image" alt="Campaign Image" loading="lazy" />
                    </div>
                </div>
            </div>

            <!-- Prev / next buttons -->
            <div class="position-absolute top-50 start-0 z-2 translate-middle-y ms-3 ms-sm-4 hover-effect-target opacity-0">
                <button type="button" class="btn btn-prev btn-icon btn-secondary bg-body border-0 rounded-circle animate-slide-start" aria-label="Prev" data-bs-theme="light">
                    <i class="ci-chevron-left fs-lg animate-target"></i>
                </button>
            </div>

            <div class="position-absolute top-50 end-0 z-2 translate-middle-y me-3 me-sm-4 hover-effect-target opacity-0">
                <button type="button" class="btn btn-next btn-icon btn-secondary bg-body border-0 rounded-circle animate-slide-end" aria-label="Next" data-bs-theme="light">
                    <i class="ci-chevron-right fs-lg animate-target"></i>
                </button>
            </div>
        </div>

        <!-- Thumbnails slider -->
        <div class="swiper swiper-thumbs pt-2 mt-1 mb-5" id="thumbs">
            <div class="swiper-wrapper">
                <div v-for="(image, index) in images" :key="index" class="swiper-slide swiper-thumb overflow-hidden">
                    <div class="bg-body-tertiary" style="--fn-aspect-ratio: calc(115 / 156 * 100%)">
                        <img :src="image" class="swiper-thumb-img" alt="Campaign Thumbnail" loading="lazy" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Single image -->
    <div v-else-if="images?.length === 1" class="bg-body-tertiary rounded overflow-hidden mb-5" style="--fn-aspect-ratio: calc(480 / 856 * 100%)">
        <img :src="images[0]" alt="Campaign Image" loading="lazy" />
    </div>

    <!-- Fallback image -->
    <div v-else class="bg-body-tertiary rounded overflow-hidden mb-5" style="--fn-aspect-ratio: calc(480 / 856 * 100%)">
        <img :src="campaign.first_image" alt="Campaign Image" loading="lazy" />
    </div>
</template>
