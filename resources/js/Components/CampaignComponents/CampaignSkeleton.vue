<template>
    <!-- Campaign Skeleton Placeholder -->
    <div class="row g-3 g-sm-4 g-lg-3 g-xl-4">
        <!-- Skeleton Campaign Cards -->
        <div v-for="n in 12" :key="n" class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 rounded-4 overflow-hidden">
                <!-- Image Skeleton -->
                <div class="card-img-top position-relative bg-body-tertiary overflow-hidden">
                    <div class="ratio d-block skeleton-shimmer" style="--cz-aspect-ratio: calc(220 / 304 * 100%)">
                        <div class="w-100 h-100"></div>
                    </div>

                    <!-- Skeleton badges -->
                    <div class="d-flex flex-column gap-2 align-items-start position-absolute top-0 start-0 z-1 pt-1 pt-sm-0 ps-1 ps-sm-0 mt-2 mt-sm-3 ms-2 ms-sm-3">
                        <div class="skeleton-shimmer rounded-pill" style="width: 70px; height: 24px;"></div>
                    </div>

                    <!-- Skeleton share button -->
                    <div class="position-absolute top-0 end-0 z-2 pt-1 pt-sm-0 pe-1 pe-sm-0 mt-2 mt-sm-3 me-2 me-sm-3">
                        <div class="skeleton-shimmer rounded-circle" style="width: 32px; height: 32px;"></div>
                    </div>
                </div>

                <!-- Card body skeleton -->
                <div class="card-body p-3">
                    <!-- Title and Goal skeleton -->
                    <div class="d-flex min-w-0 justify-content-between gap-2 gap-sm-3 mb-3">
                        <div class="skeleton-shimmer rounded" style="width: 60%; height: 20px;"></div>
                        <div class="skeleton-shimmer rounded" style="width: 30%; height: 20px;"></div>
                    </div>

                    <!-- Raised amount skeleton -->
                    <div class="skeleton-shimmer rounded mb-2" style="width: 45%; height: 16px;"></div>

                    <!-- Progress bar skeleton -->
                    <div class="rounded-pill mb-3" style="height: 4px;">
                        <div class="skeleton-shimmer bg-secondary rounded-pill" style="width: 65%; height: 4px;"></div>
                    </div>

                    <!-- Footer info skeleton -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                        <div class="skeleton-shimmer rounded" style="width: 40%; height: 14px;"></div>
                        <div class="skeleton-shimmer rounded" style="width: 35%; height: 14px;"></div>
                    </div>
                </div>
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
