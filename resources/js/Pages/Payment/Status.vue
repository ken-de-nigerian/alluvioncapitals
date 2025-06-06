<script setup lang="ts">
    import { Head, usePage, Link } from "@inertiajs/vue3";
    import { route } from "ziggy-js";
    import { computed } from "vue";

    const page = usePage();

    // Compute app name safely
    const appName = computed(() => page.props.app?.name ?? "App");

    // Compute current date
    const currentDate = computed(() => new Date().toLocaleDateString("en-GB", {
        day: "numeric",
        month: "short",
        year: "numeric"
    }));

    const props = defineProps<{
        title: string;
        errorMessage?: string;
        txRef?: string;
        sessionData?: { first_name?: string; last_name?: string; reward_id?: string | number; amount?: number };
        campaign?: { title?: string; slug?: string };
        retry?: boolean;
        retry_url: string;
    }>();
</script>

<template>
    <Head :title="`${appName} | ${props.title}`" />

    <div class="row row-cols-1 row-cols-lg-2 g-0 mx-auto" style="max-width: 1920px">
        <div class="d-flex justify-content-center w-100">
            <div class="col d-flex flex-column justify-content-center py-5 px-xl-4 px-xxl-5">
                <div class="w-100 pt-sm-2 pt-md-3 pt-lg-4 pb-lg-4 pb-xl-5 px-4 px-sm-5 mx-auto" style="max-width: 740px">
                    <div class="d-flex align-items-sm-center border-bottom pb-4 pb-md-5">
                        <div class="d-flex align-items-center justify-content-center bg-danger text-white rounded-circle flex-shrink-0" style="width: 3rem; height: 3rem; margin-top: -.125rem">
                            <i class="ci-check fs-4"></i>
                        </div>

                        <div class="w-100 ps-3">
                            <div class="fs-sm mb-1">Reference: {{ props.txRef ?? 'No tx_ref found' }}</div>
                            <div class="d-sm-flex align-items-center">
                                <h1 class="h4 mb-0 me-3">{{ props.title }}</h1>
                                <div class="nav mt-2 mt-sm-0 ms-auto">
                                    <Link class="nav-link text-decoration-underline p-0" :href="route('campaigns.index')">Back to campaigns</Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-4 pt-3 pb-5 mt-3">
                        <div>
                            <h3 class="h6 mb-2">Donor</h3>
                            <p class="fs-sm mb-0">{{ props.sessionData?.first_name ?? '' }} {{ props.sessionData?.last_name ?? '' }}</p>
                        </div>

                        <div>
                            <h3 class="h6 mb-2">Date</h3>
                            <p class="fs-sm mb-0">{{ currentDate }}</p>
                        </div>

                        <div>
                            <h3 class="h6 mb-2">Campaign</h3>
                            <p class="fs-sm mb-0">{{ props.campaign?.title ?? 'No campaign title' }}</p>
                        </div>
                    </div>

                    <div class="bg-danger rounded px-4 py-4" style="--cz-bg-opacity: .2">
                        <div class="py-3">
                            <h2 class="h4 text-center pb-2 mb-1">{{ props.title }}</h2>
                            <p class="fs-sm text-center mb-4">{{ props.errorMessage ?? 'No error message provided' }}</p>

                            <!-- Retry button -->
                            <div v-if="props.retry" class="d-flex justify-content-center gap-2">
                                <Link :href="retry_url" class="btn btn-dark">
                                    <i class="ci-arrow-up-left fs-base me-2"></i> Try Again
                                </Link>
                            </div>
                        </div>
                    </div>

                    <p class="fs-sm pt-4 pt-md-5 mt-2 mt-sm-3 mt-md-0 mb-0">
                        Need help?
                        <Link class="fw-medium ms-2" :href="'mailto:site@email.com'">Contact us</Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
