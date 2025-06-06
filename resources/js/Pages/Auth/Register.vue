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

    onMounted(() => {
        initPasswordToggle()
    })

    // Clear first_name errors when input changes
    watch(() => form.first_name, () => {
        if (form.errors.first_name) form.clearErrors('first_name')
    })

    // Clear last_name errors when input changes
    watch(() => form.last_name, () => {
        if (form.errors.last_name) form.clearErrors('last_name')
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
    <Head :title="`${page.props.app.name} | Account - Sign Up`" />

    <div class="d-lg-flex">
        <!-- Register form + Footer -->
        <div class="d-flex flex-column min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">
            <!-- Auth-Logo -->
            <AuthLogo />

            <h1 class="h2 mt-auto">Hi there,</h1>
            <div class="nav fs-sm mb-4">
                Already have an account?
                <Link class="nav-link text-decoration-underline p-0 ms-2" :href="route('login')">Sign In</Link>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit">

                <div v-if="errors.provider" class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ props.errors.provider }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="position-relative mb-4">
                            <label for="first_name" class="form-label">Firstname</label>
                            <input id="first_name" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.first_name }" v-model="form.first_name" autocomplete="first_name" autofocus @focus="form.clearErrors('first_name')" placeholder="First Name">
                            <div v-if="form.errors.first_name" class="invalid-tooltip bg-transparent py-0">{{ form.errors.first_name }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="position-relative mb-4">
                            <label for="last_name" class="form-label">Lastname</label>
                            <input id="last_name" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.last_name }" v-model="form.last_name" autocomplete="last_name" autofocus @focus="form.clearErrors('last_name')" placeholder="Last Name">
                            <div v-if="form.errors.last_name" class="invalid-tooltip bg-transparent py-0">{{ form.errors.last_name }}</div>
                        </div>
                    </div>
                </div>

                <div class="position-relative mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.email }" v-model="form.email" autocomplete="email" autofocus @focus="form.clearErrors('email')" placeholder="Email Address">
                    <div v-if="form.errors.email" class="invalid-tooltip bg-transparent py-0">{{ form.errors.email }}</div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-toggle">
                        <input id="password" type="password" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.password }" v-model="form.password" autocomplete="current-password" @focus="form.clearErrors('password')" placeholder="Minimum 8 characters">
                        <div v-if="form.errors.password" class="invalid-tooltip bg-transparent py-0">{{ form.errors.password }}</div>
                        <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                            <input type="checkbox" class="btn-check">
                        </label>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="policy" v-model="form.policy">
                        <label for="policy" class="form-check-label">I have read and accept the <a class="text-dark-emphasis" href="/">Privacy Policy</a></label>
                    </div>
                </div>

                <!-- Sign Up Button -->
                <LoadingButton :processing="form.processing" type="submit">
                    Sign Up
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
