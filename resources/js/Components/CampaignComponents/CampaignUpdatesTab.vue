<script setup lang="ts">
    import {router, usePage} from "@inertiajs/vue3";
    import LoadingButton from "../../Components/Button/LoadingButton.vue";

    import GLightbox from "glightbox";
    import "glightbox/dist/css/glightbox.min.css";

    import {computed, onMounted, onUnmounted, ref} from "vue";
    import {route} from "ziggy-js";

    interface Update {
        id: number;
        title: string;
        message: string;
        created_at: string;
        images?: string; // JSON string of image URLs
    }

    interface Updates {
        data: Update[];
    }

    interface Campaign {
        slug: string;
    }

    const page = usePage();

    const props = defineProps<{
        updates: Updates;
        campaign: Campaign;
    }>();

    const isLoading = ref(false);
    const updatesList = ref<Update[]>(props.updates.data);
    const currentPage = ref(props.updates.current_page);
    const lastPage = ref(props.updates.last_page);
    const showEndMessage = ref(false);

    const hasMorePages = computed(() => {
        return currentPage.value < lastPage.value;
    });

    const loadMoreUpdates = async () => {
        if (isLoading.value || !hasMorePages.value) return;

        isLoading.value = true;

        try {
            const nextPage = currentPage.value + 1;

            await router.get(
                route('campaigns.show', {
                    slug: props.campaign.slug,
                    tab: 'updates',
                    page: nextPage,
                }),
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['updates'],
                    onSuccess: () => {
                        currentPage.value = nextPage;
                        updatesList.value = [...updatesList.value, ...page.props.updates.data];
                        lastPage.value = page.props.updates.last_page;
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
            console.error('Error loading more updates:', error);
            isLoading.value = false;
        }
    };

    // Format date with validation
    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return "Invalid Date";
        }

        const day = date.getDate();
        const suffix = ["st", "nd", "rd"][day % 10 - 1] || "th";

        return date
            .toLocaleDateString("en-US", {
                day: "numeric",
                month: "short",
                year: "numeric",
            })
            .replace(/(\d+)/, `$1${suffix}`);
    };

    let lightbox: GLightbox | null = null;
    const isModalOpen = ref(false);

    const handleLightboxOpen = () => {
        isModalOpen.value = true;
    };

    const handleLightboxClose = () => {
        isModalOpen.value = false;
    };

    onMounted(() => {
        lightbox = GLightbox({
            selector: "[data-glightbox]",
        });

        document.addEventListener("glightbox_open", handleLightboxOpen);
        document.addEventListener("glightbox_close", handleLightboxClose);
    });

    onUnmounted(() => {
        if (lightbox) {
            lightbox.destroy();
            lightbox = null;
        }

        document.removeEventListener("glightbox_open", handleLightboxOpen);
        document.removeEventListener("glightbox_close", handleLightboxClose);
    });

    // Parse images for a single update
    const parseImages = (imageString?: string): string[] => {
        if (!imageString) return [];
        try {
            return JSON.parse(imageString);
        } catch (error) {
            console.error("Failed to parse images:", error);
            return [];
        }
    };
</script>

<template>
    <div :class="[$page.url.includes('tab=updates') ? 'd-block' : 'd-none']">
        <h2 class="h3">Updates</h2>

        <div class="content-container">
            <div v-if="updatesList.length > 0">
                <div id="update-list" class="list-group">
                    <div v-for="update in updatesList" :key="update.id" class="list-group-item">
                        <div class="py-3 pb-0">
                            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center mb-3">
                                <div class="text-nowrap me-sm-3 mb-2 mb-sm-0">
                                    <span class="h6 mb-0">{{ update.title }}</span>
                                </div>
                                <span class="text-body-secondary fs-sm ms-sm-auto">{{ formatDate(update.created_at) }}</span>
                            </div>

                            <p class="fs-sm">{{ update.message }}</p>

                            <div v-if="parseImages(update.images).length > 0" class="d-flex gap-2 mb-3">
                                <a v-for="(image, index) in parseImages(update.images)" :key="index" class="hover-effect-scale hover-effect-opacity position-relative d-block w-100 rounded overflow-hidden" :href="image" style="max-width: 112px" data-glightbox :data-gallery="`update-images-${update.id}`">
                                    <i class="ci-zoom-in hover-effect-target fs-4 text-white position-absolute top-50 start-50 translate-middle opacity-0 z-2"></i>
                                    <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
                                    <div class="ratio ratio-1x1 hover-effect-target bg-body-tertiary">
                                        <img :src="image" alt="Image" loading="lazy" />
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="hasMorePages" class="mt-4 text-center">
                    <LoadingButton type="button" :custom-classes="'btn btn-outline-primary'" :processing="isLoading" @click="loadMoreUpdates">
                        Load More
                    </LoadingButton>
                </div>

                <p v-if="showEndMessage" class="text-center text-muted mt-3">
                    You've reached the end of the updates.
                </p>
            </div>

            <div v-else>
                <p class="text-muted">No updates yet. Check back later!</p>
            </div>
        </div>
    </div>
</template>
