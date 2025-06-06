<script setup lang="ts">
    import { Head, usePage, useForm, Link } from "@inertiajs/vue3";
    import { ref, computed, onMounted } from "vue";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import { route } from "ziggy-js";

    const page = usePage();

    const props = defineProps({
        media: {
            type: Object,
            default: () => ({ campaign_images: [] })
        }
    });

    const fileInput = ref(null);
    const fileError = ref('');

    // Track existing media URLs and new files separately
    const existingMedia = ref(Array.isArray(props.media?.campaign_images) ? [...props.media.campaign_images] : []);
    const newFiles = ref<File[]>([]);
    const newPreviews = ref<string[]>([]);

    const form = useForm({
        photos: [], // Only new files to upload
        existing_photos: [], // Existing paths to keep
        deleted_photos: [], // Existing paths to delete
        campaign_video: '',
    });

    // Combined previews for display
    const allPreviews = computed(() => {
        return [
            ...existingMedia.value,
            ...newPreviews.value
        ];
    });

    // Handle file selection
    const handleFileSelect = (event) => {
        const files = Array.from(event.target.files);
        if (!files.length) return;

        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        const maxSize = 2 * 1024 * 1024; // 2MB
        const errors = [];

        files.forEach(file => {
            if (!validTypes.includes(file.type)) {
                errors.push(`File ${file.name} is not a JPEG or PNG image.`);
                return;
            }

            if (file.size > maxSize) {
                errors.push(`File ${file.name} exceeds 2MB size limit.`);
                return;
            }

            // Add to new files
            newFiles.value.push(file);

            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                newPreviews.value.push(e.target.result as string);
            };
            reader.readAsDataURL(file);
        });

        if (errors.length) {
            fileError.value = errors.join('\n');
        } else {
            fileError.value = '';
            updateFormData();
        }
    };

    // Remove image from selection
    const removeImage = (index) => {
        if (index < existingMedia.value.length) {
            // Existing media - mark for deletion
            const removedPath = existingMedia.value.splice(index, 1)[0];
            form.deleted_photos.push(removedPath);
        } else {
            // New file - remove from new files
            const newIndex = index - existingMedia.value.length;
            newFiles.value.splice(newIndex, 1);
            newPreviews.value.splice(newIndex, 1);
        }
        updateFormData();
    };

    // Update form data with current state
    const updateFormData = () => {
        form.existing_photos = [...existingMedia.value];
        form.photos = [...newFiles.value];
    };

    // Trigger file input
    const triggerFileInput = () => {
        fileInput.value?.click();
    };

    // Submit form
    const submit = () => {
        form.post(route('campaigns.add.photos.videos.store'), {
            preserveScroll: true,
            forceFormData: true,
            onError: (errors) => {
                fileError.value = Object.values(errors).join('\n');
            }
        });
    };

    // Initialize form with existing media
    onMounted(() => {
        updateFormData();
    });
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Campaigns: Add Media`" />

    <!-- Page content -->
    <main class="content-wrapper">
        <div class="container pt-3 pt-sm-4 pt-md-5 pb-5" style="padding-bottom: 18.2rem !important;">
            <div class="row pt-lg-2 pt-xl-3 pb-1 pb-sm-2 pb-md-3 pb-lg-4 pb-xl-5">
                <!-- Sidebar navigation -->
                <aside class="col-lg-3 col-xl-4 mb-3" style="margin-top: -100px">
                    <div class="sticky-top overflow-y-auto" style="padding-top: 100px">
                        <ul class="nav flex-lg-column flex-nowrap gap-4 gap-lg-0 text-nowrap pb-2 pb-lg-0">
                            <li class="nat-item">
                                <Link class="nav-link d-inline-flex position-relative px-0 px-lg-3" :href="route('campaigns.add.details')">
                                    <i class="ci-check-circle fs-lg me-2"></i>
                                    <span class="hover-effect-underline stretched-link">Campaign Details</span>
                                </Link>
                            </li>

                            <li class="nat-item">
                                <Link class="nav-link d-inline-flex position-relative px-0 px-lg-3" :href="route('campaigns.add.contact.info')">
                                    <i class="ci-check-circle fs-lg me-2"></i>
                                    <span class="hover-effect-underline stretched-link">Contact Info</span>
                                </Link>
                            </li>

                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 pe-none" aria-current="page">
                                    <i class="ci-arrow-right-circle d-none d-lg-inline-flex fs-lg me-2"></i>
                                    <i class="ci-arrow-right-circle d-lg-none fs-lg me-2"></i>
                                    Photos and videos
                                </a>
                            </li>

                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 disabled">
                                    <i class="ci-arrow-down-circle fs-lg me-2"></i>
                                    Supporting Documents
                                </a>
                            </li>

                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 disabled">
                                    <i class="ci-arrow-down-circle fs-lg me-2"></i>
                                    Ad promotion
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>

                <!-- Campaign photos and videos inputs -->
                <form class="col-lg-9 col-xl-8" @submit.prevent="submit">
                    <h1 class="h2 pb-1 pb-lg-2">Photos and videos</h1>
                    <p class="fs-sm mb-3">
                        Only JPG, JPEG, and PNG are allowed. Our suggested dimensions are 1280 px * 720 px. Larger images will be cropped to 16:9 to fit our thumbnails/previews.
                    </p>

                    <div v-if="fileError" class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div v-for="(error, index) in fileError.split('\n')" :key="index">{{ error }}</div>
                        <button type="button" class="btn-close" @click="fileError = ''" aria-label="Close"></button>
                    </div>

                    <!-- Hidden file input -->
                    <input type="file" ref="fileInput" class="d-none" accept="image/jpeg,image/png,image/jpg" multiple @change="handleFileSelect">

                    <!-- Image preview container -->
                    <div class="border rounded p-3">
                        <div class="row row-cols-2 row-cols-sm-3 g-2">
                            <!-- Preview items - both existing and new -->
                            <div class="col image-preview-item" v-for="(preview, index) in allPreviews" :key="index">
                                <div class="hover-effect-opacity position-relative overflow-hidden rounded">
                                    <div style="--fn-aspect-ratio: calc(180 / 268 * 100%)">
                                        <img :src="preview" alt="Preview" class="img-fluid">
                                    </div>

                                    <div class="hover-effect-target position-absolute top-0 start-0 d-flex align-items-center justify-content-center w-100 h-100 opacity-0">
                                        <button type="button" class="btn btn-icon btn-sm btn-light position-relative z-2" aria-label="Remove" @click="removeImage(index)">
                                            <i class="ci-trash fs-base"></i>
                                        </button>
                                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 z-1"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload button -->
                            <div class="col">
                                <div class="d-flex align-items-center justify-content-center position-relative h-100 cursor-pointer bg-body-tertiary border border-2 border-dotted rounded p-3" @click="triggerFileInput">
                                    <div class="text-center">
                                        <i class="ci-plus-circle fs-4 text-secondary-emphasis mb-2"></i>
                                        <div class="hover-effect-underline stretched-link fs-sm fw-medium">Upload photos / videos</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video URL field -->
                    <div class="pt-3 mt-3">
                        <label for="campaign_video" class="form-label">Link to the video</label>
                        <div class="position-relative">
                            <i class="ci-link position-absolute top-50 start-0 translate-middle-y fs-lg ms-3"></i>
                            <input id="campaign_video" type="url" class="form-control form-control-lg form-icon-start" :class="{ 'is-invalid': form.errors.campaign_video }" v-model="form.campaign_video" autocomplete="off" autofocus @focus="form.clearErrors('campaign_video')" placeholder="www.youtube.com/...">
                            <div v-if="form.errors.campaign_video" class="invalid-tooltip bg-transparent py-0">{{ form.errors.campaign_video }}</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Prev / Next navigation (Footer) -->
    <footer class="sticky-bottom bg-body pb-3">
        <div class="progress rounded-0" role="progressbar" aria-label="Dark example" aria-valuenow="57.14" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
            <div class="progress-bar bg-dark d-none-dark" style="width: 57.14%"></div>
            <div class="progress-bar bg-light d-none d-block-dark" style="width: 57.14%"></div>
        </div>
        <div class="container d-flex gap-3 pt-3">
            <Link class="btn btn-outline-dark animate-slide-start" :href="route('campaigns.add.contact.info')">
                <i class="ci-arrow-left animate-target fs-base ms-n1 me-2"></i> Back
            </Link>

            <LoadingButton type="button" :custom-classes="'btn btn-dark animate-slide-end ms-auto'" :processing="form.processing" @click="submit">
                Next <i class="ci-arrow-right animate-target fs-base ms-2 me-n1 align-middle"></i>
            </LoadingButton>
        </div>
    </footer>
</template>

<style scoped>
    .image-preview-item {
        position: relative;
        transition: all 0.3s ease;
    }

    .image-preview-item:hover .hover-effect-target {
        opacity: 1 !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .hover-effect-opacity {
        transition: opacity 0.3s ease;
    }

    .hover-effect-opacity:hover {
        opacity: 0.9;
    }
</style>
