<script setup lang="ts">
    import {Link, usePage} from "@inertiajs/vue3";
    import {computed} from "vue";
    import {route} from "ziggy-js";

    const page = usePage();
    const user = page.props.auth.user;

    const props = defineProps({
        frontendCampaigns: Array
    })

    const initials = computed(() => {
        const first = user?.first_name?.charAt(0) || ''
        const last = user?.last_name?.charAt(0) || ''
        return `${first}${last}`.toUpperCase()
    })

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG');
    };
</script>

<template>
    <nav class="offcanvas offcanvas-start" id="navbarNav" tabindex="-1" aria-labelledby="navbarNavLabel">
        <div class="offcanvas-header py-3">
            <h5 class="offcanvas-title" id="navbarNavLabel">{{ page.props.app.name || 'KindredCause'}}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body pt-3 pb-4 py-lg-0 mx-lg-auto">
            <ul class="navbar-nav position-relative">
                <li class="nav-item py-lg-2 me-lg-n2 me-xl-0">
                    <Link class="nav-link" :class="{ 'active': page.component === 'Homepage/Index' }" href="/" preserve-scroll>Home</Link>
                </li>

                <li class="nav-item py-lg-2 me-lg-n2 me-xl-0">
                    <Link class="nav-link" :class="{ 'active': page.component.startsWith('Categories/') }" :href="route('categories.index')" preserve-scroll>Categories</Link>
                </li>

                <li class="nav-item dropdown position-static py-lg-2 me-lg-n1 me-xl-0">
                    <a class="nav-link dropdown-toggle" :class="{ 'active': page.component.startsWith('Campaign/') }" href="#" role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">
                        Campaigns
                    </a>

                    <div v-if="page.props.frontendCampaigns && page.props.frontendCampaigns.length > 0" class="dropdown-menu rounded-4 p-4" style="--cz-dropdown-spacer: .875rem; min-width: 100%;">
                        <div class="row g-3">
                            <div v-for="campaign in page.props.frontendCampaigns" :key="campaign.id" class="col-12 col-sm-6 col-md-6 col-lg-6">
                                <Link :href="campaign.show_route" class="text-decoration-none text-dark d-block" preserve-scroll>
                                    <div class="card border-0 shadow-sm h-100">

                                        <div v-if="campaign.featured === 'yes'" class="d-flex flex-column gap-2 align-items-start position-absolute top-0 start-0 z-1 pt-1 pt-sm-0 ps-1 ps-sm-0 mt-2 mt-sm-3 ms-2 ms-sm-3">
                                            <span class="badge text-bg-info d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="This campaign has been verified for authenticity.">
                                                Verified
                                                <i class="ci-shield ms-1"></i>
                                            </span>
                                            <span class="badge text-bg-warning" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="This campaign is featured for its popularity or importance.">
                                                Featured
                                            </span>
                                        </div>

                                        <img :src="campaign.first_image" class="card-img-top object-fit-cover" :alt="campaign.title" style="height: 150px" loading="lazy">

                                        <div class="card-body p-2">
                                            <h6 class="text-truncate mb-1">{{ campaign.title }}</h6>

                                            <small class="text-muted d-block">₦{{ formatCurrency(campaign.funds_raised) }} raised out of ₦{{ formatCurrency(campaign.goal) }} goal.</small>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <hr class="dropdown-divider">

                        <div class="text-center text-muted">
                            <Link class="text-muted text-center" :href="route('campaigns.index')" preserve-scroll>View All</Link>
                        </div>
                    </div>

                    <div v-else class="dropdown-menu rounded-4 p-4" style="--cz-dropdown-spacer: .875rem; min-width: 100%;">
                        <div class="text-muted text-center">No campaigns available</div>
                    </div>
                </li>

                <li class="nav-item py-lg-2 me-lg-n2 me-xl-0">
                    <Link class="nav-link" :href="route('home')" preserve-scroll>How It Works</Link>
                </li>

                <li class="nav-item py-lg-2 me-lg-n2 me-xl-0">
                    <Link class="nav-link" :href="route('home')" preserve-scroll>Contact Us</Link>
                </li>
            </ul>
        </div>

        <!-- Account button (logged in state) visible on screens < 768px wide (md breakpoint) -->
        <div v-if="user" class="offcanvas-header nav border-top px-0 py-3 mt-3 d-md-none">
            <Link class="nav-link hover-effect-scale justify-content-center w-100 gap-2 py-0" :href="route('home')" preserve-scroll>
                <div class="btn btn-icon position-relative border rounded-circle overflow-hidden">
                    <img :src="user?.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${initials}`" class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 object-fit-cover" alt="Avatar" loading="lazy">
                </div>
                {{ user?.first_name + ' ' + user?.last_name || page.props.app.name || 'KindredCause' }}
            </Link>
        </div>
    </nav>
</template>
