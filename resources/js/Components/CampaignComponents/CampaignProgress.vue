<script>
export default {
    props: {
        fundsRaised: {
            type: Number,
            required: true
        },
        goal: {
            type: Number,
            required: true
        }
    },
    computed: {
        progress() {
            if (!this.goal || this.goal <= 0 || !this.fundsRaised) return 0;
            return Math.min(parseFloat(((this.fundsRaised / this.goal) * 100).toFixed(2)), 100);
        }
    }
}
</script>

<template>
    <!-- Success progress bar -->
    <div class="fs-sm mb-2">{{ progress }}%</div>
    <div class="progress" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
        <div class="progress-bar bg-success rounded-pill" :style="{ width: progress + '%' }"></div>
    </div>
</template>
