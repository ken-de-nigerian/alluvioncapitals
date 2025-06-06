<script setup lang="ts">
    import { Head, usePage, useForm, Link } from "@inertiajs/vue3";
    import { ref, computed, onMounted } from "vue";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import { route } from "ziggy-js";

    const page = usePage();

    const props = defineProps({
        documents: Object,
        campaign: Object
    });

    const fileInput = ref(null);
    const fileError = ref('');

    // Helper to check if URL is an image
    const isImageUrl = (url: string) => {
        const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        return imageExtensions.some(ext => url.toLowerCase().endsWith(ext));
    };

    // Helper to guess MIME type from URL
    const getMimeTypeFromUrl = (url: string) => {
        const extension = url.split('.').pop()?.toLowerCase();
        switch(extension) {
            case 'pdf': return 'application/pdf';
            case 'doc': return 'application/msword';
            case 'docx': return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            case 'xls': return 'application/vnd.ms-excel';
            case 'xlsx': return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'ppt': return 'application/vnd.ms-powerpoint';
            case 'pptx': return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            case 'txt': return 'text/plain';
            case 'rtf': return 'application/rtf';
            case 'jpg':
            case 'jpeg': return 'image/jpeg';
            case 'png': return 'image/png';
            default: return 'application/octet-stream';
        }
    };

    // Track existing documents
    const existingDocuments = ref<string[]>([]);
    const newFiles = ref<File[]>([]);
    const newPreviews = ref<Array<{type: string, content: string, file: File}>>([]);

    const form = useForm({
        documents: [] as File[],
        existing_documents: [] as string[],
        deleted_documents: [] as string[],
    });

    // Helper function to get document icon
    const getDocumentIcon = (fileType: string) => {
        if (!fileType) return 'https://cdn-icons-png.flaticon.com/512/136/136528.png';
        if (fileType.includes('pdf')) return 'https://cdn-icons-png.flaticon.com/512/337/337946.png';
        if (fileType.includes('word')) return 'https://cdn-icons-png.flaticon.com/512/281/281760.png';
        if (fileType.includes('excel')) return 'https://cdn-icons-png.flaticon.com/512/732/732220.png';
        if (fileType.includes('powerpoint')) return 'https://cdn-icons-png.flaticon.com/512/281/281769.png';
        return 'https://cdn-icons-png.flaticon.com/512/136/136528.png';
    };

    // Combined previews for display
    const allPreviews = computed(() => {
        return [
            ...existingDocuments.value,
            ...newPreviews.value
        ];
    });

    // Handle file selection
    const handleFileSelect = (event) => {
        const files = Array.from(event.target.files);
        if (!files.length) return;

        const validTypes = [
            'image/jpeg', 'image/png', 'image/jpg',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'application/rtf'
        ];

        const maxSize = 2 * 1024 * 1024; // 2MB
        const errors = [];

        files.forEach(file => {
            if (!validTypes.includes(file.type)) {
                errors.push(`File ${file.name} is not a supported file type.`);
                return;
            }

            if (file.size > maxSize) {
                errors.push(`File ${file.name} exceeds 2MB size limit.`);
                return;
            }

            // Add to new files
            newFiles.value.push(file);

            if (file.type.startsWith('image/')) {
                // Create image preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    newPreviews.value.push({
                        type: 'image',
                        content: e.target.result as string,
                        file
                    });
                };
                reader.readAsDataURL(file);
            } else {
                // For documents, show the icon preview
                newPreviews.value.push({
                    type: 'document',
                    content: getDocumentIcon(file.type),
                    file
                });
            }
        });

        if (errors.length) {
            fileError.value = errors.join('\n');
        } else {
            fileError.value = '';
            updateFormData();
        }
    };

    // Remove file from selection
    const removeFile = (index) => {
        if (index < existingDocuments.value.length) {
            // Existing documents - mark for deletion
            const removedPath = existingDocuments.value.splice(index, 1)[0];
            form.deleted_documents.push(removedPath.content);
        } else {
            // New file - remove from new files
            const newIndex = index - existingDocuments.value.length;
            newFiles.value.splice(newIndex, 1);
            newPreviews.value.splice(newIndex, 1);
        }
        updateFormData();
    };

    // Update form data with current state
    const updateFormData = () => {
        form.existing_documents = existingDocuments.value.map(doc => doc.content);
        form.documents = [...newFiles.value];
    };

    // Trigger file input
    const triggerFileInput = () => {
        fileInput.value?.click();
    };

    // Submit form
    const submit = () => {
        form.post(route('campaigns.edit.supporting.documents.store', props.campaign?.id), {
            preserveScroll: true,
            forceFormData: true,
            onError: (errors) => {
                fileError.value = Object.values(errors).join('\n');
            }
        });
    };

    // Initialize form with existing documents
    onMounted(() => {
        let documents = props.documents?.supporting_documents;

        if (!documents) {
            documents = props.campaign?.supporting_documents;
        }

        if (typeof documents === 'string') {
            try {
                documents = JSON.parse(documents);
            } catch (e) {
                documents = [];
            }
        }

        if (Array.isArray(documents)) {
            existingDocuments.value = documents.map(item => ({
                type: isImageUrl(item) ? 'image' : 'document',
                content: item,
                file: {
                    name: item.split('/').pop() || 'Existing file',
                    type: getMimeTypeFromUrl(item)
                }
            }));
        } else {
            existingDocuments.value = [];
        }

        updateFormData();
    });
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Campaigns: Add Supporting Documents`" />

    <!-- Page content -->
    <main class="content-wrapper">
        <div class="container pt-3 pt-sm-4 pt-md-5 pb-5" style="padding-bottom: 25rem !important;">
            <div class="row pt-lg-2 pt-xl-3 pb-1 pb-sm-2 pb-md-3 pb-lg-4 pb-xl-5">
                <!-- Sidebar navigation -->
                <aside class="col-lg-3 col-xl-4 mb-3" style="margin-top: -100px">
                    <div class="sticky-top overflow-y-auto" style="padding-top: 100px">
                        <ul class="nav flex-lg-column flex-nowrap gap-4 gap-lg-0 text-nowrap pb-2 pb-lg-0">
                            <li class="nat-item">
                                <Link class="nav-link d-inline-flex position-relative px-0 px-lg-3" :href="route('campaigns.edit.details', props.campaign?.id)">
                                    <i class="ci-check-circle fs-lg me-2"></i>
                                    <span class="hover-effect-underline stretched-link">Campaign Details</span>
                                </Link>
                            </li>

                            <li class="nat-item">
                                <Link class="nav-link d-inline-flex position-relative px-0 px-lg-3" :href="route('campaigns.edit.contact.info', props.campaign?.id)">
                                    <i class="ci-check-circle fs-lg me-2"></i>
                                    <span class="hover-effect-underline stretched-link">Contact Info</span>
                                </Link>
                            </li>

                            <li class="nat-item">
                                <Link class="nav-link d-inline-flex position-relative px-0 px-lg-3" :href="route('campaigns.edit.photos.videos', props.campaign?.id)">
                                    <i class="ci-check-circle fs-lg me-2"></i>
                                    <span class="hover-effect-underline stretched-link">Photos and videos</span>
                                </Link>
                            </li>

                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 pe-none" aria-current="page">
                                    <i class="ci-arrow-right-circle d-none d-lg-inline-flex fs-lg me-2"></i>
                                    <i class="ci-arrow-right-circle d-lg-none fs-lg me-2"></i>
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

                <!-- Campaign documents inputs -->
                <form class="col-lg-9 col-xl-8" @submit.prevent="submit">
                    <h1 class="h2 pb-1 pb-lg-2">Supporting Documents</h1>
                    <p class="fs-sm mb-3">
                        We require campaigners to upload documents to verify the authenticity of their campaign. These may include ID cards, hospital reports, admission letters, proof of enrollment, or project budgets.
                    </p>

                    <div v-if="fileError" class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div v-for="(error, index) in fileError.split('\n')" :key="index">{{ error }}</div>
                        <button type="button" class="btn-close" @click="fileError = ''" aria-label="Close"></button>
                    </div>

                    <!-- Hidden file input -->
                    <input type="file" ref="fileInput" class="d-none" accept="image/jpeg,image/png,image/jpg,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,application/rtf" multiple @change="handleFileSelect">

                    <!-- File preview container -->
                    <div class="border rounded p-3">
                        <div class="row row-cols-2 row-cols-sm-3 g-2">
                            <!-- Preview items - both existing and new -->
                            <div class="col file-preview-item" v-for="(preview, index) in allPreviews" :key="index">
                                <div class="hover-effect-opacity position-relative overflow-hidden rounded h-100">
                                    <div class="h-100 d-flex flex-column bg-light">
                                        <!-- Image Preview -->
                                        <div v-if="preview.type === 'image'" class="flex-grow-1" style="height: 150px">
                                            <img :src="preview.content" alt="Preview" class="img-fluid w-100 h-100 object-fit-cover">
                                        </div>

                                        <!-- Document Preview -->
                                        <div v-else class="flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="height: 150px">
                                            <img :src="getDocumentIcon(preview.file?.type)" class="mb-2" style="max-height: 60px" alt="Document icon">
                                            <div class="small text-center text-truncate w-100 px-1">
                                                {{ preview.file?.name || 'Document' }}
                                            </div>
                                            <div class="small text-muted text-center">
                                                {{ preview.file?.type?.split('/').pop()?.toUpperCase() || 'FILE' }}
                                            </div>
                                        </div>

                                        <!-- Remove button -->
                                        <div class="hover-effect-target position-absolute top-0 start-0 d-flex align-items-center justify-content-center w-100 h-100 opacity-0">
                                            <button type="button" class="btn btn-icon btn-sm btn-light position-relative z-2" aria-label="Remove" @click="removeFile(index)">
                                                <i class="ci-trash fs-base"></i>
                                            </button>
                                            <span class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 z-1"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload button -->
                            <div class="col">
                                <div class="d-flex align-items-center justify-content-center position-relative h-100 cursor-pointer bg-body-tertiary border border-2 border-dotted rounded p-3" @click="triggerFileInput">
                                    <div class="text-center">
                                        <i class="ci-plus-circle fs-4 text-secondary-emphasis mb-2"></i>
                                        <div class="hover-effect-underline stretched-link fs-sm fw-medium">Upload files</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Prev / Next navigation (Footer) -->
    <footer class="sticky-bottom bg-body pb-3">
        <div class="progress rounded-0" role="progressbar" aria-label="Dark example" aria-valuenow="85.71" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
            <div class="progress-bar bg-dark d-none-dark" style="width: 85.71%"></div>
            <div class="progress-bar bg-light d-none d-block-dark" style="width: 85.71%"></div>
        </div>
        <div class="container d-flex gap-3 pt-3">
            <Link class="btn btn-outline-dark animate-slide-start" :href="route('campaigns.edit.photos.videos', props.campaign?.id)">
                <i class="ci-arrow-left animate-target fs-base ms-n1 me-2"></i> Back
            </Link>

            <LoadingButton type="button" :custom-classes="'btn btn-dark animate-slide-end ms-auto'" :processing="form.processing" @click="submit">
                Next <i class="ci-arrow-right animate-target fs-base ms-2 me-n1 align-middle"></i>
            </LoadingButton>
        </div>
    </footer>
</template>

<style scoped>
    .file-preview-item {
        position: relative;
        transition: all 0.3s ease;
    }

    .file-preview-item:hover .hover-effect-target {
        opacity: 1 !important;
    }

    .object-fit-cover {
        object-fit: cover;
    }

    .hover-effect-opacity {
        transition: opacity 0.3s ease;
    }

    .hover-effect-opacity:hover {
        opacity: 0.9;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>
