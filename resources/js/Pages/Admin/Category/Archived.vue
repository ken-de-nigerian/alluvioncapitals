<script setup lang="ts">
    import { Head, Link, usePage, router } from "@inertiajs/vue3";
    import { ref, watch } from "vue";
    import { route } from "ziggy-js";
    import debounce from 'lodash/debounce';
    import iziToast from 'izitoast';
    import 'izitoast/dist/css/iziToast.min.css';
    import Pagination from "../../../Components/Navigation/Pagination.vue";

    const page = usePage();

    const props = defineProps({
        categories: Object,
        filters: Object,
        activeCategories: Number,
        inactiveCategories: Number
    });

    const search = ref(props.filters?.search || '');
    const selectedItems = ref<number[]>([]);
    const selectAll = ref(false);

    // Debounced search function
    const performSearch = debounce(() => {
        router.get(route('admin.categories.archived'), { search: search.value }, {
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
            selectedItems.value = props.categories.data.map((category: any) => category.id);
        } else {
            selectedItems.value = [];
        }
    };

    // Update select all state based on current selections
    const updateSelectAllState = () => {
        selectAll.value = selectedItems.value.length === props.categories.data.length && props.categories.data.length > 0;
    };

    // Bulk archive action
    const bulkArchive = () => {
        if (selectedItems.value.length === 0) {
            showToast('error', "Please select rows to restore.");
            return;
        }

        confirmAction("Are you sure you want to restore the selected items?", () => {
            router.post(route('admin.categories.restore'), { ids: selectedItems.value }, {
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
            router.delete(route('admin.categories.destroy'), {
                data: { ids: selectedItems.value },
                preserveScroll: true,
                onSuccess: () => {
                    selectedItems.value = [];
                    selectAll.value = false;
                }
            });
        });
    };

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
    <Head :title="`${page.props.app.name} | Account - Categories`" />

    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Page title + Add Category button-->
        <div class="d-flex align-items-center justify-content-between pb-3 mb-1 mb-sm-2 mb-md-3">
            <h1 class="h2 me-3 mb-0">Categories</h1>
            <div class="nav">
                <Link class="nav-link animate-underline px-0 py-1 py-ms-2" :href="route('admin.categories.create')">
                    <i class="ci-plus fs-base me-1"></i>
                    <span class="animate-target">Add category</span>
                </Link>
            </div>
        </div>

        <!-- Nav tabs -->
        <div class="overflow-auto mb-3">
            <ul class="nav nav-pills flex-nowrap gap-2 text-nowrap pb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <Link :href="route('admin.categories.index')" :class="{ 'active': page.component === 'Admin/Category/Index' }" class="nav-link">
                        <i class="ci-bookmark fs-base opacity-75 me-2"></i>
                        Published ({{ activeCategories }})
                    </Link>
                </li>

                <li class="nav-item" role="presentation">
                    <Link :href="route('admin.categories.archived')" :class="{ 'active': page.component === 'Admin/Category/Archived' }" class="nav-link">
                        <i class="ci-archive fs-base opacity-75 me-2"></i>
                        Archived ({{ inactiveCategories }})
                    </Link>
                </li>
            </ul>
        </div>

        <!-- Tabs content -->
        <div class="tab-content">
            <div class="tab-pane fade" :class="{ 'show active': page.component === 'Admin/Category/Archived' }">
                <div class="row align-items-start align-items-md-center mb-4">
                    <!-- Left: Master Checkbox + Action Buttons -->
                    <div class="col-12 col-md-8">
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center flex-wrap">
                            <div class="nav-link fs-lg ps-0 pe-2 py-2 mt-n1 me-md-4">
                                <input type="checkbox" class="form-check-input" id="published-master" v-model="selectAll" @change="toggleSelectAll">
                                <label for="published-master" class="form-check-label fw-normal mt-1 ms-2">
                                    {{ selectAll ? 'Unselect all' : 'Select all the categories to apply the same action to them' }}
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
                            <input type="search" class="form-control form-icon-end"  v-model="search" placeholder="Search categories..." aria-label="Search categories">
                            <i class="ci-search position-absolute top-50 end-0 translate-middle-y me-3"></i>
                        </div>
                    </div>
                </div>

                <div class="vstack gap-4" id="publishedSelection">
                    <!-- Category list (table) -->
                    <table class="table align-middle fs-sm mb-0">
                        <thead>
                            <tr>
                                <th class="ps-0" scope="col">
                                    <div class="form-check position-relative z-1 fs-lg m-0">
                                        <input type="checkbox" class="form-check-input" id="select-all-header" v-model="selectAll" @change="toggleSelectAll">
                                    </div>
                                </th>

                                <th class="ps-0" scope="col">
                                    <span class="fw-normal text-body">Category</span>
                                </th>

                                <th class="ps-0" scope="col">
                                    <span class="fw-normal text-body">Campaigns</span>
                                </th>

                                <th class="d-none d-md-table-cell" scope="col">
                                    <span class="fw-normal text-body">Status</span>
                                </th>

                                <th class="d-none d-md-table-cell" scope="col">
                                    <span class="fw-normal text-body">Date Added</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="product-list">
                            <tr v-for="category in categories.data" :key="category.id">
                                <td class="py-3 ps-0">
                                    <div class="form-check position-relative z-1 fs-lg m-0">
                                        <input type="checkbox" class="form-check-input item-checkbox" :value="category.id" :checked="selectedItems.includes(category.id)" @change="toggleItemSelection(category.id)">
                                    </div>
                                </td>

                                <td class="py-3 ps-0">
                                    <div class="d-flex align-items-start align-items-md-center hover-effect-scale position-relative">
                                        <div class="ratio bg-body-secondary rounded-2 overflow-hidden flex-shrink-0" style="--cz-aspect-ratio: calc(52 / 66 * 100%); width: 66px">
                                            <img :src="category.image" class="hover-effect-target" :alt="category.name" loading="lazy">
                                        </div>

                                        <div class="ps-2 ms-1">
                                            <span class="fs-xs text-capitalize d-md-none mb-1" :class="{'text-success': category.status === 'active', 'text-danger': category.status === 'inactive'}">{{ category.status }}</span>

                                            <h6 class="product mb-1 mb-md-0">
                                                <Link class="fs-sm fw-medium hover-effect-underline stretched-link" :href="route('admin.categories.show', category.slug)">{{ category.name }}</Link>
                                            </h6>

                                            <div class="fs-body-emphasis d-md-none">{{ new Date(category.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' }) }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="d-md-table-cell py-3">
                                    <Link class="fs-sm fw-medium text-decoration-none badge bg-info-subtle" :href="route('admin.categories.show', category.slug)">{{ category.campaigns_count }}</Link>
                                </td>

                                <td class="d-none d-md-table-cell py-3">
                                    <span class="fs-xs text-capitalize" :class="{'text-success': category.status === 'active', 'text-danger': category.status === 'inactive'}">{{ category.status }}</span>
                                </td>

                                <td class="d-none d-md-table-cell py-3">{{ new Date(category.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' }) }}</td>
                            </tr>

                            <tr v-if="categories.data.length === 0">
                                <td class="py-3 ps-0 pt-4 pb-4" colspan="5">
                                    <h2 class="h6 pt-2 mb-2">You have no archived categories</h2>
                                    <p class="fs-sm mb-4" style="max-width: 640px">
                                        This means all your active categories are still visible to users. Archiving category helps you retain your information even when it's temporarily inactive.
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
                    <Pagination :categories="categories" />
                </div>
            </div>
        </div>
    </div>
</template>
