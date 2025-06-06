<script setup lang="ts">
    import { route } from 'ziggy-js';
    import { ref, watch } from 'vue';

    const props = defineProps<{
        campaign: {
            status_badge: { text: string };
            days_left_text: string;
            slug: string;
        };
        amounts: number[];
        selectedRewardId: string | number;
        selectedAmount: string;
    }>();

    const emit = defineEmits<{
        (e: 'update:selectedRewardId', value: string): void;
        (e: 'update:selectedAmount', value: string): void;
    }>();

    const localSelectedAmount = ref(props.selectedAmount);
    const amountInputReadonly = ref(false);

    watch(
        () => props.selectedAmount,
        (newAmount) => {
            localSelectedAmount.value = newAmount;
            amountInputReadonly.value = !!props.selectedRewardId;
        }
    );

    watch(
        () => props.selectedRewardId,
        (newRewardId) => {
            amountInputReadonly.value = !!newRewardId;
        }
    );

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG', { minimumFractionDigits: 2 });
    };

    const handleInput = (event: Event) => {
        const input = event.target as HTMLInputElement;
        let value = input.value;
        value = value.replace(/^\.|[^\d.]/g, '');
        value = value.replace(/(\..*?)\./g, '$1');
        localSelectedAmount.value = value;
        emit('update:selectedAmount', value);
        emit('update:selectedRewardId', '');
        amountInputReadonly.value = false;
    };

    const selectAmount = (amount: number) => {
        localSelectedAmount.value = amount.toString();
        emit('update:selectedAmount', amount.toString());
        emit('update:selectedRewardId', '');
        amountInputReadonly.value = false;
    };
</script>

<template>
    <div v-if="campaign.status_badge.text !== 'Completed' && campaign.days_left_text !== 'Expired'" class="card p-sm-2 p-lg-0 p-xl-2">
        <div class="card-body">
            <h4 class="h6">Help Us Reach the Goal — Every Little Bit Counts</h4>

            <form class="needs-validation mb-3" id="donatePreviewForm" :action="route('campaigns.donate', campaign.slug)" method="get" novalidate>
                <input type="hidden" id="selectedRewardId" name="rewards_id" :value="props.selectedRewardId">

                <div class="mb-3 d-flex flex-wrap gap-2">
                    <button v-for="roundedAmount in props.amounts" type="button" class="btn donate-amount-btn" :class="{'btn-primary': localSelectedAmount === roundedAmount.toString() && !props.selectedRewardId,'btn-outline-primary': localSelectedAmount !== roundedAmount.toString() || props.selectedRewardId}" :data-amount="roundedAmount" @click="selectAmount(roundedAmount)">
                        ₦{{ formatCurrency(roundedAmount) }}
                    </button>
                </div>

                <div class="mb-3 d-flex align-items-center gap-2">
                    <div class="position-relative flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="text" id="selected-amount" name="selected-amount" class="form-control" placeholder="0.00" v-model="localSelectedAmount" :readonly="amountInputReadonly" required @input="handleInput">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Donate</button>
                </div>
            </form>

            <p class="fs-xs">
                If everyone did just a little, this campaign could reach its goal in no time. Will you be part of the change?
            </p>
        </div>
    </div>
</template>
