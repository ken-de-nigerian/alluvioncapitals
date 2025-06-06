<script setup lang="ts">
    import {Head, Link, router, usePage} from "@inertiajs/vue3";
    import {route} from "ziggy-js";
    import Pagination from "../../../Components/Navigation/Pagination.vue";

    import iziToast from 'izitoast';
    import 'izitoast/dist/css/iziToast.min.css';

    const page = usePage();

    const props = defineProps({
        campaign: Object,
        rewards: Object
    })

    const formatDate = (dateString):string => {
        const date = new Date(dateString);

        // For ordinal suffix (1st, 2nd, etc.)
        const day = date.getDate();
        const suffix = ['st', 'nd', 'rd'][day % 10] || 'th';

        return date.toLocaleDateString('en-US', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        }).replace(/(\d+)/, `$1${suffix}`);
    }

    // Delete reward action
    const deleteReward = (reward: Reward) => {
        confirmAction("Deleting this reward is final and cannot be undone. Do you wish to continue?", () => {
            router.delete(route('user.campaigns.rewards.delete', [props.campaign.id, reward.id]), {
                preserveScroll: true,
            });
        });
    };

    // Toast notification helper
    const confirmAction = (message, callback) => {
        iziToast.question({
            timeout: false,
            close: false,
            overlay: true,
            displayMode: 'once',
            title: 'Confirm',
            message,
            position: 'topRight',
            buttons: [
                ['<button><b>YES</b></button>', (instance, toast) => {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    callback();
                }, true],
                ['<button>Cancel</button>', (instance, toast) => {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                }]
            ]
        });
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Thank-You Gifts: ${campaign.title}`"/>

    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Header -->
        <div class="d-md-flex align-items-center justify-content-between gap-3 pb-2 pb-sm-3 mb-md-2">
            <h1 class="h2 mb-md-0">Thank-You Gifts</h1>
            <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3">
                <div class="position-relative" style="min-width: 190px">
                    <Link :href="route('user.campaigns.rewards.add', campaign.id)" class="btn btn-dark rounded-pill w-100 animate-scale mt-lg-4">
                        <i class="ci-plus-circle fs-base animate-target me-2 ms-n1"></i>
                        Create a Thank-You Gift
                    </Link>
                </div>
            </div>
        </div>

        <table class="table align-middle fs-sm mb-5">
            <thead>
                <tr>
                    <th class="ps-0" scope="col">
                        <span class="fw-normal text-body">Title</span>
                    </th>

                    <th class="d-none d-md-table-cell" scope="col">
                        <span class="fw-normal text-body">Date Added</span>
                    </th>

                    <th class="d-none d-md-table-cell" scope="col">
                        <span class="fw-normal text-body">Status</span>
                    </th>

                    <th class="text-end d-none d-sm-table-cell" scope="col">
                        <span class="fw-normal text-body">Amount</span>
                    </th>

                    <th class="text-end pe-0" scope="col">
                        <span class="fw-normal text-body">Action</span>
                    </th>
                </tr>
            </thead>

            <tbody class="product-list">
                <tr v-for="reward in rewards.data" :key="rewards.id">
                    <td class="py-3 ps-0">
                        <div class="d-flex align-items-start align-items-md-center hover-effect-scale position-relative">
                            <div class="ps-2">
                                <span v-if="reward.status === 'active'" class="badge fs-xs text-success bg-success-subtle rounded-pill d-md-none mb-1">Active</span>
                                <span v-else class="badge fs-xs text-danger bg-danger-subtle rounded-pill d-md-none mb-1">Inactive</span>

                                <h6 class="product mb-1 mb-md-0">
                                    <a class="fs-sm fw-medium hover-effect-underline stretched-link">{{ reward.title }}</a>
                                </h6>

                                <div class="fs-body-emphasis d-sm-none mb-1">₦{{ reward.amount }}</div>
                                <div class="fs-body-emphasis d-md-none">{{ formatDate(reward.created_at) }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="d-none d-md-table-cell py-3">{{ formatDate(reward.created_at) }}</td>

                    <td class="d-none d-md-table-cell py-3">
                        <span v-if="reward.status === 'active'" class="badge fs-xs text-success bg-success-subtle rounded-pill">Active</span>
                        <span v-else class="badge fs-xs text-danger bg-danger-subtle rounded-pill">Inactive</span>
                    </td>

                    <td class="tendered text-end d-none d-sm-table-cell py-3">₦{{ reward.amount }}</td>

                    <td class="earning text-end py-3 pe-0">
                        <Link :href="route('user.campaigns.rewards.edit', reward.id)" class="btn btn-outline-secondary me-2">Edit</Link>
                        <button type="button" class="btn btn-outline-danger" @click="deleteReward(reward)">
                            Delete
                        </button>
                    </td>
                </tr>

                <tr v-if="rewards.data.length === 0">
                    <td class="py-3 ps-0 text-center pt-4 pb-4" colspan="5">
                        <p>No thank you gift added yet</p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <Pagination :categories="rewards" />
    </div>
</template>
