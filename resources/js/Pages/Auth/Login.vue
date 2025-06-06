<script setup lang="ts">
    import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
    import { onMounted, watch } from "vue";
    import initPasswordToggle from '../../Components/Js/PasswordToggle.js'

    import SocialLogin from '../../Components/Auth/SocialLogin.vue'
    import AuthLogo from '../../Components/Auth/AuthLogo.vue'
    import AuthFooter from '../../Components/Auth/AuthFooter.vue'
    import AuthCoverImage from '../../Components/Auth/AuthCoverImage.vue'
    import LoadingButton from "../../Components/Button/LoadingButton.vue";
    import {route} from "ziggy-js";

    const page = usePage()

    const props = defineProps({
        social_login: Boolean,
        errors: Object,
    })

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    })

    const submit = () => {
        form.post(route('login'), {
            onFinish: () => {
                form.reset('password')
            },
        })
    }

    onMounted(() => {
        initPasswordToggle()
    })

    // Clear email errors when input changes
    watch(() => form.email, () => {
        if (form.errors.email) form.clearErrors('email')
    })

    // Clear password errors when input changes
    watch(() => form.password, () => {
        if (form.errors.password) form.clearErrors('password')
    })
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Sign In`" />

    <div class="d-lg-flex">
        <!-- Login form + Footer -->
        <div class="d-flex flex-column min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">
            <!-- Auth-Logo -->
            <AuthLogo />

            <h1 class="h2 mt-auto">Hi there,</h1>
            <div class="nav fs-sm mb-4">
                Don't have an account?
                <Link class="nav-link text-decoration-underline p-0 ms-2" :href="route('register')">Create an account</Link>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit">

                <div v-if="errors.provider" class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ props.errors.provider }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <div class="position-relative mb-4">
                    <input id="email" type="email" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.email }" v-model="form.email" autocomplete="email" autofocus @focus="form.clearErrors('email')" placeholder="Email Address">
                    <div v-if="form.errors.email" class="invalid-tooltip bg-transparent py-0">{{ form.errors.email }}</div>
                </div>

                <div class="mb-4">
                    <div class="password-toggle">
                        <input id="password" type="password" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.password }" v-model="form.password" autocomplete="current-password" @focus="form.clearErrors('password')" placeholder="Password">
                        <div v-if="form.errors.password" class="invalid-tooltip bg-transparent py-0">{{ form.errors.password }}</div>
                        <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                            <input type="checkbox" class="btn-check">
                        </label>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check me-2">
                        <input type="checkbox" id="remember" class="form-check-input" v-model="form.remember">
                        <label for="remember" class="form-check-label">Remember for 30 days</label>
                    </div>

                    <div class="nav">
                        <Link class="nav-link animate-underline p-0" :href="route('password.request')">
                            <span class="animate-target">Forgot password?</span>
                        </Link>
                    </div>
                </div>

                <!-- Sign In Button -->
                <LoadingButton :processing="form.processing" type="submit">
                    Sign In
                </LoadingButton>

                <!-- Social Login Links -->
                <SocialLogin :social_login="social_login" />
            </form>

            <!-- Auth-Footer -->
            <AuthFooter />
        </div>

        <!-- Auth Cover image visible on screens > 992px wide (lg breakpoint) -->
        <AuthCoverImage />
    </div>
</template>
