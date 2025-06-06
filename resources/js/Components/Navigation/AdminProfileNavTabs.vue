<script setup lang="ts">
    import {Link, usePage} from "@inertiajs/vue3";
    import {route} from "ziggy-js";
    const page = usePage();
</script>

<template>
    <div class="overflow-auto mb-3">
        <ul class="nav nav-pills flex-nowrap gap-2 text-nowrap pb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <Link :href="route('admin.profile')" :class="{ 'active': page.component === 'Admin/Profile/Index' }" class="nav-link">
                    <i class="ci-user fs-base opacity-75 me-2"></i>
                    Profile Settings
                </Link>
            </li>

            <li class="nav-item" role="presentation">
                <Link :href="route('admin.profile.security')" :class="{ 'active': page.component === 'Admin/Profile/Security' }" class="nav-link">
                    <i class="ci-check-shield fs-base opacity-75 me-2"></i>
                    Account Security
                </Link>
            </li>

            <li class="nav-item" role="presentation">
                <Link :href="route('admin.test.email')" :class="{ 'active': page.component === 'Admin/Profile/TestEmail' }" class="nav-link">
                    <i class="ci-mail fs-base opacity-75 me-2"></i>
                    Email Settings
                </Link>
            </li>
        </ul>
    </div>
</template>
