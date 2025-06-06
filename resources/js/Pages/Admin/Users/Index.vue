<script setup lang="ts">
    import {Head, usePage, Link, router} from "@inertiajs/vue3";
    import {route} from "ziggy-js";
    import Pagination from "../../../Components/Navigation/Pagination.vue";
    import { ref, watch } from 'vue';
    import debounce from 'lodash/debounce';

    const page = usePage()
    const props = defineProps({
        users: Object,
        filters: Object
    })

    const search = ref(props.filters.search)
    const sort = ref(props.filters.sort || 'newest')

    const getInitials = (user: any): string => {
        const first = user?.first_name?.charAt(0) || '';
        const last = user?.last_name?.charAt(0) || '';
        return `${first}${last}`.toUpperCase();
    };

    // Debounced search
    watch(search, debounce((value) => {
        router.get(route('admin.users.index'),
            { search: value, sort: sort.value },
            { preserveState: true, replace: true }
        )
    }, 500))

    // Immediate sort
    watch(sort, (value) => {
        router.get(route('admin.users.index'),
            { search: search.value, sort: value },
            { preserveState: true, replace: true }
        )
    })
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Users`" />

    <!-- Users content -->
    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Title -->
        <div class="row">
            <div class="col-12 mb-2 mb-sm-3">
                <div class="d-sm-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-3 mb-sm-0">Users</h1>
                </div>
            </div>
        </div>

        <!-- Search and select START -->
        <div class="row g-3 align-items-center justify-content-between mb-4">
            <!-- Search -->
            <div class="col-md-8">
                <form class="rounded position-relative" @submit.prevent>
                    <input v-model="search" class="form-control pe-5" type="search" placeholder="Search by name or email" aria-label="Search">
                    <button class="btn border-0 px-3 py-0 position-absolute top-50 end-0 translate-middle-y" type="button">
                        <i class="fas fa-search fs-6"></i>
                    </button>
                </form>
            </div>

            <!-- Select option -->
            <div class="col-md-3">
                <select v-model="sort" class="form-select" aria-label="Sort by">
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>
        </div>
        <!-- Search and select END -->

        <!-- user list START -->
        <div class="row g-4 mb-4">
            <!-- Card item -->
            <div v-for="user in users.data" :key="user.id" class="col-md-6 col-lg-4 col-xxl-4">
                <div class="card h-100 animate-underline hover-effect-opacity hover-effect-scale rounded-4 overflow-hidden">
                    <div class="card-img-top position-relative bg-body-tertiary overflow-hidden">
                        <Link class="ratio d-block hover-effect-target" style="--cz-aspect-ratio: calc(220 / 304 * 100%)" :href="route('admin.users.view.profile', user.id)">
                            <img :src="user?.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${getInitials(user)}`" alt="Image" loading="lazy">
                        </Link>
                    </div>

                    <div class="card-body p-3">
                        <div class="d-flex min-w-0 justify-content-between gap-2 gap-sm-3 mb-2">
                            <h3 class="nav min-w-0 mb-0">
                                <Link class="nav-link text-truncate p-0" :href="route('admin.users.view.profile', user.id)">
                                    <span class="text-truncate animate-target">{{ user.first_name }} {{ user.last_name }}</span>
                                </Link>
                            </h3>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="nav align-items-center gap-1 fs-xs">
                                <Link class="nav-link fs-xs text-body gap-1 p-0" :href="route('admin.users.view.profile', user.id)">
                                    <div class="flex-shrink-0 border rounded-circle" style="width: 22px">
                                        <div class="ratio ratio-1x1 rounded-circle overflow-hidden">
                                            <img :src="user?.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${getInitials(user)}`" alt="Avatar">
                                        </div>
                                    </div>
                                </Link>

                                <div class="text-body-secondary">role:</div>
                                <Link class="nav-link fs-xs text-body p-0" :href="route('admin.users.view.profile', user.id)">{{ user.role }}</Link>
                            </div>
                            <div v-if="user.status === 'active'" class="fs-xs badge bg-success-subtle rounded-pill">Active</div>
                            <div v-else class="fs-xs badge bg-danger-subtle rounded-pill">Banned</div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="users.data.length === 0">
                <h2 class="h6 pt-2 mb-2">No users found</h2>
                <p class="fs-sm mb-4" style="max-width: 640px">
                    There are currently no users registered in the system. When users sign up,
                    they will appear here for management.
                </p>
                <Link :href="route('register')" class="btn btn-dark">
                    <i class="ci-user-plus fs-base ms-n1 me-2"></i>
                    Register User
                </Link>
            </div>
        </div>

        <!-- Pagination START -->
        <Pagination :categories="users" />
    </div>
</template>
