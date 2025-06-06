<script setup lang="ts" >
    import { computed } from 'vue'
    import {Link, usePage} from '@inertiajs/vue3'
    import {route} from "ziggy-js";

    const page = usePage();

    const props = defineProps({
        user: Object
    })

    const initials = computed(() => {
        const first = props.user?.first_name?.charAt(0) || ''
        const last = props.user?.last_name?.charAt(0) || ''
        return `${first}${last}`.toUpperCase()
    })

    const truncate = (text: string, maxLength: number): string => {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };
</script>

<template>

    <template v-if="props.user">
        <div v-if="props.user.role === 'admin'" class="dropdown d-md-block mx-1">
            <a class="btn btn-icon hover-effect-scale position-relative border rounded-circle overflow-hidden" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="My account">
                <img :src="props.user?.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${initials}`" class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 object-fit-cover" alt="Avatar" loading="lazy">
            </a>

            <ul class="dropdown-menu dropdown-menu-end" style="--cz-dropdown-spacer: .625rem">
                <li><span class="h6 dropdown-header">{{  props.user?.first_name }} {{  props.user?.last_name }}</span></li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': page.component === 'Admin/Index' }" :href="route('admin.dashboard')">
                        <i class="ci-grid fs-base opacity-75 me-2"></i>
                        Dashboard
                    </Link>
                </li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': ['Admin/Campaign', 'Admin/Rewards', 'Admin/Updates'].some(prefix => page.component.startsWith(prefix)) }" :href="route('admin.campaigns.index')">
                        <i class="ci-gift fs-base opacity-75 me-2"></i>
                        Campaigns ({{ page.props.allCampaignCount }})
                    </Link>
                </li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': page.component.startsWith('Admin/Donations') }" :href="route('admin.payments.donations')">
                        <i class="ci-dollar-sign fs-base opacity-75 me-2"></i>
                        Donations
                    </Link>
                </li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': page.component.startsWith('Admin/Category') }" :href="route('admin.categories.index')">
                        <i class="ci-layers-2 fs-base opacity-75 me-2"></i>
                        Categories
                    </Link>
                </li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': page.component.startsWith('Admin/Profile') }" :href="route('admin.profile')">
                        <i class="ci-settings fs-base opacity-75 me-2"></i>
                        Settings
                    </Link>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <!--suppress HtmlWrongAttributeValue -->
                    <Link as="button" method="post" class="dropdown-item" :href="route('logout')">
                        <i class="ci-log-out fs-base opacity-75 me-2"></i>
                        Log out
                    </Link>
                </li>
            </ul>
        </div>

        <div v-if="props.user.role === 'user'" class="dropdown d-md-block mx-1">
            <a class="btn btn-icon hover-effect-scale position-relative border rounded-circle overflow-hidden" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="My account">
                <img :src="props.user?.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${initials}`" class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 object-fit-cover" alt="Avatar" loading="lazy">
            </a>

            <ul class="dropdown-menu dropdown-menu-end" style="--cz-dropdown-spacer: .625rem">
                <li><span class="h6 dropdown-header">{{  props.user?.first_name }} {{ truncate(props.user?.last_name, 10) }}</span></li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': page.component === 'User/Index' }" :href="route('user.dashboard')">
                        <i class="ci-grid fs-base opacity-75 me-2"></i>
                        Dashboard
                    </Link>
                </li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': ['User/Campaign'].some(prefix => page.component.startsWith(prefix)) }" :href="route('user.campaigns.index')">
                        <i class="ci-gift fs-base opacity-75 me-2"></i>
                        Campaigns ({{ page.props.allUserCampaignCount }})
                    </Link>
                </li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': page.component.startsWith('User/Donations') }" :href="route('user.payments.donations')">
                        <i class="ci-dollar-sign fs-base opacity-75 me-2"></i>
                        Donations
                    </Link>
                </li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': page.component.startsWith('User/Withdrawals') }" :href="route('user.payments.withdrawals')">
                        <i class="ci-credit-card fs-base opacity-75 me-2"></i>
                        Withdrawals
                    </Link>
                </li>

                <li>
                    <Link class="dropdown-item" :class="{ 'active': page.component.startsWith('User/Profile') }" :href="route('user.profile')">
                        <i class="ci-settings fs-base opacity-75 me-2"></i>
                        Settings
                    </Link>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <!--suppress HtmlWrongAttributeValue -->
                    <Link as="button" method="post" class="dropdown-item" :href="route('logout')">
                        <i class="ci-log-out fs-base opacity-75 me-2"></i>
                        Log out
                    </Link>
                </li>
            </ul>
        </div>
    </template>

    <template v-else>
        <!-- Account button visible on screens > 768px wide (md breakpoint) -->
        <Link class="btn btn-icon btn-lg fs-lg btn-outline-secondary border-0 rounded-circle animate-shake d-none d-md-inline-flex" :href="route('login')">
            <i class="ci-user animate-target"></i>
            <span class="visually-hidden">Account</span>
        </Link>
    </template>
</template>
