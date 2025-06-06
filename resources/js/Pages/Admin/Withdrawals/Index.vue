<script setup lang="ts">
    import {Head, router, usePage} from "@inertiajs/vue3";
    import {ref} from 'vue';
    import {route} from "ziggy-js";
    import iziToast from 'izitoast';
    import 'izitoast/dist/css/iziToast.min.css';
    import Pagination from "../../../Components/Navigation/Pagination.vue";

    const page = usePage();
    const props = defineProps({
        withdrawals: Object,
        filters: Object
    });

    // Sorting state
    const sortBy = ref(props.filters?.sort || '');
    const sortOrder = ref(props.filters?.order || 'desc');

    const getInitials = (withdrawal: any): string => {
        const first = withdrawal.user?.first_name?.charAt(0) || '';
        const last = withdrawal.user?.last_name?.charAt(0) || '';
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

    // Handle sorting
    const handleSort = (selectedValue: string) => {
        sortBy.value = selectedValue;
        router.get(route('admin.payments.withdrawals'),
            { sort: sortBy.value, order: sortOrder.value },
            { preserveState: true, replace: true }
        );
    };

    // Toggle sort order
    const toggleSortOrder = () => {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
        router.get(route('admin.payments.withdrawals'),
            { sort: sortBy.value, order: sortOrder.value },
            { preserveState: true, replace: true }
        );
    };

    // Approve withdrawal action
    const approveWithdrawal = (withdrawal: any) => {
        confirmAction("Approving this withdrawal is final and cannot be undone. Do you wish to continue?", () => {
            router.post(route('admin.payments.withdrawal.approve', [withdrawal.id]), {
                preserveScroll: true,
            });
        });
    };

    // Reject withdrawal action
    const rejectWithdrawal = (withdrawal: any) => {
        confirmAction("Rejecting this reward is final and cannot be undone. Do you wish to continue?", () => {
            router.post(route('admin.payments.withdrawal.reject', [withdrawal.id]), {
                preserveScroll: true,
            });
        });
    };

    // Toast notification helper
    const confirmAction = (message: string, callback: Function) => {
        iziToast.question({
            timeout: false,
            close: false,
            overlay: true,
            displayMode: 'once',
            title: 'Confirm',
            message,
            position: 'topRight',
            buttons: [
                ['<button><b>YES</b></button>', (instance: any, toast: any) => {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    callback();
                }, true],
                ['<button>Cancel</button>', (instance: any, toast: any) => {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                }]
            ]
        });
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Withdrawals`" />

    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between gap-3 pb-2 pb-sm-3 mb-md-2">
            <h1 class="h2 text-nowrap mb-sm-0">Withdrawal Requests</h1>
            <div class="d-flex gap-2">
                <div class="position-relative" style="width: 190px">
                    <i class="ci-filter position-absolute top-50 start-0 translate-middle-y z-1 ms-3"></i>
                    <select
                        class="form-select rounded-pill ps-5"
                        v-model="sortBy"
                        @change="handleSort(sortBy)">
                        <option value="">All Requests</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <button
                    class="btn btn-outline-secondary rounded-pill"
                    @click="toggleSortOrder">
                    <i :class="`ci-arrow-${sortOrder === 'asc' ? 'up' : 'down'} me-1`"></i>
                    {{ sortOrder === 'asc' ? 'Oldest' : 'Newest' }}
                </button>
            </div>
        </div>

        <!-- Withdrawals list (table) -->
        <table class="table align-middle fs-sm mb-4">
            <thead>
                <tr>
                    <th class="px-0 pe-sm-2" scope="col">
                        <span class="fw-normal text-body">Details</span>
                    </th>

                    <th class="d-md-table-cell" scope="col">
                        <span class="fw-normal text-body">Amount</span>
                    </th>

                    <th class="d-none d-md-table-cell" scope="col">
                        <span class="fw-normal text-body">Requested</span>
                    </th>

                    <th class="d-none d-sm-table-cell pe-0" scope="col"></th>
                </tr>
            </thead>
            <tbody class="product-list">
            <!-- Item -->
            <tr v-for="withdrawal in withdrawals.data" :key="withdrawal.id">
                <td class="py-3 px-0 pe-sm-2">
                    <div class="d-flex align-items-start align-items-md-center hover-effect-scale position-relative py-1">
                        <div>
                            <h6 class="product mb-2">
                                <span class="fs-sm fw-medium hover-effect-underline">
                                    {{ withdrawal.withdrawal_settings.account_number }}
                                </span>
                            </h6>

                            <div class="d-flex align-items-center flex-wrap gap-1 fs-xs">
                                <div class="author d-flex align-items-center fs-xs fw-medium text-body gap-1 p-0">
                                    <div class="flex-shrink-0 border rounded-circle" style="width: 22px">
                                        <div class="ratio ratio-1x1 rounded-circle overflow-hidden">
                                            <img :src="withdrawal.user?.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${getInitials(withdrawal)}`"
                                                 :alt="`${withdrawal.user?.first_name || ''} ${withdrawal.user?.last_name || ''}`" loading="lazy">
                                        </div>
                                    </div>
                                    {{ withdrawal.user.first_name }} {{ withdrawal.user.last_name }}
                                </div>
                                <div class="text-body-secondary">via</div>
                                <div class="category fs-xs fw-medium text-body">{{ withdrawal.withdrawal_settings.bank_name }}</div>
                            </div>

                            <!-- Mobile view -->
                            <div class="fs-xs text-nowrap d-md-none mt-2 mb-1">
                                <span class="text-body-secondary">Status:</span>
                                <span class="license" :class="{
                                        'text-success': withdrawal.status === 'approved',
                                        'text-warning': withdrawal.status === 'pending',
                                        'text-danger': withdrawal.status === 'rejected'
                                    }">
                                        {{ capitalizeStatus(withdrawal.status) }}
                                    </span>
                            </div>
                            <div class="fs-xs text-nowrap d-md-none">
                                <span class="text-body-secondary">Date:</span>
                                {{ relativeTime(withdrawal.created_at) }}
                            </div>

                            <template v-if="withdrawal.status === 'pending'">
                                <button type="button" class="btn btn-sm btn-secondary rounded-pill animate-scale position-relative z-2 d-sm-none mt-3" @click="approveWithdrawal(withdrawal)">
                                    <i class="ci-check-circle animate-target fs-sm me-1"></i>
                                    Approve
                                </button>

                                <button type="button" class="btn btn-sm btn-danger rounded-pill animate-scale position-relative z-2 d-sm-none mt-3 ms-2" @click="rejectWithdrawal(withdrawal)">
                                    <i class="ci-close-octagon animate-target fs-sm me-1"></i>
                                    Reject
                                </button>
                            </template>
                        </div>
                    </div>
                </td>

                <td class="d-md-table-cell py-3">{{ formatCurrency(withdrawal.amount) }}</td>
                <td class="d-none d-md-table-cell py-3">{{ relativeTime(withdrawal.created_at) }}</td>

                <td class="d-none d-sm-table-cell text-end py-3 ps-0 ps-sm-3 pe-0" style="width: 120px">
                    <template v-if="withdrawal.status === 'pending'">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-secondary rounded-pill animate-scale" @click="approveWithdrawal(withdrawal)">
                                <i class="ci-check-circle animate-target fs-sm me-1"></i>
                                Approve
                            </button>

                            <button type="button" class="btn btn-sm btn-danger rounded-pill animate-scale" @click="rejectWithdrawal(withdrawal)">
                                <i class="ci-close-octagon animate-target fs-sm me-1"></i>
                                Reject
                            </button>
                        </div>
                    </template>

                    <template v-else-if="withdrawal.status === 'approved'">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill">
                            <i class="ci-check me-1"></i>
                            Approved
                        </button>
                    </template>

                    <template v-else>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill">
                            <i class="ci-close me-1"></i>
                            Rejected
                        </button>
                    </template>
                </td>
            </tr>

            <tr v-if="withdrawals.data.length === 0">
                <td class="py-3 ps-0 pt-4 pb-4" colspan="5">
                    <h2 class="h6 pt-2 mb-2">No withdrawal requests</h2>
                    <p class="fs-sm mb-4" style="max-width: 640px">
                        There are currently no pending withdrawal requests from users.
                        When users request withdrawals, they will appear here for review.
                    </p>
                    <button class="btn btn-outline-primary" @click="router.reload()">
                        <i class="ci-reload me-2"></i>
                        Refresh
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <Pagination :categories="withdrawals" />
    </div>
</template>
