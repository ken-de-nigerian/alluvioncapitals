<script setup lang="ts">
    import { usePage, router } from "@inertiajs/vue3";
    import { ref, computed } from "vue";
    import { route } from "ziggy-js";
    import LoadingButton from "../../Components/Button/LoadingButton.vue";

    interface Donation {
        id: number;
        first_name: string;
        last_name: string;
        anonymous: boolean;
        amount: number;
        created_at: string;
    }

    interface DonationsData {
        data: Donation[];
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
        donations: DonationsData;
        campaign: Campaign;
    }>();

    const isLoading = ref(false);
    const donationsList = ref<Donation[]>(props.donations.data);
    const currentPage = ref(props.donations.current_page);
    const lastPage = ref(props.donations.last_page);
    const showEndMessage = ref(false);

    const hasMorePages = computed(() => {
        return currentPage.value < lastPage.value;
    });

    const loadMoreDonations = async () => {
        if (isLoading.value || !hasMorePages.value) return;

        isLoading.value = true;

        try {
            const nextPage = currentPage.value + 1;

            await router.get(
                route('campaigns.show', {
                    slug: props.campaign.slug,
                    tab: 'donations',
                    page: nextPage,
                }),
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['donations'],
                    onSuccess: () => {
                        currentPage.value = nextPage;
                        donationsList.value = [...donationsList.value, ...page.props.donations.data];
                        lastPage.value = page.props.donations.last_page;
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
            console.error('Error loading more donations:', error);
            isLoading.value = false;
        }
    };

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG', {
            style: 'currency',
            currency: 'NGN',
            minimumFractionDigits: 2,
        });
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

    const getInitials = (donation: Donation): string => {
        if (donation.anonymous) return 'A';
        const first = donation.first_name?.charAt(0) || '';
        const last = donation.last_name?.charAt(0) || '';
        return `${first}${last}`.toUpperCase();
    };
</script>

<template>
    <div :class="[$page.url.includes('tab=donations') ? 'd-block' : 'd-none']">
        <h2 class="h3">Donations</h2>

        <div class="content-container">
            <div v-if="donationsList.length > 0">
                <div id="donation-list" class="list-group">
                    <div v-for="donation in donationsList" :key="donation.id" class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center pe-3">
                            <!-- Left side: Avatar and Name -->
                            <div class="d-flex align-items-center">
                                <div class="ratio ratio-1x1 flex-shrink-0 bg-body-secondary rounded-circle overflow-hidden position-relative" style="width: 48px">
                                    <img v-if="page.props.lazySpinner" :src="page.props.lazySpinner" alt="Loading..." class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover lazy-placeholder">
                                    <img :src="`https://placehold.co/124x124/222934/ffffff?text=${getInitials(donation)}`" :alt="donation.anonymous ? 'Anonymous' : 'Donor avatar'" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover lazy-target" loading="lazy" @load="(e) => (e.target as HTMLElement).previousElementSibling?.remove()">
                                </div>

                                <div class="ps-3">
                                    <h6 class="mb-1">{{ donation.anonymous ? 'Anonymous' : `${donation.first_name} ${donation.last_name}` }}</h6>
                                    <div class="fs-xs text-body-secondary">
                                        {{ formatCurrency(donation.amount) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Right side: Date -->
                            <div class="text-muted small ms-auto">
                                {{ relativeTime(donation.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="hasMorePages" class="mt-4 text-center">
                    <LoadingButton type="button" :custom-classes="'btn btn-outline-primary'" :processing="isLoading" @click="loadMoreDonations">
                        Load More
                    </LoadingButton>
                </div>

                <p v-if="showEndMessage" class="text-center text-muted mt-3">
                    You've reached the end of the donations.
                </p>
            </div>

            <div v-else>
                <p class="text-muted">No donations yet. Be the first to contribute!</p>
            </div>
        </div>
    </div>
</template>
