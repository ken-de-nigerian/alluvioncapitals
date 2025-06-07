<script setup lang="ts">
    import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
    import {ref, watch} from "vue";
    import {route} from "ziggy-js";

    import AuthLogo from "@/Components/Auth/AuthLogo.vue";
    import AuthCoverImage from "@/Components/Auth/AuthCoverImage.vue";
    import SocialLogin from "@/Components/Auth/SocialLogin.vue";
    import LoadingButton from "@/Components/Button/LoadingButton.vue";

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
        form.post(route('login.store'), {
            onFinish: () => {
                form.reset('password')
            },
        })
    }

    // Password toggle state
    const isPasswordVisible = ref(false)

    const togglePassword = () => {
        isPasswordVisible.value = !isPasswordVisible.value
        const passwordInput = document.getElementById('password') as HTMLInputElement
        if (passwordInput) {
            passwordInput.type = isPasswordVisible.value ? 'text' : 'password'
        }
    }

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

    <div class="row">
        <div class="col-12 col-md-6 col-xl-4 minvheight-100 d-flex flex-column px-0">

            <!-- Auth-Logo -->
            <AuthLogo />

            <div class="h-100 py-4 px-3">
                <div class="row h-100 align-items-center justify-content-center mt-md-4">
                    <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                        <h1 class="h2 mt-auto">Hi there,</h1>
                        <div class="nav fs-sm mb-4">
                            Don't have an account?
                            <Link class="text-decoration-underline p-0 ms-2" :href="route('register')">Create an account</Link>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit">

                            <div v-if="errors.provider" class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ props.errors.provider }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <div class="form-floating mb-4">
                                <input id="email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.email }" v-model="form.email" autocomplete="email" autofocus @focus="form.clearErrors('email')" placeholder="Email">
                                <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                                <label for="email">Email</label>
                            </div>

                            <div class="position-relative mb-4">
                                <div class="form-floating mb-3">
                                    <input id="password" type="password" class="form-control" :class="{ 'is-invalid': form.errors.password }" v-model="form.password" autocomplete="current-password" @focus="form.clearErrors('password')" placeholder="Password">
                                    <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
                                    <label for="password">Password</label>
                                </div>

                                <button type="button" class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2" @click="togglePassword">
                                    <i :class="isPasswordVisible ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                </button>
                            </div>

                            <div class="row align-items-center my-4">
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" v-model="form.remember" />
                                        <label class="form-check-label" for="remember">Remember for 30 days</label>
                                    </div>
                                </div>

                                <div class="col-auto">
                                    <Link :href="route('password.request')">Forget Password?</Link>
                                </div>
                            </div>

                            <!-- Sign In Button -->
                            <LoadingButton :custom-classes="'btn btn-lg btn-theme w-100 mb-4'" :processing="form.processing" type="submit">
                                Sign In
                            </LoadingButton>
                        </form>

                        <!-- Social Login Links -->
                        <SocialLogin :social_login="social_login" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Auth Cover image -->
        <AuthCoverImage />
    </div>
</template>
