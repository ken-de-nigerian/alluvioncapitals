<script setup lang="ts">
    import { usePage, router } from "@inertiajs/vue3";
    import { ref, computed } from "vue";
    import { route } from "ziggy-js";
    import LoadingButton from "../../Components/Button/LoadingButton.vue";

    interface Comment {
        id: number;
        first_name: string;
        last_name: string;
        anonymous: boolean;
        amount: number;
        created_at: string;
    }

    interface CommentsData {
        data: Comment[];
        current_page: number;
        from: number;
        last_page: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        next_page_url: string | null;
        path: string;
        per_page: number;
        to: number;
        total: number;
    }

    interface Campaign {
        slug: string;
        first_name?: string;
        last_name?: string;
    }

    const page = usePage();
    const props = defineProps<{
        comments: CommentsData;
        campaign: Campaign;
    }>();

    const isLoading = ref(false);
    const commentsList = ref<Comment[]>(props.comments.data);
    const currentPage = ref(props.comments.current_page);
    const lastPage = ref(props.comments.last_page);
    const showEndMessage = ref(false);

    const hasMorePages = computed(() => {
        return currentPage.value < lastPage.value;
    });

    const loadMoreComments = async () => {
        if (isLoading.value || !hasMorePages.value) return;

        isLoading.value = true;

        try {
            const nextPage = currentPage.value + 1;

            await router.get(
                route('campaigns.show', {
                    slug: props.campaign.slug,
                    tab: 'comments',
                    page: nextPage,
                }),
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['comments'],
                    onSuccess: () => {
                        currentPage.value = nextPage;
                        commentsList.value = [...commentsList.value, ...page.props.comments.data];
                        lastPage.value = page.props.comments.last_page;
                        if (currentPage.value >= lastPage.value) {
                            showEndMessage.value = true;
                        }
                        // Reset loading state after successful update
                        isLoading.value = false;
                    },
                    onError: () => {
                        isLoading.value = false;
                    }
                }
            );
        } catch (error) {
            console.error('Error loading more comments:', error);
            isLoading.value = false;
        }
    };

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
    <div :class="[$page.url.includes('tab=comments') ? 'd-block' : 'd-none']">
        <h2 class="h3">Comments</h2>

        <div class="content-container">
            <div v-if="commentsList.length > 0">
                <div id="comment-list" class="list-group">
                    <div v-for="comment in commentsList" :key="comment.id" class="list-group-item position-relative">
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

                <div v-if="hasMorePages" class="mt-4 text-center">
                    <LoadingButton type="button" :custom-classes="'btn btn-outline-primary'" :processing="isLoading" @click="loadMoreComments">
                        Load More
                    </LoadingButton>
                </div>

                <p v-if="showEndMessage" class="text-center text-muted mt-3">
                    You've reached the end of the comments.
                </p>
            </div>

            <div v-else>
                <p class="text-muted">No comments yet. Donate and be the first to share words of support!</p>
            </div>
        </div>
    </div>
</template>
