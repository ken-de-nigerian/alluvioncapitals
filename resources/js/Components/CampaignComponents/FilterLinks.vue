<script setup lang="ts">
    import {Link} from "@inertiajs/vue3";
</script>

<template>
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-lg-4 mb-4">
        <!-- Tab filters (left side) -->
        <div class="overflow-x-auto">
            <ul class="nav nav-pills flex-nowrap gap-2 text-nowrap pb-3">
                <li class="nav-item">
                    <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=all') || !$page.url.includes('tab=') }" aria-current="page" href="?tab=all" preserve-scroll preserve-state>
                        All campaigns
                    </Link>
                </li>

                <li class="nav-item">
                    <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=latest') }" href="?tab=latest" preserve-scroll preserve-state>
                        Latest
                    </Link>
                </li>

                <li class="nav-item">
                    <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=featured') }" href="?tab=featured" preserve-scroll preserve-state>
                        Featured
                    </Link>
                </li>

                <li class="nav-item">
                    <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=popular') }" href="?tab=popular" preserve-scroll preserve-state>
                        Popular
                    </Link>
                </li>

                <li class="nav-item">
                    <Link class="nav-link" :class="{ 'active': $page.url.includes('tab=ended') }" href="?tab=ended" preserve-scroll preserve-state>
                        Ended
                    </Link>
                </li>
            </ul>
        </div>
    </div>
</template>
