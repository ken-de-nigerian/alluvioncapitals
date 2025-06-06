<script setup lang="ts">
    import {Head, Link, usePage} from "@inertiajs/vue3";
    import {computed, onMounted, ref} from "vue";
    import {route} from "ziggy-js";
    import CampaignProgress from "../../Components/CampaignComponents/CampaignProgress.vue";

    const page = usePage()
    const props = defineProps({
        user: Object,
        profile_progress: Number,
        campaigns: Object
    })

    const progressColor = computed(() => {
        if (props.profile_progress < 30) return 'danger'
        if (props.profile_progress < 90) return 'warning'
        return 'success'
    })

    const animatedProgress = ref(0)

    onMounted(() => {
        // Animate the progress from 0 to the actual value
        const duration = 1500 // animation duration in ms
        const startTime = performance.now()

        const animate = (currentTime: number) => {
            const elapsedTime = currentTime - startTime
            const progress = Math.min(elapsedTime / duration, 1)

            animatedProgress.value = Math.floor(progress * props.profile_progress)

            if (progress < 1) {
                requestAnimationFrame(animate)
            }
        }

        requestAnimationFrame(animate)
    })

    const truncate = (text: string, maxLength: number): string => {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG');
    };

    const formatDate = (date: number):string => {
        if (!date) return '';
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Dashboard`" />

    <!-- Dashboard content -->
    <div class="col-lg-9">
        <h1 class="h2 pb-2 pb-lg-3">Dashboard</h1>

        <!-- Wallet + Account progress -->
        <section class="row g-3 g-xl-4 pb-5 mb-md-3">
            <div class="col-md-6 col-lg-5 col-xl-6">
                <div class="card bg-success-subtle border-0 h-100">
                    <div class="card-body">
                        <h3 class="fs-sm fw-normal mb-2">Personal wallet</h3>
                        <div class="h5 mb-0">₦{{ formatCurrency(props.user?.balance) }}</div>
                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0 pb-4 mt-n2 mt-sm-0">
                        <Link class="position-relative d-inline-flex align-items-center fs-sm fw-medium text-success text-decoration-none" :href="route('user.payments.withdrawals')">
                            <span class="hover-effect-underline stretched-link">Withdraw funds</span>
                            <i class="fi-chevron-right fs-base ms-1"></i>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-7 col-xl-6">
                <div class="card border-0 h-100" :class="animatedProgress === 100 ? 'bg-success-subtle' : 'bg-warning-subtle'">
                    <div class="card-body d-flex align-items-center">
                        <!-- Progress Circle -->
                        <div class="circular-progress flex-shrink-0 ms-n2 ms-sm-0 mb-3" :class="`text-${progressColor}`" role="progressbar" :style="`--fn-progress-bar-width: 14px; --fn-progress: ${animatedProgress}`" aria-label="Profile progress" :aria-valuenow="props.profile_progress" aria-valuemin="0" aria-valuemax="100">
                            <svg class="progress-circle">
                                <circle class="progress-background d-none-dark" r="0" style="stroke: #fff"></circle>
                                <circle class="progress-background d-none d-block-dark" r="0" style="stroke: rgba(255,255,255, .1)"></circle>
                                <circle class="progress-bar" r="0"></circle>
                            </svg>
                            <h5 class="position-absolute top-50 start-50 translate-middle text-center mb-0 fs-sm fw-medium">
                                {{ animatedProgress }}%
                            </h5>
                        </div>

                        <div class="ps-3 ps-sm-4">
                            <template v-if="profile_progress === 100">
                                <!-- Display success message when profile is complete -->
                                <h3 class="h6 pb-1 mb-2">Profile Completed</h3>
                                <p class="fs-sm mb-0 text-success">
                                    <i class="ci-check-circle me-2"></i>
                                    Your profile is fully completed. Great job!
                                </p>
                            </template>

                            <template v-else>
                                <h3 class="h6 pb-1 mb-2">Complete your profile</h3>
                                <ul class="list-unstyled fs-sm mb-0">
                                    <li class="d-flex">
                                        <i :class="user.first_name && user.last_name ? 'ci-check-circle' : 'ci-plus'" class="fs-base me-2" style="margin-top: 0.1875rem"></i>
                                        Fill in your details
                                    </li>

                                    <li class="d-flex">
                                        <i :class="user.email_verified_at ? 'ci-check-circle' : 'ci-plus'" class="fs-base me-2" style="margin-top: 0.1875rem"></i>
                                        Verify your email
                                    </li>

                                    <li class="d-flex">
                                        <i :class="user.phone_number ? 'ci-check-circle' : 'ci-plus'" class="fs-base me-2" style="margin-top: 0.1875rem"></i>
                                        Add phone number
                                    </li>
                                </ul>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Campaign listings -->
        <section class="pb-5 mb-md-3">
            <div class="d-flex align-items-center justify-content-between pb-1 pb-sm-0 mb-3 mb-sm-4">
                <h2 class="h4 mb-0 me-3">My Campaigns</h2>
                <div class="nav" v-if="props.campaigns.data.length > 0">
                    <Link class="nav-link position-relative px-0" :href="route('user.campaigns.index')">
                        <span class="hover-effect-underline stretched-link me-1">View all</span>
                        <i class="fi-chevron-right fs-base"></i>
                    </Link>
                </div>
            </div>

            <div class="vstack gap-3" v-if="props.campaigns.data.length > 0">
                <!-- Item -->
                <article v-for="campaign in props.campaigns.data" :key="campaign.id" class="card">
                    <div class="row g-0">
                        <div class="col-sm-4 col-md-3 rounded overflow-hidden pb-2 pb-sm-0 pe-sm-2">
                            <div class="position-relative d-flex h-100 bg-body-tertiary" style="min-height: 174px" :class="{ 'cursor-pointer': true }">
                                <img :src="campaign.first_image" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" :alt="campaign.title">
                                <div class="ratio d-none d-sm-block" style="--fn-aspect-ratio: calc(180 / 240 * 100%)"></div>
                                <div class="ratio ratio-16x9 d-sm-none"></div>
                            </div>
                        </div>

                        <div class="col-sm-8 col-md-9 align-self-center">
                            <div class="card-body d-flex justify-content-between p-3 py-sm-4 ps-sm-2 ps-md-3 pe-md-4 mt-n1 mt-sm-0">
                                <div class="position-relative pe-3">
                                    <span class="badge text-body-emphasis bg-body-secondary mb-2">{{ truncate(campaign.title, 25) }}</span>

                                    <div class="h5 mb-2">₦{{ formatCurrency(campaign.funds_raised) }}
                                        <sup class="fs-6 fw-lighter">raised</sup>
                                    </div>
                                    <Link class="stretched-link d-block fs-sm text-body text-decoration-none mb-2" :href="campaign.show_route" target="_blank">out of ₦{{ formatCurrency(campaign.goal) }} goal.</Link>

                                    <!-- Success progress bar -->
                                    <CampaignProgress :funds-raised="campaign.funds_raised" :goal="campaign.goal" />
                                </div>

                                <div class="text-end">
                                    <div class="fs-xs text-body-secondary mb-3">
                                        Created: {{ formatDate(campaign.created_at) }}
                                    </div>

                                    <!-- Buttons -->
                                    <div v-if="campaign.status_badge.text !== 'Completed' && campaign.days_left_text !== 'Expired'" class="d-flex justify-content-end gap-2 mb-3">
                                        <Link :href="route('campaigns.edit.details', campaign.id)" class="btn btn-outline-secondary">
                                            Edit
                                        </Link>

                                        <div class="dropdown">
                                            <button type="button" class="btn btn-icon btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Settings">
                                                <i class="ci-settings fs-base"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <Link class="dropdown-item" :href="route('user.campaigns.rewards.index', campaign.id)">
                                                        <i class="ci-gift opacity-75 me-2"></i> Gifts
                                                    </Link>
                                                </li>

                                                <li>
                                                    <Link class="dropdown-item" :href="route('user.campaigns.updates.index', campaign.id)">
                                                        <i class="ci-corner-right-up fs-base opacity-75 me-2"></i> Updates
                                                    </Link>
                                                </li>

                                                <li>
                                                    <Link class="dropdown-item" :href="route('user.campaigns.comments.index', campaign.id)">
                                                        <i class="ci-message-square fs-base opacity-75 me-2"></i> Comments
                                                    </Link>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Footer list -->
                                    <ul class="list-unstyled flex-row flex-wrap justify-content-end fs-sm" :class="{'mb-2': campaign.status_badge.text !== 'Completed' && campaign.days_left_text !== 'Expired', 'mb-5': campaign.status_badge.text === 'Completed' || campaign.days_left_text === 'Expired'}">
                                        <li class="d-flex align-items-center me-2 me-md-3">
                                            <i class="ci-clock fs-base me-1"></i>
                                            {{ campaign.days_left_text }}
                                        </li>

                                        <li v-if="campaign.status_badge.text !== 'Completed' && campaign.days_left_text !== 'Expired'" class="d-flex align-items-center me-2 me-md-3">
                                            <!-- Share button -->
                                            <button data-bs-toggle="modal" data-bs-target=".share-modal" class="btn btn-sm btn-secondary px-2 mb-0" :data-url="campaign.show_route" :data-title="campaign.title">
                                                <i class="ci-share-2"></i>
                                            </button>
                                        </li>
                                    </ul>

                                    <!-- Status Badge -->
                                    <div class="badge" :class="campaign.status_badge.class">
                                        {{ campaign.status_badge.text }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div v-if="props.campaigns.data.length === 0">
                <h2 class="h6 pt-2 mb-2">You have no published campaigns</h2>
                <p class="fs-sm mb-4" style="max-width: 640px">
                    You haven't published any campaigns yet. Create your first campaign to start reaching supporters
                    and raising funds for your cause.
                </p>
                <Link :href="route('campaigns.add.details')" class="btn btn-dark">
                    <i class="ci-plus fs-base ms-n1 me-2"></i>
                    Create Campaign
                </Link>
            </div>
        </section>

        <!-- Help center -->
        <section>
            <h2 class="h4 mb-4">Need help?</h2>
            <div class="row row-cols-1 row-cols-sm-2 g-4 g-md-5">
                <div class="col">
                    <div class="position-relative">
                        <i class="ci-globe fs-4 text-dark-emphasis pb-1 pb-md-0 mb-2 mb-md-3"></i>
                        <h3 class="h6 pb-md-1 mb-2">
                            <a class="hover-effect-underline stretched-link" href="#">Creator Community</a>
                        </h3>
                        <p class="fs-sm mb-0">Join our community of fundraisers to share experiences, get advice, and connect with other campaign creators.</p>
                    </div>
                </div>

                <div class="col">
                    <div class="position-relative">
                        <i class="ci-headphones fs-4 text-dark-emphasis pb-1 pb-md-0 mb-2 mb-md-3"></i>
                        <h3 class="h6 pb-md-1 mb-2">
                            <a class="hover-effect-underline stretched-link" href="#">Get professional support</a>
                        </h3>
                        <p class="fs-sm mb-0">Our dedicated support team is here to help you with any questions about your campaign, payments, or platform features.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
