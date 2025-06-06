<script setup lang="ts">
    import {Link, usePage} from "@inertiajs/vue3";
    import {route} from "ziggy-js";
    const page = usePage();
</script>

<template>
    <div class="overflow-auto mb-3">
        <ul class="nav nav-pills flex-nowrap gap-2 text-nowrap pb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <Link :href="route('user.profile')" :class="{ 'active': page.component === 'User/Profile/Index' }" class="nav-link">
                    <i class="ci-user fs-base opacity-75 me-2"></i>
                    Profile Settings
                </Link>
            </li>

            <li class="nav-item" role="presentation">
                <Link :href="route('user.profile.security')" :class="{ 'active': page.component === 'User/Profile/Security' }" class="nav-link">
                    <i class="ci-check-shield fs-base opacity-75 me-2"></i>
                    Account Security
                </Link>
            </li>

            <li class="nav-item" role="presentation">
                <Link :href="route('user.notification.settings')" :class="{ 'active': page.component === 'User/Profile/NotificationSettings' }" class="nav-link">
                    <i class="ci-mail fs-base opacity-75 me-2"></i>
                    Notification Settings
                </Link>
            </li>
        </ul>
    </div>
</template>
