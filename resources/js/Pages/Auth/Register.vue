<script setup lang="ts">
    import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
    import { watch, ref } from "vue";
    import SocialLogin from '@/Components/Auth/SocialLogin.vue'
    import AuthLogo from '@/Components/Auth/AuthLogo.vue'
    import AuthCoverImage from '@/Components/Auth/AuthCoverImage.vue'
    import LoadingButton from "@/Components/Button/LoadingButton.vue";
    import { route } from "ziggy-js";

    const page = usePage()

    const props = defineProps<{
        social_login: boolean;
        errors: Record<string, string>;
        app: { name: string };
        flash: any;
        auth: any;
    }>()

    const form = useForm({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        policy: false,
    })

    const submit = () => {
        form.post(route('register'), {
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

    // Clear errors when input changes
    watch(() => form.first_name, () => {
        if (form.errors.first_name) form.clearErrors('first_name')
    })

    watch(() => form.last_name, () => {
        if (form.errors.last_name) form.clearErrors('last_name')
    })

    watch(() => form.email, () => {
        if (form.errors.email) form.clearErrors('email')
    })

    watch(() => form.password, () => {
        if (form.errors.password) form.clearErrors('password')
    })
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Sign Up`" />

    <div class="row">
        <div class="col-12 col-md-6 col-xl-4 minvheight-100 d-flex flex-column px-0">
            <!-- Auth-Logo -->
            <AuthLogo />

            <div class="h-100 py-3 px-3">
                <div class="row h-100 align-items-center justify-content-center">
                    <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                        <h1 class="h2 mt-auto">Hi there,</h1>
                        <div class="nav fs-sm mb-4">
                            Already have an account?
                            <Link class="text-decoration-underline p-0 ms-2" :href="route('login')">Sign In</Link>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit">

                            <div v-if="errors.provider" class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ props.errors.provider }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-4">
                                        <input id="first_name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.first_name }" v-model="form.first_name" autocomplete="first_name" autofocus @focus="form.clearErrors('first_name')" placeholder="First Name">
                                        <div v-if="form.errors.first_name" class="invalid-feedback">{{ form.errors.first_name }}</div>
                                        <label for="first_name">First Name</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-4">
                                        <input id="last_name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.last_name }" v-model="form.last_name" autocomplete="last_name" autofocus @focus="form.clearErrors('last_name')" placeholder="Last Name">
                                        <div v-if="form.errors.last_name" class="invalid-feedback">{{ form.errors.last_name }}</div>
                                        <label for="last_name">Last Name</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating mb-4">
                                <input id="email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.email }" v-model="form.email" autocomplete="email" autofocus @focus="form.clearErrors('email')" placeholder="Email Address">
                                <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                                <label for="email">Email Address</label>
                            </div>

                            <div class="position-relative">
                                <div class="form-floating mb-4">
                                    <input id="password" type="password" class="form-control" :class="{ 'is-invalid': form.errors.password }" v-model="form.password" autocomplete="current-password" @focus="form.clearErrors('password')" placeholder="Minimum 8 characters">
                                    <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
                                    <label for="password">Password</label>
                                </div>

                                <button type="button" class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2" @click="togglePassword">
                                    <i :class="isPasswordVisible ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                </button>
                            </div>

                            <div class="d-flex flex-column gap-2 mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="policy" v-model="form.policy">
                                    <label for="policy" class="form-check-label">I have read and accept the <a href="/">Privacy Policy</a></label>
                                </div>
                            </div>

                            <!-- Sign Up Button -->
                            <LoadingButton :custom-classes="'btn btn-lg btn-theme w-100 mb-4'" :processing="form.processing" type="submit" @click="submit">
                                Create an account
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
