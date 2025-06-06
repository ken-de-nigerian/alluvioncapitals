<script setup lang="ts">
    import {usePage, useForm, Head} from "@inertiajs/vue3";
    import {ref, computed, onMounted} from "vue";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import { route } from "ziggy-js";

    const page = usePage();

    const props = defineProps({
        update: {
            type: Object,
        },
    });

    const fileInput = ref(null);
    const fileError = ref('');

    // Track media URLs and new files separately
    const existingMedia = ref<string[]>([]);
    const newFiles = ref<File[]>([]);
    const newPreviews = ref<string[]>([]);

    const form = useForm({
        photos: [] as File[],
        existing_photos: [] as string[],
        deleted_photos: [] as string[],
        title: props.update?.title || '',
        message: props.update?.message || '',
        status: props.update?.status || '',
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
        form.post(route('user.campaigns.updates.modify', [props.update.id]), {
            preserveScroll: true,
            forceFormData: true,
            onError: (errors) => {
                fileError.value = Object.values(errors).join('\n');
            }
        });
    };

    // Initialize form with existing media
    onMounted(() => {
        let images = props.update?.images;

        // Ensure we have an array
        if (Array.isArray(images)) {
            existingMedia.value = images;
        } else if (typeof images === 'string') {
            // Handle a case where it might be a JSON string
            try {
                existingMedia.value = JSON.parse(images);
            } catch (e) {
                existingMedia.value = [];
            }
        } else {
            existingMedia.value = [];
        }

        updateFormData();
    });
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Edit Campaign Update: ${update.title}`"/>

    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Campaign Update Form -->
        <form @submit.prevent="submit">

            <h1 class="h2 pb-1 pb-lg-2">Edit Campaign Update</h1>
            <p class="fs-sm mb-3">
                Keep your donors informed with updates. You can attach images to show progress or share inspiring moments.
                Accepted formats: JPG, JPEG, PNG. Suggested dimensions: 1280 × 720 px (16:9 ratio).
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
                    <!-- Preview items -->
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
                                <div class="hover-effect-underline stretched-link fs-sm fw-medium">Upload photos</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Title -->
            <div class="pt-3 mt-3">
                <label for="title" class="form-label">Update Title</label>
                <div class="position-relative">
                    <input id="title" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.title }" v-model="form.title" autocomplete="off" autofocus @focus="form.clearErrors('title')" placeholder="e.g. Reached halfway goal, thanks to you!">
                    <div v-if="form.errors.title" class="invalid-feedback">{{ form.errors.title }}</div>
                </div>
            </div>

            <!-- Update status -->
            <div class="pt-3 mt-3">
                <label for="status" class="form-label">Status *</label>
                <select class="form-select form-control-lg" :class="{ 'is-invalid': form.errors.status }" v-model="form.status" id="status" @focus="form.clearErrors('status')">
                    <option value="">Select option...</option>
                    <option value="active">Enabled</option>
                    <option value="inactive">Disabled</option>
                </select>
                <div v-if="form.errors.status" class="invalid-tooltip bg-transparent py-0">{{ form.errors.status}}</div>
            </div>

            <!-- Update Message -->
            <div class="pt-3 mt-3">
                <label for="message" class="form-label">Update Message *</label>
                <textarea id="message" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.message }" v-model="form.message" autocomplete="off" autofocus @focus="form.clearErrors('message')" rows="6" placeholder="Write a heartfelt update to let your donors know how things are going..."></textarea>
                <div v-if="form.errors.message" class="invalid-feedback">{{ form.errors.message }}</div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <LoadingButton type="submit" :custom-classes="'btn btn-primary btn-lg'" :processing="form.processing" @click="submit">
                    Post Update
                </LoadingButton>
            </div>
        </form>
    </div>
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
