<template>
    <!-- Action bar skeleton -->
    <div class="row align-items-start align-items-md-center mb-4">
        <div class="col-12 col-md-8">
            <div class="d-flex align-items-center flex-wrap">
                <!-- Checkbox and label container -->
                <div class="d-flex align-items-center me-3 mb-2 mb-md-0">
                    <!-- Checkbox placeholder -->
                    <div class="d-inline-flex position-relative z-2 pt-1 pb-2 ps-2 p-sm-0 ms-2 ms-sm-0 me-sm-2">
                        <div class="skeleton-shimmer rounded" style="width: 20px; height: 20px;"></div>
                    </div>
                    <!-- Label placeholder -->
                    <div class="skeleton-shimmer rounded ms-2" style="width: 200px; height: 24px;"></div>
                </div>

                <!-- Action buttons placeholder (shown when items are selected) -->
                <div class="d-flex flex-wrap gap-3">
                    <div class="skeleton-shimmer rounded" style="width: 120px; height: 24px;"></div>
                    <div class="skeleton-shimmer rounded" style="width: 120px; height: 24px;"></div>
                    <div class="skeleton-shimmer rounded" style="width: 80px; height: 24px;"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 mt-3 mt-md-0">
            <div class="skeleton-shimmer rounded" style="width: 100%; height: 38px;"></div>
        </div>
    </div>

    <!-- Campaign items skeleton -->
    <div class="vstack gap-4">
        <div v-for="i in 5" :key="i" class="d-sm-flex align-items-center">
            <!-- Checkbox placeholder -->
            <div class="d-inline-flex position-relative z-2 pt-1 pb-2 ps-2 p-sm-0 ms-2 ms-sm-0 me-sm-2">
                <div class="skeleton-shimmer rounded" style="width: 20px; height: 20px;"></div>
            </div>

            <!-- Card skeleton -->
            <div class="card w-100">
                <div class="d-sm-none" style="margin-top: -44px"></div>
                <div class="row g-0">
                    <!-- Image column -->
                    <div class="col-sm-4 col-md-3 rounded overflow-hidden pb-2 pb-sm-0 pe-sm-2">
                        <div class="skeleton-shimmer h-100" style="min-height: 174px"></div>
                    </div>

                    <!-- Content column -->
                    <div class="col-sm-8 col-md-9 align-self-center">
                        <div class="card-body d-flex justify-content-between p-3 py-sm-4 ps-sm-2 ps-md-3 pe-md-4 mt-n1 mt-sm-0">
                            <!-- Left content -->
                            <div class="position-relative pe-3" style="width: 60%">
                                <!-- Title -->
                                <div class="skeleton-shimmer rounded mb-2" style="width: 70%; height: 24px;"></div>

                                <!-- Funds raised -->
                                <div class="skeleton-shimmer rounded mb-2" style="width: 50%; height: 28px;"></div>

                                <!-- Progress bar -->
                                <div class="skeleton-shimmer rounded mb-3" style="width: 100%; height: 8px;"></div>

                                <!-- Goal text -->
                                <div class="skeleton-shimmer rounded" style="width: 80%; height: 12px;"></div>
                            </div>

                            <!-- Right content -->
                            <div class="text-end" style="width: 35%">
                                <!-- Date -->
                                <div class="skeleton-shimmer rounded mb-3" style="width: 80%; height: 14px; margin-left: auto;"></div>

                                <!-- Buttons -->
                                <div class="d-flex justify-content-end gap-2 mb-3">
                                    <div class="skeleton-shimmer rounded" style="width: 60px; height: 32px;"></div>
                                    <div class="skeleton-shimmer rounded" style="width: 32px; height: 32px;"></div>
                                </div>

                                <!-- Days left -->
                                <div class="skeleton-shimmer rounded d-inline-block mb-3" style="width: 60%; height: 16px;"></div>

                                <!-- Status badge -->
                                <div class="skeleton-shimmer rounded-pill d-inline-block" style="width: 80px; height: 24px;"></div>
                            </div>
                        </div>
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
