<script setup lang="ts">
    import { Head, usePage, Link, useForm } from "@inertiajs/vue3";
    import { onMounted, watch, computed } from "vue";
    import initPasswordToggle from "../../../Components/Js/PasswordToggle.js";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import { route } from "ziggy-js";
    import UserProfileNavTabs from "../../../Components/Navigation/UserProfileNavTabs.vue";

    const page = usePage();
    const user = page.props.auth.user;

    const props = defineProps({
        auth: Object,
        activeSessions: Array,
        connectedAccounts: String,
    });

    // Compute connection status based on connectedAccounts string
    const isGoogleConnected = computed(() => props.connectedAccounts === 'google');
    const isFacebookConnected = computed(() => props.connectedAccounts === 'facebook');

    // Form for password update
    const form = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    // Form for account disconnection
    const connectForm = useForm({});

    const submit = () => {
        form.patch(route('user.password.update'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('password', 'password_confirmation', 'current_password');
            },
            onError: () => {
                form.reset('password', 'password_confirmation');
            },
        });
    };

    // Invoke (disconnect) an account
    const invokeAccount = (provider: string) => {
        connectForm.post(route('social.disconnect', { provider }), {
            preserveScroll: true,
            onError: (errors) => {
                console.error(`Failed to invoke ${provider} account:`, errors);
            },
        });
    };

    onMounted(() => {
        initPasswordToggle();
    });

    // Clear password errors when input changes
    watch(() => form.password, () => {
        if (form.errors.password) form.clearErrors('password');
    });

    watch(() => form.current_password, () => {
        if (form.errors.current_password) form.clearErrors('current_password');
    });

    watch(() => form.password_confirmation, () => {
        if (form.errors.password_confirmation) form.clearErrors('password_confirmation');
    });
</script>

<template>
    <div class="col-lg-9 pt-2 pt-xl-3">
        <Head :title="`${page.props.app.name} | Account - Security`" />

        <h1 class="h2 pb-1 pb-sm-2 pb-md-3">Security</h1>

        <UserProfileNavTabs />

        <div class="tab-content">
            <div class="tab-pane fade" :class="{ 'show active': page.component === 'User/Profile/Security' }">
                <p class="mb-sm-4">
                    Your current email address is <span class="fw-medium text-primary">{{ user.email }}</span>
                </p>

                <!-- Change password form -->
                <form @submit.prevent="submit">
                    <div class="row row-cols-1 row-cols-sm-2 g-4 mb-4">
                        <div class="col">
                            <label for="current-password" class="form-label fs-base">Current password</label>
                            <div class="password-toggle">
                                <input id="current-password" type="password" class="form-control form-control-lg rounded-pill" :class="{ 'is-invalid': form.errors.current_password }" v-model="form.current_password" autocomplete="current-password" @focus="form.clearErrors('current_password')" placeholder="Current Password"/>
                                <div v-if="form.errors.current_password" class="invalid-tooltip bg-transparent py-0">
                                    {{ form.errors.current_password }}
                                </div>
                                <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                                    <input type="checkbox" class="btn-check" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-sm-2 g-4 mb-4">
                        <div class="col">
                            <label for="new-password" class="form-label fs-base">
                                New password <span class="fs-sm fw-normal text-body-secondary">(Min 8 chars)</span>
                            </label>
                            <div class="password-toggle">
                                <input id="password" type="password" class="form-control form-control-lg rounded-pill" :class="{ 'is-invalid': form.errors.password }" v-model="form.password" autocomplete="new-password" @focus="form.clearErrors('password')" placeholder="New Password"/>
                                <div v-if="form.errors.password" class="invalid-tooltip bg-transparent py-0">
                                    {{ form.errors.password }}
                                </div>
                                <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                                    <input type="checkbox" class="btn-check" />
                                </label>
                            </div>
                        </div>

                        <div class="col">
                            <label for="confirm-new-password" class="form-label fs-base">Confirm new password</label>
                            <div class="password-toggle">
                                <input id="confirm-new-password" type="password" class="form-control form-control-lg rounded-pill" :class="{ 'is-invalid': form.errors.password_confirmation }" v-model="form.password_confirmation" autocomplete="confirm-password" @focus="form.clearErrors('password_confirmation')" placeholder="Confirm New Password"/>
                                <div v-if="form.errors.password_confirmation" class="invalid-tooltip bg-transparent py-0">
                                    {{ form.errors.password_confirmation }}
                                </div>
                                <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                                    <input type="checkbox" class="btn-check" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <LoadingButton type="submit" :custom-classes="'btn btn-lg btn-primary rounded-pill'" :processing="form.processing">
                            Update password
                        </LoadingButton>
                    </div>
                </form>

                <div v-if="isGoogleConnected || isFacebookConnected" class="row g-4 mt-4">
                    <div class="col-lg-12">
                        <!-- Show Google if connected, otherwise show Facebook -->
                        <div v-if="isGoogleConnected" class="position-relative mb-4 d-sm-flex border p-3 rounded bg-success bg-opacity-10 border-success">
                            <h2 class="fs-1 mb-0 me-3">
                                <i class="ci ci-google text-google-icon p-2"></i>
                            </h2>

                            <div>
                                <h6 class="mb-1">Google</h6>
                                <p class="mb-1 small">You are successfully connected to your Google account</p>
                                <button type="button" class="btn btn-sm btn-danger mb-0" @click="invokeAccount('google')" :disabled="connectForm.processing">
                                    Disconnect
                                </button>
                                <a href="#" class="btn btn-sm btn-link text-body mb-0">Learn more</a>
                            </div>
                        </div>
                        <div v-else class="mb-4 d-sm-flex border p-3 rounded" :class="{ 'bg-success bg-opacity-10 border-success': isFacebookConnected }">
                            <h2 class="fs-1 mb-0 me-3">
                                <i class="ci ci-facebook text-facebook p-2 bg-light-subtle rounded-circle"></i>
                            </h2>

                            <div>
                                <h6 class="mb-1">Facebook</h6>
                                <p class="mb-1 small">
                                    {{ isFacebookConnected ? 'You are successfully connected to your Facebook account' : 'No social account is currently connected' }}
                                </p>

                                <button v-if="isFacebookConnected" type="button" class="btn btn-sm btn-danger mb-0" @click="invokeAccount('facebook')" :disabled="connectForm.processing">
                                    Disconnect
                                </button>
                                <a href="#" class="btn btn-sm btn-link text-body mb-0">Learn more</a>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="h4 pt-2 mt-md-3 mb-sm-4">Device history</h3>
                <div class="row g-3 g-xl-4 mb-3 mb-md-4">
                    <div v-for="session in activeSessions" class="col-sm-6 col-md-4" :key="session.id">
                        <div class="card h-100">
                            <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
                                <button type="button" class="btn btn-icon btn-sm fs-xl text-dark-emphasis border-0" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                    <i class="ci-more-vertical"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end" style="--fn-dropdown-min-width: 8rem">
                                    <li>
                                        <Link class="dropdown-item" as="button" method="post" :href="route('logout.current', session.id)">
                                            <i class="ci-log-out opacity-75 me-2"></i>
                                            Sign out
                                        </Link>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-body pb-2">
                                <i class="ci-computer fs-3 text-body-tertiary pb-1 mb-2"></i>
                                <h6 class="mb-0">{{ session.device }} - {{ session.platform }}</h6>
                            </div>

                            <div class="card-footer d-flex align-items-center gap-2 bg-transparent border-0 pt-0 pb-4">
                                <span class="fs-sm text-body-secondary">{{ session.browser }}</span>
                                <span v-if="session.is_current" class="badge text-success bg-success-subtle">Active now</span>
                                <span v-else class="fs-sm text-body-secondary">{{ session.last_activity }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="nav">
                    <Link class="nav-link position-relative px-0" as="button" method="delete" :href="route('logout.all')">
                        <i class="ci-log-out fs-base me-2"></i>
                        <span class="hover-effect-underline stretched-link">Sign out of all sessions</span>
                    </Link>
                </div>

                <h2 class="h6 pt-5 mt-xl-2 pb-1 mb-2">Delete account</h2>
                <p class="fs-sm">
                    When you delete your account, your public profile will be deactivated immediately. If you change your mind before the 14 days are up, sign in with your email and password, and we'll send a link to reactivate account.
                </p>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="confirm-deletion" />
                    <label for="confirm-deletion" class="form-check-label">Yes, I want to delete my account</label>
                </div>
                <a class="fs-sm fw-medium text-danger" href="#">Delete account</a>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .text-google-icon {
        background: conic-gradient(from -45deg, #ea4335 110deg, #4285f4 90deg 180deg, #34a853 180deg 270deg, #fbbc05 270deg) 73% 55% / 150% 150% no-repeat;
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        -webkit-text-fill-color: transparent;
    }
    .text-facebook {
        color: #5d82d1;
    }
</style>
