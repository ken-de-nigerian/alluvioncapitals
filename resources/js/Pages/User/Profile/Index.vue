<script setup lang="ts">
    import {Head, usePage, router, useForm} from "@inertiajs/vue3";
    import {computed, onMounted, ref} from "vue";
    import initPasswordToggle from "../../../Components/Js/PasswordToggle.js";
    import Cleave from 'cleave.js';
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import {route} from "ziggy-js";

    import iziToast from 'izitoast';
    import 'izitoast/dist/css/iziToast.min.css';
    import UserProfileNavTabs from "../../../Components/Navigation/UserProfileNavTabs.vue";

    const page = usePage();
    const phoneInput = ref(null);

    const props = defineProps({
        user: Object
    });

    const form = useForm({
        first_name: props.user.first_name,
        last_name: props.user.last_name,
        email: props.user.email,
        phone_number: props.user.phone_number,
    })

    const submit = () => {
        form.patch(route('user.profile.update'), {
            preserveScroll: true,
        });
    }

    const initials = computed(() => {
        const first = props.user?.first_name?.charAt(0) || '';
        const last = props.user?.last_name?.charAt(0) || '';
        return `${first}${last}`.toUpperCase();
    });

    // Refs for file upload
    const fileError = ref('');
    const fileSuccess = ref('');
    const fileInput = ref(null);
    const previewImage = ref(null);

    // Handle file selection
    const handleFileSelect = (event) => {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file type and size
        const validTypes = ['image/jpeg', 'image/png'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!validTypes.includes(file.type)) {
            fileError.value = 'Only JPEG and PNG images are allowed.';
            return;
        }

        if (file.size > maxSize) {
            fileError.value = 'Image size should not exceed 2MB.';
            return;
        }

        // Preview image
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Submit form using Inertia
        uploadFile(file);
    };

    const uploadFile = (file) => {
        fileError.value = '';
        fileSuccess.value = '';

        router.post(route('user.picture.update'),
            { profile_image: file },
            {
                preserveScroll: true,
                forceFormData: true,
                onSuccess: () => {},
                onError: (errors) => {
                    fileError.value = errors.profile_image?.[0] ||
                        errors.error?.[0] ||
                        'Failed to update profile picture.';
                }
            }
        );
    };

    const removeProfilePicture = () => {
        router.delete(route('user.picture.remove'), {
            preserveScroll: true,
            onSuccess: () => {
                // Reset the preview to initials
                previewImage.value.src = `https://placehold.co/124x124/222934/ffffff?text=${initials.value}`;
            },
            onError: (errors) => {
                fileError.value = errors.error?.[0] || 'Failed to remove profile picture.';
            }
        });
    };

    const triggerFileInput = () => {
        fileInput.value.click();
    };

    const triggerFileRemoval = () => {
        confirmAction('Are you sure you want to remove your profile picture?', removeProfilePicture);
    };

    onMounted(() => {
        initPasswordToggle();
        previewImage.value = document.getElementById('profile-image-preview');

        if (phoneInput.value) {
            new Cleave(phoneInput.value, {
                numericOnly: true,
                blocks: [0, 3, 0, 4, 4],
                delimiters: ['(', ')', ' ', '-', ' '],
                maxLength: 16
            });
        }
    });

    // Toast notification helper
    const confirmAction = (message, callback) => {
        iziToast.question({
            timeout: false,
            close: false,
            overlay: true,
            displayMode: 'once',
            title: 'Confirm',
            message,
            position: 'topRight',
            buttons: [
                ['<button><b>YES</b></button>', (instance, toast) => {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    callback();
                }, true],
                ['<button>Cancel</button>', (instance, toast) => {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                }]
            ]
        });
    };
</script>

<template>
    <!-- Account settings content -->
    <div class="col-lg-9 pt-2 pt-xl-3">
        <Head :title="`${page.props.app.name} | Account - Settings`"/>

        <!-- Page title -->
        <h1 class="h2 pb-1 pb-sm-2 pb-md-3">Settings</h1>

        <!-- Nav tabs -->
        <UserProfileNavTabs />

        <!-- Tabs content -->
        <div class="tab-content">
            <!-- Profile tab -->
            <div class="tab-pane fade" :class="{ 'show active': page.component === 'User/Profile/Index' }">
                <div v-if="fileError" class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ fileError }}
                    <button type="button" class="btn-close" @click="fileError = ''" aria-label="Close"></button>
                </div>

                <div v-if="fileSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ fileSuccess }}
                    <button type="button" class="btn-close" @click="fileSuccess = ''" aria-label="Close"></button>
                </div>

                <!-- Avatar -->
                <div class="d-flex align-items-start align-items-sm-center pb-3 mb-3">
                    <div class="ratio ratio-1x1 hover-effect-opacity border rounded-circle overflow-hidden" style="width: 112px">
                        <img :src="props.user.avatar ?? `https://placehold.co/124x124/222934/ffffff?text=${initials}`" alt="Avatar" id="profile-image-preview" class="object-fit-cover" loading="lazy">

                        <div v-if="props.user.avatar" class="hover-effect-target position-absolute top-0 start-0 d-flex align-items-center justify-content-center w-100 h-100 opacity-0" @click="triggerFileRemoval">
                            <button type="button" class="btn btn-icon btn-sm btn-light position-relative z-2" aria-label="Remove">
                                <i class="ci-trash fs-base"></i>
                            </button>
                            <span class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 z-1"></span>
                        </div>
                    </div>

                    <div class="ps-3 ps-sm-4">
                        <p class="fs-sm" style="max-width: 400px">Your profile picture will appear on your profile and campaigns. PNG or JPG no bigger than 2MB.</p>

                        <!-- Update Profile Picture Button -->
                        <input type="file" ref="fileInput" accept="image/png, image/jpeg" style="display: none;" @change="handleFileSelect">
                        <button type="button" class="btn btn-sm btn-secondary animate-rotate rounded-pill" @click="triggerFileInput">
                            <i class="ci-refresh-ccw animate-target fs-sm ms-n1 me-2"></i>
                            Update picture
                        </button>
                    </div>
                </div>

                <!-- Settings form -->
                <form @submit.prevent="submit">
                    <div class="row row-cols-1 row-cols-sm-2 g-4 mb-4">
                        <div class="col position-relative">
                            <label for="first_name" class="form-label fs-base">First name *</label>
                            <input id="first_name" type="text" class="form-control form-control-lg rounded-pill" :class="{ 'is-invalid': form.errors.first_name }" v-model="form.first_name" autocomplete="off" autofocus @focus="form.clearErrors('first_name')" placeholder="First Name">
                            <div v-if="form.errors.first_name" class="invalid-tooltip bg-transparent py-0">{{ form.errors.first_name }}</div>
                        </div>

                        <div class="col position-relative">
                            <label for="last_name" class="form-label fs-base">Last name *</label>
                            <input id="last_name" type="text" class="form-control form-control-lg rounded-pill" :class="{ 'is-invalid': form.errors.last_name }" v-model="form.last_name" autocomplete="off" autofocus @focus="form.clearErrors('last_name')" placeholder="Last Name">
                            <div v-if="form.errors.last_name" class="invalid-tooltip bg-transparent py-0">{{ form.errors.last_name }}</div>
                        </div>

                        <div class="col position-relative">
                            <label for="email" class="form-label d-flex align-items-center fs-base">Email address * <span :class="['badge', 'ms-2', user.email_verified_at ? 'bg-success' : 'bg-danger']" :aria-label="user.email_verified_at ? 'Email verified' : 'Email not verified'">{{ user.email_verified_at ? 'Verified' : 'Unverified' }}</span></label>
                            <input id="email" type="email" class="form-control form-control-lg rounded-pill" v-model="form.email" autocomplete="email" placeholder="Email Address" readonly>
                        </div>

                        <div class="col position-relative">
                            <label for="phone_number" class="form-label d-flex align-items-center fs-base">Phone number *</label>
                            <input ref="phoneInput" id="phone_number" type="tel" class="form-control form-control-lg rounded-pill" :class="{ 'is-invalid': form.errors.phone_number }" v-model="form.phone_number" autocomplete="off" autofocus @focus="form.clearErrors('phone_number')" placeholder="(___) ___-____">
                            <div v-if="form.errors.phone_number" class="invalid-tooltip bg-transparent py-0">{{ form.errors.phone_number }}</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <!-- Update Profile Button -->
                        <LoadingButton type="submit" :custom-classes="'btn btn-lg btn-primary rounded-pill'" :processing="form.processing">
                            Save Changes
                        </LoadingButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
