<script setup>
    import { Head, useForm, usePage } from '@inertiajs/vue3'
    import { ref, computed, onMounted, onBeforeUnmount } from "vue";
    import initPasswordToggle from '../../Components/Js/PasswordToggle.js'

    import AuthLogo from '../../Components/Auth/AuthLogo.vue'
    import AuthFooter from '../../Components/Auth/AuthFooter.vue'
    import AuthCoverImage from '../../Components/Auth/AuthCoverImage.vue'
    import LoadingButton from "../../Components/Button/LoadingButton.vue";

    const page = usePage()
    const countdown = ref(0)
    let countdownInterval = null

    const form = useForm({
        email: '',
    })

    // Check if we have throttle data from the server
    if (page.props.errors?.throttle) {
        // Ensure we never set a negative countdown
        countdown.value = Math.max(0, Math.round(page.props.errors.throttle))
    }

    const submit = () => {
        form.post('/password/email', {
            onFinish: () => {
                form.reset('email')
                // Start countdown if we got a throttle error
                if (page.props.errors?.throttle) {
                    startCountdown(Math.max(0, Math.round(page.props.errors.throttle)))
                }
            },
        })
    }

    const startCountdown = (seconds) => {
        // Clear any existing interval
        if (countdownInterval) clearInterval(countdownInterval)

        countdown.value = seconds
        if (seconds > 0) {
            countdownInterval = setInterval(() => {
                countdown.value--
                if (countdown.value <= 0) {
                    clearInterval(countdownInterval)
                }
            }, 1000)
        }
    }

    const buttonText = computed(() => {
        return countdown.value > 0 ? `Resend in ${countdown.value}s` : 'Reset password'
    })

    const isButtonDisabled = computed(() => {
        return countdown.value > 0 || form.processing
    })

    // Start countdown on the component mount if needed
    onMounted(() => {
        initPasswordToggle()
        if (countdown.value > 0) {
            startCountdown(countdown.value)
        }
    })

    // Cleanup interval when a component unmounts
    onBeforeUnmount(() => {
        if (countdownInterval) clearInterval(countdownInterval)
    })
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Password Recovery`" />

    <div class="d-lg-flex">
        <!-- Login form + Footer -->
        <div class="d-flex flex-column min-vh-100 w-100 py-4 mx-auto me-lg-5" style="max-width: 416px">
            <!-- Auth-Logo -->
            <AuthLogo />

            <h1 class="h2 mt-auto">Forgot password?</h1>
            <p class="pb-2 pb-md-3">Enter the email address you used when you joined and we'll send you instructions to reset your password</p>

            <!-- Form -->
            <form class="pb-4 mb-3 mb-lg-4" @submit.prevent="submit">
                <div class="position-relative mb-4">
                    <i class="ci-mail position-absolute top-50 start-0 translate-middle-y fs-lg ms-3"></i>
                    <input id="email" type="email" class="form-control form-control-lg form-icon-start" :class="{ 'is-invalid': form.errors.email }" v-model="form.email" autocomplete="email" autofocus @focus="form.clearErrors('email')" placeholder="Email address">
                    <div v-if="form.errors.email" class="invalid-tooltip bg-transparent py-0">
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- Sign In Button -->
                <LoadingButton :processing="form.processing" type="submit" :disabled="isButtonDisabled">
                    {{ buttonText }}
                </LoadingButton>
            </form>

            <!-- Auth-Footer -->
            <AuthFooter />
        </div>

        <!-- Auth Cover image visible on screens > 992px wide (lg breakpoint) -->
        <AuthCoverImage />
    </div>
</template>
