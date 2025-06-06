<script setup lang="ts">
    const props = defineProps({
        reward: Object
    })

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG');
    };
</script>

<template>
    <div v-if="reward" class="card card-body border mb-2 reward-item" style="height: 100%;">
        <div class="d-sm-flex justify-content-sm-between align-items-center mb-3">
            <div>
                <span class="text-muted"><i class="ci-gift"></i> Donate</span>
                <h4 class="card-title mb-0">₦{{ formatCurrency(reward.amount) }}</h4>
            </div>
        </div>

        <ul class="list-inline mb-2">
            <li class="list-inline-item me-1 h6 mb-0">
                <i class="ci-arrow-right text-primary me-2"></i>{{ reward.title }}
            </li>
        </ul>

        <p class="fs-sm mb-4 text-muted">
            <i class="ci-arrow-right text-primary me-2"></i>
            {{ reward.description }}
        </p>

        <div class="d-grid mt-auto">
            <button type="button" class="btn btn-lg btn-primary rounded-pill" disabled>
                Selected
            </button>
        </div>
    </div>
</template>
