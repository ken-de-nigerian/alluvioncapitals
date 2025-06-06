<script setup lang="ts">
    import { Head, usePage, useForm } from "@inertiajs/vue3";
    import { ref } from "vue";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import { route } from "ziggy-js";

    const page = usePage();

    const props = defineProps({
        auth: Object,
        category: Object
    });

    const fileInput = ref(null);
    const fileError = ref('');
    const selectedImage = ref<File | null>(null);

    const form = useForm({
        category_name: props.category.name,
        category_status: props.category.status,
        category_image: null,
    });

    // Handle image selection
    const handleFileSelect = (event) => {
        const file = event.target.files[0];
        if (!file) return;

        const validTypes = ['image/jpeg', 'image/png'];
        const maxSize = 2 * 1024 * 1024;

        if (!validTypes.includes(file.type)) {
            fileError.value = 'Only JPEG and PNG images are allowed.';
            return;
        }

        if (file.size > maxSize) {
            fileError.value = 'Image size should not exceed 2MB.';
            return;
        }

        // Assign image to form data
        selectedImage.value = file;
        form.category_image = file;
        fileError.value = '';

        // Preview
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('preview-category') as HTMLImageElement;
            if (preview) preview.src = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    };

    // Trigger file input
    const triggerFileInput = () => {
        fileInput.value?.click();
    };

    // Submit everything in one form
    const submit = () => {
        const data = {
            category_name: form.category_name,
            category_status: form.category_status,
        };

        // Only add file if a new one is selected
        if (selectedImage.value) {
            data.category_image = selectedImage.value;
        }

        form.transform(() => data).post(route('admin.categories.update', props.category.id), {
            preserveScroll: true,
            forceFormData: true,
            onError: (errors) => {
                fileError.value = errors.category_image || '';
            }
        });
    };
</script>

<template>
    <!-- Account settings content -->
    <div class="col-lg-9 pt-2 pt-xl-3">
        <Head :title="`${page.props.app.name} | Account - Add Category`"/>

        <!-- Page title -->
        <h1 class="h2 pb-1 pb-sm-2 pb-md-3">Edit Category: {{ category.name }}</h1>

        <div v-if="fileError" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ fileError }}
            <button type="button" class="btn-close" @click="fileError = ''" aria-label="Close"></button>
        </div>

        <!-- Category form -->
        <form @submit.prevent="submit">
            <div class="d-flex align-items-start align-items-sm-center pb-3 mb-3">
                <div class="ratio ratio-1x1 hover-effect-opacity overflow-hidden" style="width: 112px">
                    <img id="preview-category" :src="category.image" class="img-fluid mb-3 rounded" style="max-height: 120px; width: auto;" :alt="category.name" loading="lazy">
                </div>

                <div class="ps-3 ps-sm-4">
                    <p class="fs-sm" style="max-width: 400px">Your category image will appear on your category data and campaigns. PNG or JPG no bigger than 2MB.</p>
                    <!-- Update Category Image Button -->
                    <div class="d-flex flex-column">
                        <div style="display: inline;">
                            <input type="file" ref="fileInput" accept="image/png, image/jpeg" style="display: none;" @change="handleFileSelect">
                            <button type="button" class="btn btn-sm btn-outline-secondary animate-rotate rounded-pill" @click="triggerFileInput">
                                <i class="ci-refresh-ccw animate-target fs-sm ms-n1 me-2"></i>
                                Select Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 g-4 mb-4">
                <div class="col-md-6 position-relative">
                    <label for="category_name" class="form-label fs-base">Category Name *</label>
                    <input id="category_name" type="text" class="form-control form-control-lg rounded-pill" :class="{ 'is-invalid': form.errors.category_name }" v-model="form.category_name" autocomplete="off" autofocus @focus="form.clearErrors('category_name')" placeholder="Category Name">
                    <div v-if="form.errors.category_name" class="invalid-tooltip bg-transparent py-0">{{ form.errors.category_name }}</div>
                </div>

                <div class="col-md-6 position-relative">
                    <label for="status" class="form-label fs-base">Status *</label>
                    <select class="form-select form-select-lg rounded-pill" :class="{ 'is-invalid': form.errors.category_status }" v-model="form.category_status" id="status" @focus="form.clearErrors('category_status')">
                        <option value="">Select option...</option>
                        <option value="active">Enabled</option>
                        <option value="inactive">Disabled</option>
                    </select>
                    <div v-if="form.errors.category_status" class="invalid-tooltip bg-transparent py-0">{{ form.errors.category_status}}</div>
                </div>
            </div>

            <div class="d-flex gap-3 mt-2">
                <LoadingButton type="submit" :custom-classes="'btn btn-lg btn-primary rounded-pill'" :processing="form.processing">
                    Save Changes
                </LoadingButton>
            </div>
        </form>
    </div>
</template>
