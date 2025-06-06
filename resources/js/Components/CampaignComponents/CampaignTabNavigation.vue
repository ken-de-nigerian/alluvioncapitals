<script setup lang="ts">
    import { Link } from '@inertiajs/vue3'

    const props = defineProps({
        campaign: Object,
        currentTab: {
            type: String,
            default: 'description'
        }
    })
</script>

<template>
    <!-- Nav tabs -->
    <ul class="nav nav-underline flex-nowrap border-bottom" role="tablist">
        <li class="nav-item me-md-1" role="presentation">
            <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=description') || !$page.url.includes('tab=') }" aria-current="page" href="?tab=description" preserve-scroll preserve-state>
                Description
            </Link>
        </li>

        <li class="nav-item me-md-1" role="presentation">
            <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=donations') }" aria-current="page" href="?tab=donations" preserve-scroll preserve-state>
                Donations
            </Link>
        </li>

        <li class="nav-item me-md-1" role="presentation">
            <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=updates') }" aria-current="page" href="?tab=updates" preserve-scroll preserve-state>
                Updates
            </Link>
        </li>

        <li class="nav-item" role="presentation">
            <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=comments') }" aria-current="page" href="?tab=comments" preserve-scroll preserve-state>
                Comments<span class="d-md-inline ms-2">({{ campaign.comments_count }})</span>
            </Link>
        </li>
    </ul>
</template>
