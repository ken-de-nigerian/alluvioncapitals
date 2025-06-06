<script setup lang="ts">
    import { Head, usePage, Link, router } from "@inertiajs/vue3";
    import { route } from "ziggy-js";
    import Pagination from "../../../Components/Navigation/Pagination.vue";
    import iziToast from "izitoast";
    import "izitoast/dist/css/iziToast.min.css";
    import { onMounted, onUnmounted, ref } from "vue";
    import GLightbox from "glightbox";
    import "glightbox/dist/css/glightbox.min.css";

    // Define interfaces for props
    interface Campaign {
        id: number;
        title: string;
    }

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

    const page = usePage();

    const props = defineProps<{
        campaign: Campaign;
        updates: Updates;
    }>();

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

    // Delete update action
    const deleteUpdate = (update: Update) => {
        confirmAction(
            "Deleting this update is final and cannot be undone. Do you wish to continue?",
            () => {
                router.delete(
                    route("user.campaigns.updates.delete", [props.campaign.id, update.id]),
                    {
                        preserveScroll: true,
                    }
                );
            }
        );
    };

    // Toast notification helper
    const confirmAction = (message: string, callback: () => void) => {
        iziToast.question({
            timeout: false,
            close: false,
            overlay: true,
            displayMode: "once",
            title: "Confirm",
            message,
            position: "topRight",
            buttons: [
                [
                    "<button><b>YES</b></button>",
                    (instance, toast) => {
                        instance.hide({ transitionOut: "fadeOut" }, toast, "button");
                        callback();
                    },
                    true,
                ],
                [
                    "<button>Cancel</button>",
                    (instance, toast) => {
                        instance.hide({ transitionOut: "fadeOut" }, toast, "button");
                    },
                ],
            ],
        });
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
    <Head :title="`${page.props.app.name} | Account - Campaign Updates: ${campaign.title}`" />

    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Header -->
        <div class="d-flex align-items-center mb-4">
            <h2 class="h3 mb-0">Updates</h2>
            <Link :href="route('user.campaigns.updates.add', campaign.id)" class="btn btn-secondary ms-auto">
                <i class="ci-plus-circle fs-base ms-n1 me-2"></i>
                Add Update
            </Link>
        </div>

        <template v-if="props.updates.data.length > 0">
            <!-- Update -->
            <div v-for="update in props.updates.data" :key="update.id" class="border-bottom py-3 mb-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="text-nowrap me-3">
                        <span class="h6 mb-0">{{ update.title }}</span>
                    </div>
                    <span class="text-body-secondary fs-sm ms-auto">{{ formatDate(update.created_at) }}</span>
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

                <div class="nav align-items-center">
                    <Link :href="route('user.campaigns.updates.edit', update.id)" class="nav-link text-body-secondary animate-scale px-0 ms-auto me-n1">
                        <i class="ci-edit-3 text-success fs-base animate-target me-1"></i>
                        Edit
                    </Link>

                    <hr class="vr my-2 mx-3" />

                    <button type="button" class="nav-link text-body-secondary animate-scale px-0 ms-n1" @click="deleteUpdate(update)">
                        <i class="ci-close-octagon text-danger fs-base animate-target me-1"></i>
                        Delete
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination :categories="props.updates" />
        </template>

        <template v-else>
            <h2 class="h6 pt-4 mb-2">No campaign updates yet</h2>
            <p class="fs-sm mb-4" style="max-width: 640px">
                You haven't shared any updates for this campaign yet. Keeping your donors informed helps
                build trust and shows the impact their support is making.
            </p>
        </template>
    </div>
</template>
