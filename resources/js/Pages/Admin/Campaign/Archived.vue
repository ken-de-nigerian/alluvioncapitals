<script setup lang="ts">
    import { Head, Link, usePage, router } from "@inertiajs/vue3";
    import { ref, watch } from "vue";
    import { route } from "ziggy-js";
    import debounce from 'lodash/debounce';
    import iziToast from 'izitoast';
    import 'izitoast/dist/css/iziToast.min.css';
    import CampaignProgress from "../../../Components/CampaignComponents/CampaignProgress.vue";
    import Pagination from "../../../Components/Navigation/Pagination.vue";
    import DashboardCampaignSkeleton from "../../../Components/CampaignComponents/DashboardCampaignSkeleton.vue";

    const page = usePage();

    const props = defineProps({
        campaigns: Object,
        filters: Object,
        activeCampaigns: Number,
        inactiveCampaigns: Number
    });

    const search = ref(props.filters?.search || '');
    const selectedItems = ref<number[]>([]);
    const selectAll = ref(false);

    // Debounced search function
    const performSearch = debounce(() => {
        router.get(route('admin.campaigns.archived'), { search: search.value }, {
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

    // Handle individual checkbox selection
    const toggleItemSelection = (id: number) => {
        if (selectedItems.value.includes(id)) {
            selectedItems.value = selectedItems.value.filter(itemId => itemId !== id);
        } else {
            selectedItems.value = [...selectedItems.value, id];
        }
        updateSelectAllState();
    };

    // Handle selects all checkboxes
    const toggleSelectAll = () => {
        if (selectAll.value) {
            selectedItems.value = props.campaigns.data.map((campaign: any) => campaign.id);
        } else {
            selectedItems.value = [];
        }
    };

    const toggleSelectionByImage = (id: number, event: Event) => {
        // Prevent the default link behavior if the image is wrapped in a link
        event.preventDefault();
        toggleItemSelection(id);
    };

    // Update select all state based on current selections
    const updateSelectAllState = () => {
        selectAll.value = selectedItems.value.length === props.campaigns.data.length && props.campaigns.data.length > 0;
    };

    // Bulk archive action
    const bulkArchive = () => {
        if (selectedItems.value.length === 0) {
            showToast('error', "Please select rows to restore.");
            return;
        }

        confirmAction("Are you sure you want to restore the selected items?", () => {
            router.post(route('admin.campaigns.restore'), { ids: selectedItems.value }, {
                preserveScroll: true,
                onSuccess: () => {
                    selectedItems.value = [];
                    selectAll.value = false;
                }
            });
        });
    };

    // Bulk delete action
    const bulkDelete = () => {
        if (selectedItems.value.length === 0) {
            showToast('error', "Please select rows to delete.");
            return;
        }

        confirmAction("Are you sure you want to delete the selected items? This action cannot be undone.", () => {
            router.delete(route('admin.campaigns.destroy'), {
                data: { ids: selectedItems.value },
                preserveScroll: true,
                onSuccess: () => {
                    selectedItems.value = [];
                    selectAll.value = false;
                }
            });
        });
    };

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

    // Toast notification helper
    const showToast = (type: string, message: string) => {
        const options = {
            message,
            position: 'topRight',
            timeout: 4000,
            progressBar: true,
        };

        switch (type) {
            case 'success':
                iziToast.success({ ...options, title: 'Success' });
                break;
            case 'error':
                iziToast.error({ ...options, title: 'Error' });
                break;
            case 'info':
                iziToast.info({ ...options, title: 'Info' });
                break;
            case 'warning':
                iziToast.warning({ ...options, title: 'Warning' });
                break;
            default:
                iziToast.show({ ...options, title: 'Notice' });
        }
    };

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
    <Head :title="`${page.props.app.name} | Account - Campaigns`" />

    <div class="col-lg-9">
        <!-- Page title -->
        <div class="d-flex align-items-center justify-content-between pb-3 mb-1 mb-sm-2 mb-md-3">
            <h1 class="h2 me-3 mb-0">Campaigns</h1>
        </div>

        <!-- Nav tabs -->
        <div class="overflow-auto mb-3">
            <ul class="nav nav-pills flex-nowrap gap-2 text-nowrap pb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <Link :href="route('admin.campaigns.index')" :class="{ 'active': page.component === 'Admin/Campaign/Index' }" class="nav-link">
                        <i class="ci-bookmark fs-base opacity-75 me-2"></i>
                        Published ({{ activeCampaigns }})
                    </Link>
                </li>

                <li class="nav-item" role="presentation">
                    <Link :href="route('admin.campaigns.archived')" :class="{ 'active': page.component === 'Admin/Campaign/Archived' }" class="nav-link">
                        <i class="ci-archive fs-base opacity-75 me-2"></i>
                        Archived ({{ inactiveCampaigns }})
                    </Link>
                </li>
            </ul>
        </div>

        <!-- Tabs content -->
        <div class="tab-content">
            <div class="tab-pane fade" :class="{ 'show active': page.component === 'Admin/Campaign/Archived' }">
                <!-- Loading state -->
                <template v-if="!campaigns">
                    <!-- Campaign skeleton -->
                    <DashboardCampaignSkeleton />
                </template>

                <!-- Loaded state -->
                <template v-else>
                    <div class="row align-items-start align-items-md-center mb-4" v-if="campaigns.data.length > 0">
                        <!-- Left: Master Checkbox + Action Buttons -->
                        <div class="col-12 col-md-8">
                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center flex-wrap">
                                <div class="nav-link fs-lg ps-0 pe-2 py-2 mt-n1 me-md-4">
                                    <input type="checkbox" class="form-check-input" id="published-master" v-model="selectAll" @change="toggleSelectAll">
                                    <label for="published-master" class="form-check-label fw-normal mt-1 ms-2">
                                        {{ selectAll ? 'Unselect all' : 'Select all the campaigns to apply the same action to them' }}
                                    </label>
                                </div>

                                <div class="d-flex flex-wrap" :class="{ 'd-none': selectedItems.length === 0 }" id="published-action-buttons">
                                    <button class="nav-link position-relative px-0 pe-sm-2 py-2 me-4" @click="bulkArchive">
                                        <i class="ci-rotate-cw fs-base me-2"></i>
                                        <span class="hover-effect-underline d-md-inline">
                                            Restore (<span id="archiveCount">{{ selectedItems.length }}</span>)
                                        </span>
                                    </button>

                                    <button class="nav-link position-relative text-danger px-0 py-2" @click="bulkDelete">
                                        <i class="ci-trash fs-base me-1"></i>
                                        <span class="hover-effect-underline d-md-inline">
                                            Delete (<span id="deletePublishedCount">{{ selectedItems.length }}</span>)
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Search -->
                        <div class="col-12 col-md-4 mt-3 mt-md-0">
                            <div class="position-relative mb-3" style="max-width: 100%;">
                                <input type="search" class="form-control form-icon-end"  v-model="search" placeholder="Search campaigns..." aria-label="Search campaigns">
                                <i class="ci-search position-absolute top-50 end-0 translate-middle-y me-3"></i>
                            </div>
                        </div>
                    </div>

                    <div class="vstack gap-4" id="publishedSelection" v-if="campaigns.data.length > 0">
                        <!-- Item -->
                        <div v-for="campaign in campaigns.data" :key="campaign.id" class="d-sm-flex align-items-center">
                            <div
                                class="d-inline-flex position-relative z-2 pt-1 pb-2 ps-2 p-sm-0 ms-2 ms-sm-0 me-sm-2">
                                <div class="form-check position-relative z-1 fs-lg m-0">
                                    <input type="checkbox" class="form-check-input item-checkbox" :value="campaign.id" :checked="selectedItems.includes(campaign.id)" @change="toggleItemSelection(campaign.id)">
                                </div>
                                <span class="position-absolute top-0 start-0 w-100 h-100 bg-body border rounded d-sm-none"></span>
                            </div>

                            <article class="card w-100">
                                <div class="d-sm-none" style="margin-top: -44px"></div>
                                <div class="row g-0">
                                    <div class="col-sm-4 col-md-3 rounded overflow-hidden pb-2 pb-sm-0 pe-sm-2">
                                        <div class="position-relative d-flex h-100 bg-body-tertiary" style="min-height: 174px" @click="toggleSelectionByImage(campaign.id, $event)" :class="{ 'cursor-pointer': true }">
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

                                                <!-- Footer list -->
                                                <ul class="list-unstyled flex-row flex-wrap justify-content-end fs-sm mb-5">
                                                    <li class="d-flex align-items-center me-2 me-md-3">
                                                        <i class="ci-clock fs-base me-1"></i>
                                                        {{ campaign.days_left_text }}
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

                        <!-- Pagination -->
                        <Pagination :categories="campaigns" />
                    </div>

                    <div v-if="campaigns.data.length === 0">
                        <h2 class="h6 pt-2 mb-2">You have no archived campaigns</h2>
                        <p class="fs-sm mb-4" style="max-width: 640px">
                            Archived campaigns are temporarily hidden from public view. When you archive a campaign, you
                            preserve all its data while keeping your active campaigns list clean.
                        </p>
                        <Link :href="route('campaigns.add.details')" class="btn btn-dark">
                            <i class="ci-plus fs-base ms-n1 me-2"></i>
                            Create Campaign
                        </Link>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
