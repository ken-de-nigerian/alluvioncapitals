<script setup lang="ts">
    import { computed } from 'vue'
    import { ref, onMounted } from 'vue'
    import {usePage} from "@inertiajs/vue3";

    const page = usePage();

    const props = defineProps({
        campaign: Object,
    })

    const progressColor = computed(() => {
        if (props.campaign.progress < 30) return 'danger'
        if (props.campaign.progress < 70) return 'warning'
        return 'success'
    })

    const remainingAmount = computed(() => props.campaign.goal - props.campaign.funds_raised)

    const formattedAmount = (amount: number) => {
        return new Intl.NumberFormat('en-NG', {
            style: 'currency',
            currency: 'NGN',
            minimumFractionDigits: 0
        }).format(amount).replace('NGN', '₦')
    }

    const hidePhoneNumber = (phone_number: string): string => {
        // Remove all non-digit characters
        const digits = phone_number.replace(/\D/g, '');

        // Validate we have at least 10 digits (NG phone number)
        if (digits.length < 10) {
            return 'Invalid phone number format';
        }

        // Extract area code (first 3 digits)
        const areaCode = digits.substring(0, 3);

        // Return formatted masked number: (XXX) *** ****
        return `(${areaCode}) *** ****`;
    }

    const getTime = (dateString: string) => {
        const now = new Date();
        const dateAdded = new Date(dateString);
        const elapsed = now - dateAdded;

        // Convert elapsed time to appropriate units
        const seconds = Math.floor(elapsed / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        const months = Math.floor(days / 30); // Approximate, not exact
        const years = Math.floor(days / 365);

        if (years > 0) {
            return years + (years === 1 ? " year ago" : " years ago");
        } else if (months > 0) {
            return months + (months === 1 ? " month ago" : " months ago");
        } else if (days > 0) {
            return days + (days === 1 ? " day ago" : " days ago");
        } else if (hours > 0) {
            return hours + (hours === 1 ? " hour ago" : " hours ago");
        } else if (minutes > 0) {
            return minutes + (minutes === 1 ? " minute ago" : " minutes ago");
        } else {
            return seconds + (seconds === 1 ? " second ago" : " seconds ago");
        }
    }

    const togglePhoneNumber = (event: Event) => {
        const button = event.target as HTMLButtonElement
        if (!props.campaign.phone_number) return // Exit early if no phone number
        if (button.innerHTML.includes('***')) {
            button.innerHTML = `${button.dataset.fullNumber} - hide`
        } else {
            button.innerHTML = `${button.dataset.maskedNumber} - reveal`
        }
    }

    const initials = computed(() => {
        const first = props.campaign?.first_name?.charAt(0) || ''
        const last = props.campaign?.last_name?.charAt(0) || ''
        return `${first}${last}`.toUpperCase()
    })

    const animatedProgress = ref(0)

    onMounted(() => {
        // Animate the progress from 0 to the actual value
        const duration = 1500 // animation duration in ms
        const startTime = performance.now()

        const animate = (currentTime: number) => {
            const elapsedTime = currentTime - startTime
            const progress = Math.min(elapsedTime / duration, 1)

            animatedProgress.value = Math.floor(progress * props.campaign.progress)

            if (progress < 1) {
                requestAnimationFrame(animate)
            }
        }

        requestAnimationFrame(animate)
    })
</script>

<template>
    <div v-if="campaign.featured === 'yes'" class="d-lg-block">
        <div class="d-flex gap-2 pb-1 mb-2">
            <span class="badge text-bg-info d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="This campaign has been verified for authenticity.">
                Verified
                <i class="ci-shield ms-1"></i>
            </span>

            <span class="badge text-bg-warning" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" title="This campaign is featured for its popularity or importance.">
                Featured
            </span>
        </div>
        <div class="h2 pb-1 mb-2"></div>
    </div>

    <div class="card bg-body-tertiary border-0 p-sm-2 p-lg-0 p-xl-2 mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center position-relative mb-3">
                <div class="position-relative rounded-circle overflow-hidden" style="width: 72px; aspect-ratio: 1 / 1;">
                    <img :src="page.props.lazySpinner" alt="Loading..." class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover lazy-placeholder">
                    <img :src="`https://placehold.co/124x124/181d25/ffffff?text=${initials}`" :alt="`${campaign.first_name} ${campaign.last_name}'s avatar`" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover lazy-target" loading="lazy" @load="(e) => (e.target as HTMLElement).previousElementSibling?.remove()">
                </div>

                <div class="w-100 ps-3">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                        <div class="h6 fs-sm hover-effect-underline stretched-link text-decoration-none mb-0">
                            {{ campaign.first_name }} {{ campaign.last_name }}
                        </div>
                        <span class="badge text-bg-light">Organizer</span>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <i class="ci-map-pin text-warning"></i>
                        <span class="fs-sm fw-medium text-dark-emphasis"></span>
                        <span class="fs-xs text-body-secondary">{{ campaign.country }} | created {{ getTime(campaign.created_at) }}</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3">
                <button v-if="campaign.phone_number && typeof campaign.phone_number === 'string' && campaign.phone_number.length >= 11" type="button" class="btn btn-outline-dark" :data-full-number="campaign.phone_number" :data-masked-number="hidePhoneNumber(campaign.phone_number)" @click="togglePhoneNumber">
                    {{ hidePhoneNumber(campaign.phone_number) }} - reveal
                </button>

                <a class="btn btn-primary" :href="`mailto:${campaign.email}`">
                    <i class="ci-mail fs-base me-2"></i>
                    Send email
                </a>
            </div>
        </div>
    </div>

    <div class="card card-body border mb-4">
        <div v-if="campaign.status_badge.text.toLowerCase() === 'completed' || campaign.days_left_text.toLowerCase() === 'expired'" class="text-center py-3">
            <p class="mb-0">Thank you to all our donors, this campaign finished with
                <b>{{ formattedAmount(campaign.funds_raised) }}</b> out of
                <b>{{ formattedAmount(campaign.goal) }}</b> goal.
            </p>
        </div>

        <div v-else>
            <div class="d-flex flex-column align-items-center text-center">
                <h3 class="card-title mb-1 fs-4">{{ formattedAmount(campaign.funds_raised) }}</h3>
                <p class="text-muted mb-3">raised out of <b>{{ formattedAmount(campaign.goal) }}</b> goal</p>

                <div class="circular-progress flex-shrink-0 ms-n2 ms-sm-0 mb-3" :class="`text-${progressColor}`" role="progressbar" :style="`--fn-progress-bar-width: 14px; --fn-progress: ${animatedProgress}`" aria-label="Campaign progress" :aria-valuenow="campaign.progress" aria-valuemin="0" aria-valuemax="100">
                    <svg class="progress-circle">
                        <circle class="progress-background d-none-dark" r="0" style="stroke: #fff"></circle>
                        <circle class="progress-background d-none d-block-dark" r="0" style="stroke: rgba(255,255,255, .1)"></circle>
                        <circle class="progress-bar" r="0"></circle>
                    </svg>
                    <h5 class="position-absolute top-50 start-50 translate-middle text-center mb-0 fs-sm fw-medium">
                        {{ animatedProgress }}%
                    </h5>
                </div>

                <p class="mb-0">
                    <span v-if="remainingAmount > 0" class="text-muted">
                        <b>{{ formattedAmount(remainingAmount) }}</b> needed to reach goal
                    </span>

                    <span v-else class="text-success">
                        Goal exceeded by <b>{{ formattedAmount(Math.abs(remainingAmount)) }}</b>! 🎉
                    </span>
                </p>
            </div>
        </div>
    </div>
</template>
