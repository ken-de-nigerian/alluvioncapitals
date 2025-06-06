<script setup lang="ts">
    import { ref, onMounted } from 'vue';
    import Swiper from 'swiper';
    import { Navigation, Pagination } from 'swiper/modules';
    import 'swiper/css';
    import 'swiper/css/navigation';
    import 'swiper/css/pagination';

    const props = defineProps<{
        rewards: Array<{
            id: string;
            amount: number;
            title: string;
            description: string;
        }>;
    }>();

    const emit = defineEmits<{
        (e: 'select-reward', reward: { id: string; amount: number }): void;
    }>();

    const selectedRewardId = ref('');

    onMounted(() => {
        if (props.rewards?.length > 1) {
            new Swiper('.swiper', {
                modules: [Navigation, Pagination],
                slidesPerView: 1,
                spaceBetween: 24,
                loop: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    prevEl: '.btn-prev',
                    nextEl: '.btn-next',
                },
                breakpoints: {
                    550: {
                        slidesPerView: 2,
                    },
                    850: {
                        slidesPerView: 3,
                    },
                    1200: {
                        slidesPerView: 4,
                    },
                },
            });
        }
    });

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG', { minimumFractionDigits: 2 });
    };

    const selectReward = (reward: { id: string; amount: number }) => {
        selectedRewardId.value = reward.id;
        emit('select-reward', { id: reward.id, amount: reward.amount });
    };
</script>

<template>
    <section class="container py-2 py-sm-3 py-md-4 py-lg-5 my-xxl-3">
        <div class="text-center pb-3 mb-2 mb-sm-3">
            <h2 class="mb-2">Thank-You Gifts for You</h2>
            <p class="fs-sm mb-0">Donate and receive our appreciation</p>
        </div>

        <div class="swiper pb-5">
            <div class="swiper-wrapper">
                <div v-for="reward in props.rewards" :key="reward.id" class="swiper-slide h-auto">
                    <div class="card card-body border mb-2 reward-item" :data-reward-id="reward.id" style="height: 100%;">
                        <input type="hidden" class="reward-amount" :value="reward.amount">
                        <input type="hidden" class="reward-id" :value="reward.id">

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

                        <p class="fs-sm mb-4 text-muted" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4;">
                            <i class="ci-arrow-right text-primary me-2"></i>
                            {{ reward.description }}
                        </p>

                        <div class="d-grid mt-auto">
                            <button type="button" class="btn btn-lg rounded-pill" :class="{ 'btn-primary': selectedRewardId === reward.id, 'btn-outline-primary': selectedRewardId !== reward.id }" :disabled="selectedRewardId === reward.id" @click="selectReward(reward)">
                                {{ selectedRewardId === reward.id ? 'Selected' : 'Select' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="swiper-pagination position-static mt-3 mt-lg-4"></div>
        </div>
    </section>
</template>
