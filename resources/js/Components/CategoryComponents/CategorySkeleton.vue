<script setup>

</script>

<template>
    <!-- Categories Skeleton Loader with Shimmer -->
    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-4 g-lg-3 g-xl-4 pb-2 mb-4">
        <!-- Repeat skeleton items -->
        <div v-for="i in 12" :key="`category-skeleton-${i}`" class="col">
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
</style>
