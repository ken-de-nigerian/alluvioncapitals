<script setup lang="ts">
    import {ref, watch} from "vue";
    import {Head, Link, router, usePage} from "@inertiajs/vue3";
    import debounce from 'lodash/debounce';
    import {route} from "ziggy-js";
    import Pagination from "../../../Components/Navigation/Pagination.vue";

    const page = usePage();

    const props = defineProps({
        donations: Object,
        filters: Object
    })

    const search = ref(props.filters?.search || '');

    // Debounced search function
    const performSearch = debounce(() => {
        router.get(route('admin.payments.donations'), { search: search.value }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 500);

    watch(search, (newValue) => {
        if (newValue !== props.filters?.search) {
            performSearch();
        }
    });

    const getInitials = (donation: Donation): string => {
        if (donation.anonymous) return 'A';
        const first = donation.first_name?.charAt(0) || '';
        const last = donation.last_name?.charAt(0) || '';
        return `${first}${last}`.toUpperCase();
    };

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG', {
            style: 'currency',
            currency: 'NGN',
            minimumFractionDigits: 2,
        });
    };

    const relativeTime = (dateString: string): string => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

        if (diffInSeconds < 60) return 'just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
        if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;

        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    const capitalizeStatus = (status: string): string => {
        if (!status) return '';
        return status.charAt(0).toUpperCase() + status.slice(1);
    }

    const truncate = (text: string, maxLength: number): string => {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Donations`" />

    <!-- Donations content -->
    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between gap-3 pb-2 pb-sm-3 mb-md-2">
            <h1 class="h2 text-nowrap mb-sm-0">Donations</h1>
            <div class="position-relative w-100" style="max-width: 300px">
                <i class="ci-search position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                <input type="search" class="product-search form-control form-icon-start rounded-pill" v-model="search" placeholder="Search donations...">
            </div>
        </div>

        <!-- Donations list (table) -->
        <table class="table align-middle fs-sm mb-4">
            <thead>
                <tr>
                    <th class="px-0 pe-sm-2" scope="col">
                        <span class="fw-normal text-body">Campaign</span>
                    </th>

                    <th class="d-none d-md-table-cell" scope="col">
                        <span class="fw-normal text-body">Date</span>
                    </th>

                    <th class="d-none d-md-table-cell" scope="col">
                        <span class="fw-normal text-body">Status</span>
                    </th>

                    <th class="d-none d-sm-table-cell pe-0" scope="col"></th>
                </tr>
            </thead>

            <tbody class="product-list">
                <!-- Item -->
                <tr v-for="donation in donations.data" :key="donation.id">
                    <td class="py-3 px-0 pe-sm-2">
                        <div class="d-none d-md-block d-xl-none" style="width: 350px"></div>
                        <div class="d-none d-xl-block" style="width: 450px"></div>
                        <div class="d-flex align-items-start align-items-md-center hover-effect-scale position-relative py-1">
                            <div class="ratio bg-body-secondary rounded overflow-hidden flex-shrink-sm-0" style="--cz-aspect-ratio: calc(110 / 142 * 100%); max-width: 142px">
                                <img :src="donation.campaign.first_image" class="hover-effect-target" :alt="donation.campaign.title">
                            </div>

                            <div class="ps-2 ps-sm-3 ms-1">
                                <h6 class="product mb-2">
                                    <Link class="fs-sm fw-medium hover-effect-underline stretched-link" :href="donation.campaign.show_route">{{ truncate(donation.campaign.title, 25) }}</Link>
                                </h6>

                                <div class="d-flex align-items-center flex-wrap gap-1 fs-xs">
                                    <div class="author d-flex align-items-center fs-xs fw-medium text-body gap-1 p-0">
                                        <div class="flex-shrink-0 border rounded-circle" style="width: 22px">
                                            <div class="ratio ratio-1x1 rounded-circle overflow-hidden">
                                                <img :src="`https://placehold.co/124x124/222934/ffffff?text=${getInitials(donation)}`" :alt="donation.anonymous ? 'Anonymous' : 'Donor avatar'" loading="lazy">
                                            </div>
                                        </div>
                                        {{ formatCurrency(donation.amount) }}
                                    </div>

                                    <div class="text-body-secondary">by</div>
                                    <div class="category fs-xs fw-medium text-body">{{ donation.anonymous ? 'Anonymous' : `${donation.first_name} ${donation.last_name}` }}</div>
                                </div>

                                <div class="d-flex align-items-center flex-wrap gap-1 fs-xs mt-2">
                                <div class="category fs-xs fw-medium text-body badge bg-secondary-subtle">Via {{ donation.gateway }}</div>
                                </div>

                                <!-- Visible on screens < 769px wide (md breakpoint) -->
                                <div class="fs-xs text-nowrap d-md-none mt-2 mb-1"><span class="text-body-secondary">Status:</span> <span class="license" :class="{ 'text-success': donation.status === 'approved', 'text-warning': donation.status === 'pending', 'text-danger': donation.status === 'rejected' }">{{ capitalizeStatus(donation.status) }}</span></div>
                                <div class="fs-xs text-nowrap d-md-none"><span class="text-body-secondary">Date:</span> {{ relativeTime(donation.created_at) }}</div>

                                <!-- Visible on screens < 500px wide (sm breakpoint) -->
                                <button type="button" class="btn btn-sm btn-secondary rounded-pill animate-scale position-relative z-2 d-sm-none mt-3">
                                    <i class="ci-download-cloud animate-target fs-sm ms-n1 me-1"></i>
                                    Download
                                </button>
                            </div>
                        </div>
                    </td>

                    <!-- Visible on screens > 768px wide (md breakpoint) -->
                    <td class="d-none d-md-table-cell py-3">{{ relativeTime(donation.created_at) }}</td>
                    <td class="d-none d-md-table-cell py-3" :class="{ 'text-success': donation.status === 'approved', 'text-warning': donation.status === 'pending', 'text-danger': donation.status === 'rejected' }">{{ capitalizeStatus(donation.status) }}</td>

                    <!-- Visible on screens > 500px wide (sm breakpoint) -->
                    <td class="d-none d-sm-table-cell text-end py-3 ps-0 ps-sm-3 pe-0" style="width: 120px">
                        <button type="button" class="btn btn-sm btn-secondary rounded-pill animate-scale">
                            <i class="ci-download-cloud animate-target fs-sm ms-n1 me-1"></i>
                            Download
                        </button>
                    </td>
                </tr>

                <tr v-if="donations.data.length === 0">
                    <td class="py-3 ps-0 pt-4 pb-4" colspan="5">
                        <h2 class="h6 pt-2 mb-2">You have no published categories</h2>
                        <p class="fs-sm mb-4" style="max-width: 640px">
                            You haven't created any categories yet. Adding categories will help organize your campaigns and make it easier for users to navigate.
                        </p>

                        <Link :href="route('admin.categories.create')" class="btn btn-dark">
                            <i class="ci-plus fs-base ms-n1 me-2"></i>
                            Add category
                        </Link>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <Pagination :categories="donations" />
    </div>
</template>
