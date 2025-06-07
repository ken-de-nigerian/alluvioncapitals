<script setup lang="ts">
    import { Head, useForm, usePage, Link } from '@inertiajs/vue3'
    import { ref, computed, onMounted, onBeforeUnmount } from "vue";

    import AuthLogo from '@/Components/Auth/AuthLogo.vue'
    import AuthCoverImage from '@/Components/Auth/AuthCoverImage.vue'
    import LoadingButton from "@/Components/Button/LoadingButton.vue";
    import {route} from "ziggy-js";

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

    <div class="row">
        <div class="col-12 col-md-6 col-xl-4 minvheight-100 d-flex flex-column px-0">

            <!-- Auth-Logo -->
            <AuthLogo />

            <div class="h-100 py-4 px-3">
                <div class="row h-100 align-items-center justify-content-center mt-md-4">
                    <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                        <h1 class="h2 mt-auto">Sorry! You have to be here,</h1>
                        <div class="nav fs-sm mb-4">
                            Provide your registered email address, we will send you an email with change
                            password link with steps.
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit">
                            <div class="form-floating mb-4">
                                <input id="email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.email }" v-model="form.email" autocomplete="email" autofocus @focus="form.clearErrors('email')" placeholder="Email">
                                <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                                <label for="email">Email Address</label>
                            </div>

                            <!-- Sign In Button -->
                            <LoadingButton :custom-classes="'btn btn-lg btn-theme w-100 mb-4'" :processing="form.processing" type="submit" :disabled="isButtonDisabled">
                                {{ buttonText }}
                            </LoadingButton>

                            <div class="text-center mb-3">
                                Already have a password? <Link :href="route('login')" class=" ">Login</Link> here.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auth Cover image -->
        <AuthCoverImage />
    </div>
</template>
