<script setup lang="ts">
    import {Head, usePage} from "@inertiajs/vue3";
    import Pagination from "../../../Components/Navigation/Pagination.vue";

    const page = usePage()
    const props = defineProps({
        campaign: Object,
        comments: Object
    })

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

    const getInitials = (comment: Comment): string => {
        if (comment.anonymous) return 'A';
        const first = comment.first_name?.charAt(0) || '';
        const last = comment.last_name?.charAt(0) || '';
        return `${first}${last}`.toUpperCase();
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Campaign Comments: ${campaign.title}`"/>

    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Header -->
        <div class="d-flex align-items-center mb-4">
            <h2 class="h3 mb-0">Comments</h2>
        </div>

        <template v-if="props.comments.data.length > 0">
            <!-- Comment -->
            <div v-for="comment in props.comments.data" :key="comment.id" class="list-group mb-3">
                <div class="list-group-item position-relative">
                    <!-- Top-right badge -->
                    <span class="badge text-bg-info position-absolute top-2 end-0 mt-2 me-3 d-inline-flex align-items-center z-1">
                            Donated
                            <i class="ci-shield ms-1"></i>
                        </span>

                    <!-- Comment -->
                    <div class="pb-2 pt-2">
                        <div class="d-sm-flex align-items-center mb-3">
                            <div class="d-flex align-items-center pe-3">
                                <div class="ratio ratio-1x1 flex-shrink-0 bg-body-secondary rounded-circle overflow-hidden position-relative" style="width: 48px">
                                    <img v-if="page.props.lazySpinner" :src="page.props.lazySpinner" alt="Loading..." class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover lazy-placeholder">
                                    <img :src="`https://placehold.co/124x124/222934/ffffff?text=${getInitials(comment)}`" :alt="comment.anonymous ? 'Anonymous' : 'Donor avatar'" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover lazy-target" loading="lazy" @load="(e) => (e.target as HTMLElement).previousElementSibling?.remove()">
                                </div>

                                <div class="ps-3">
                                    <h6 class="mb-1">{{ comment.anonymous ? 'Anonymous' : `${comment.first_name} ${comment.last_name}` }}</h6>
                                    <div class="fs-xs text-body-secondary">{{ relativeTime(comment.created_at) }}</div>
                                </div>
                            </div>
                        </div>

                        <p class="fs-sm">{{ comment.comment }}</p>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination :categories="comments" />
        </template>

        <template v-else>
            <h2 class="h6 pt-4 mb-2">No comments yet</h2>
            <p class="fs-sm mb-4" style="max-width: 640px">
                This campaign hasn't received any comments yet. Comments will appear here once donors start engaging with your campaign.
            </p>
        </template>
    </div>
</template>
