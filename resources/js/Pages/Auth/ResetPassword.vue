<script setup lang="ts">
    import {Head, useForm, usePage} from "@inertiajs/vue3";
    import AuthCoverImage from "../../Components/Auth/AuthCoverImage.vue";
    import AuthFooter from "../../Components/Auth/AuthFooter.vue";
    import AuthLogo from "../../Components/Auth/AuthLogo.vue";
    import {onMounted, watch} from "vue";
    import initPasswordToggle from "../../Components/Js/PasswordToggle.js";
    import LoadingButton from "../../Components/Button/LoadingButton.vue";

    interface Props {
        email: string;
        token: string;
    }

    const props = defineProps<Props>();

    const page = usePage()

    const form = useForm({
        token: props.token as string,
        email: props.email as string,
        password: '',
        password_confirmation: '',
    })

    const submit = () => {
        form.post('/password/update', {
            onFinish: () => {
                form.reset('password', 'password_confirmation')
            },
        })
    }

    onMounted(() => {
        initPasswordToggle()
    })

    // Clear password errors when input changes
    watch(() => form.password, () => {
        if (form.errors.password) form.clearErrors('password')
    })

    // Clear both password errors when either field changes
    watch(() => [form.password, form.password_confirmation], () => {
        if (form.errors.password) form.clearErrors('password')
        if (form.errors.password_confirmation) form.clearErrors('password_confirmation')
    })
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Reset Password`" />

    <div class="d-lg-flex">
        <!-- Login form + Footer -->
        <div class="d-flex flex-column min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">
            <!-- Auth-Logo -->
            <AuthLogo />

            <h1 class="h2 mt-auto">Reset Password</h1>
            <div class="nav fs-sm mb-4">
                Set new password for email:
                <a class="nav-link text-decoration-underline p-0 ms-2">{{ email }}</a>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <div class="password-toggle">
                        <input id="password" type="password" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.password }" v-model="form.password" autocomplete="new-password" @focus="form.clearErrors('password')" placeholder="Password">
                        <div v-if="form.errors.password" class="invalid-tooltip bg-transparent py-0">{{ form.errors.password }}</div>
                        <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                            <input type="checkbox" class="btn-check">
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="password-toggle">
                        <input id="password_confirmation" type="password" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.password_confirmation }" v-model="form.password_confirmation" autocomplete="new-password" @focus="form.clearErrors('password_confirmation')" placeholder="Password">
                        <div v-if="form.errors.password_confirmation" class="invalid-tooltip bg-transparent py-0">{{ form.errors.password_confirmation }}</div>
                        <label class="password-toggle-button fs-lg" aria-label="Show/hide password">
                            <input type="checkbox" class="btn-check">
                        </label>
                    </div>
                </div>

                <!-- Reset Password Button -->
                <LoadingButton :processing="form.processing" type="submit">
                    Reset Password
                </LoadingButton>
            </form>

            <!-- Auth-Footer -->
            <AuthFooter />
        </div>

        <!-- Auth Cover image visible on screens > 992px wide (lg breakpoint) -->
        <AuthCoverImage />
    </div>
</template>
