<script setup lang="ts">
    import {Link} from "@inertiajs/vue3";
    import {usePage} from '@inertiajs/vue3';
    import {computed} from "vue";
    import {route} from "ziggy-js";

    defineProps({
        avatar: String
    })

    const page = usePage();
    const user = page.props.auth.user

    const initials = computed(() => {
        const first = user?.first_name?.charAt(0) || ''
        const last = user?.last_name?.charAt(0) || ''
        return `${first}${last}`.toUpperCase()
    })
</script>

<template>
    <aside class="col-lg-3">
        <div class="d-none d-lg-block" style="margin-top: -105px"></div>
        <div class="offcanvas-lg offcanvas-start sticky-lg-top pe-lg-0 pe-xl-4" id="accountSidebar">
            <div class="d-none d-lg-block" style="padding-top: 105px"></div>

            <!-- Header -->
            <div class="offcanvas-header align-items-start d-lg-block py-3 p-lg-0">
                <div class="d-flex align-items-start flex-lg-column gap-lg-3">
                    <!-- Visible on screens > 991px wide -->
                    <div class="ratio ratio-1x1 border rounded-circle overflow-hidden d-none d-lg-block" style="width: 86px">
                        <img :src="user?.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${initials}`" alt="Avatar" loading="lazy">
                    </div>

                    <!-- Visible on screens < 992px wide -->
                    <div class="ratio ratio-1x1 border rounded-circle overflow-hidden flex-shrink-0 d-lg-none" style="width: 48px">
                        <img :src="user?.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${initials}`" alt="Avatar" loading="lazy">
                    </div>

                    <div class="w-100 ps-2 ms-1 ms-lg-0 ps-lg-0">
                        <h4 class="h6 mb-1 mb-lg-2">{{ user?.first_name }} {{ user?.last_name }}</h4>
                        <p class="fs-sm mb-0">Become a beacon of hope for those in need.</p>
                    </div>
                </div>
                <button type="button" class="btn-close d-lg-none mt-n2" data-bs-dismiss="offcanvas" data-bs-target="#accountSidebar" aria-label="Close"></button>
            </div>

            <!-- Body (Navigation) -->
            <div class="offcanvas-body d-block pt-2 pt-lg-4 pb-lg-0">
                <nav class="list-group list-group-borderless">
                    <Link class="list-group-item list-group-item-action d-flex align-items-center rounded-pill" :class="{ 'active': page.component === 'Admin/Index' }" :href="route('admin.dashboard')">
                        <i class="ci-grid fs-base opacity-75 me-2"></i>
                        Dashboard
                    </Link>

                    <Link class="list-group-item list-group-item-action d-flex align-items-center rounded-pill" :class="{ 'active': ['Admin/Campaign', 'Admin/Rewards', 'Admin/Updates', 'Admin/Comments'].some(prefix => page.component.startsWith(prefix)) }" :href="route('admin.campaigns.index')">
                        <i class="ci-gift fs-base opacity-75 me-2"></i>
                        Campaigns ({{ page.props.allCampaignCount }})
                    </Link>

                    <Link class="list-group-item list-group-item-action d-flex align-items-center rounded-pill" :class="{ 'active': page.component.startsWith('Admin/Donations') }" :href="route('admin.payments.donations')">
                        <i class="ci-dollar-sign fs-base opacity-75 me-2"></i>
                        Donations
                    </Link>

                    <Link class="list-group-item list-group-item-action d-flex align-items-center rounded-pill" :class="{ 'active': page.component.startsWith('Admin/Category') }" :href="route('admin.categories.index')">
                        <i class="ci-layers-2 fs-base opacity-75 me-2"></i>
                        Categories
                    </Link>
                </nav>

                <nav class="list-group list-group-borderless">
                    <Link class="list-group-item list-group-item-action d-flex align-items-center rounded-pill" :class="{ 'active': page.component.startsWith('Admin/Users') }" :href="route('admin.users.index')">
                        <i class="ci-user fs-base opacity-75 me-2"></i>
                        Users
                    </Link>
                </nav>

                <h6 class="pt-4 ps-2 ms-1">Payments</h6>
                <nav class="list-group list-group-borderless">
                    <Link class="list-group-item list-group-item-action d-flex align-items-center rounded-pill" :class="{ 'active': page.component.startsWith('Admin/Withdrawals') }" :href="route('admin.payments.withdrawals')">
                        <i class="ci-credit-card fs-base opacity-75 me-2"></i>
                        Withdrawals
                    </Link>
                </nav>

                <h6 class="pt-4 ps-2 ms-1">Account</h6>
                <nav class="list-group list-group-borderless">
                    <Link class="list-group-item list-group-item-action d-flex align-items-center rounded-pill" :class="{ 'active': page.component.startsWith('Admin/Profile') }" :href="route('admin.profile')">
                        <i class="ci-settings fs-base opacity-75 me-2"></i>
                        Settings
                    </Link>

                    <!--suppress HtmlWrongAttributeValue -->
                    <Link as="button" method="post" class="list-group-item list-group-item-action d-flex align-items-center rounded-pill" :href="route('logout')">
                        <i class="ci-log-out fs-base opacity-75 me-2"></i>
                        Log out
                    </Link>
                </nav>
            </div>

            <div class="offcanvas-header d-lg-block p-lg-0">
                <Link :href="route('campaigns.add.details')" class="btn btn-dark rounded-pill w-100 animate-scale mt-lg-4">
                    <i class="ci-plus-circle fs-base animate-target me-2 ms-n1"></i>
                    Add Campaign
                </Link>
            </div>
        </div>
    </aside>
</template>
