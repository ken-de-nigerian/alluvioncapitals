<script setup lang="ts">
    import {route} from "ziggy-js";
    import {usePage} from "@inertiajs/vue3";
    import {onMounted} from "vue";

    import NavbarLogo from "../../../Components/Navigation/NavbarLogo.vue";
    import initThemeSwitcher from "../../../Components/Utilities/ThemeSwitcher.js";

    const page = usePage();

    const props = defineProps({
        campaign: Object,
    })

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG');
    };

    onMounted(() => {
        initThemeSwitcher();
    });
</script>

<template>
    <!-- =======================
    Embedded grid START -->
    <section class="d-flex align-items-center justify-content-center vh-100">
        <div class="container">
            <div class="row g-4 justify-content-center align-items-center">
                <!-- Card item START -->
                <div class="col-md-6 col-xl-4 mt-0">
                    <div class="card h-100 animate-underline hover-effect-opacity hover-effect-scale rounded-4 overflow-hidden">
                        <!-- Card header START -->
                        <div class="card-header bg-transparent border-bottom-0 p-3">
                            <div class="d-flex justify-content-center">
                                <!-- Navbar brand (Logo) -->
                                <NavbarLogo/>
                            </div>
                        </div>
                        <!-- Card header END -->

                        <!-- Card body content -->
                        <div class="card-body p-3">
                            <div class="d-flex min-w-0 justify-content-between gap-2 gap-sm-3 mb-3">
                                <h3 class="nav min-w-0 mb-0">
                                    <a class="nav-link text-truncate p-0" :href="route('campaigns.show', campaign.slug)" target="_blank" :aria-label="campaign.title">
                                        <span class="animate-target">{{ campaign.title }}</span>
                                    </a>
                                </h3>
                            </div>

                            <!-- Success progress bar -->
                            <div class="fs-sm mb-2">₦{{ formatCurrency(campaign.funds_raised) }} raised of ₦{{ formatCurrency(campaign.goal) }}</div>
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
                                    <a class="nav-link" :href="route('categories.show', campaign.category.slug)" target="_blank" :aria-label="`View ${campaign.category.name} category`">
                                        <span class="text-truncate animate-target">{{ campaign.category.name }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Card footer START-->
                        <div class="card-footer pt-3">
                            <!-- Organizer and Button -->
                            <div class="d-sm-flex justify-content-sm-between align-items-center">
                                <!-- Organizer -->
                                <div class="d-flex align-items-center">
                                    <span class="mb-0 me-2">by <b>{{ campaign.first_name }} {{ campaign.last_name }}</b></span>
                                </div>

                                <!-- Button -->
                                <div class="mt-2 mt-sm-0">
                                    <a :href="route('campaigns.show', campaign.slug)" target="_blank" class="btn btn-sm btn-primary mb-0 w-100">
                                        Donate Now<i class="ci-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card item END -->
            </div>
            <!-- Row END -->
        </div>
    </section>
    <!-- =======================
    Embedded grid END -->
</template>
