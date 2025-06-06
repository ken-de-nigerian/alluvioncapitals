<script setup lang="ts">
    import { ref, watch } from 'vue';
    import axios from 'axios';

    const searchQuery = ref('');
    const isLoading = ref(false);
    const showResults = ref(false);
    const searchResults = ref([]);
    const searchTimeout = ref(null);
    const abortController = ref<AbortController | null>(null);

    const performSearch = async () => {
        if (searchQuery.value.length < 2) {
            showResults.value = false;
            searchResults.value = [];
            return;
        }

        // Cancel previous request if it exists
        if (abortController.value) {
            abortController.value.abort();
        }

        isLoading.value = true;
        showResults.value = true;

        // Create a new AbortController
        abortController.value = new AbortController();

        try {
            const response = await axios.get('/campaigns/search', {
                params: { query: searchQuery.value },
                signal: abortController.value.signal
            });

            searchResults.value = response.data;
        } catch (error) {
            if (!axios.isCancel(error)) {
                console.error('Search error:', error);
                searchResults.value = [];
            }
        } finally {
            isLoading.value = false;
            abortController.value = null;
        }
    };

    watch(searchQuery, (newVal) => {
        clearTimeout(searchTimeout.value);

        if (newVal.length < 2) {
            showResults.value = false;
            searchResults.value = [];
            return;
        }

        searchTimeout.value = setTimeout(() => {
            performSearch();
        }, 300);
    });

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG');
    };

    const formatDate = (date: string): string => {
        if (!date) return '';
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }

    const truncate = (text: string, maxLength: number): string => {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };

    const resetSearch = () => {
        clearTimeout(searchTimeout.value);
        if (abortController.value) {
            abortController.value.abort();
            abortController.value = null;
        }
        searchQuery.value = '';
        searchResults.value = [];
        showResults.value = false;
        isLoading.value = false;
    };
</script>

<template>
    <div class="offcanvas offcanvas-top" id="searchBox" data-bs-backdrop="static" tabindex="-1">
        <div class="offcanvas-header border-bottom p-0 py-lg-1">
            <form class="container d-flex align-items-center" @submit.prevent="performSearch">
                <input v-model="searchQuery" type="search" class="form-control form-control-lg fs-lg border-0 rounded-0 py-3 ps-0" placeholder="Search for campaigns" data-autofocus="offcanvas" autocomplete="off">
                <button type="button" class="btn-close fs-lg" data-bs-dismiss="offcanvas" aria-label="Close" @click="resetSearch"></button>
            </form>
        </div>

        <div class="offcanvas-body px-0">
            <!-- Loading spinner -->
            <div v-if="isLoading" class="text-center">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <!-- Initial state (shown by default) -->
            <div v-else-if="!showResults" class="container">
                <div class="text-center">
                    <svg class="text-body-tertiary opacity-60 mb-4" xmlns="http://www.w3.org/2000/svg" width="60" viewBox="0 0 512 512" fill="currentColor">
                        <path d="M340.115,361.412l-16.98-16.98c-34.237,29.36-78.733,47.098-127.371,47.098C87.647,391.529,0,303.883,0,195.765S87.647,0,195.765,0s195.765,87.647,195.765,195.765c0,48.638-17.738,93.134-47.097,127.371l16.98,16.98l11.94-11.94c5.881-5.881,15.415-5.881,21.296,0l112.941,112.941c5.881,5.881,5.881,15.416,0,21.296l-45.176,45.176c-5.881,5.881-15.415,5.881-21.296,0L328.176,394.648c-5.881-5.881-5.881-15.416,0-21.296L340.115,361.412z M195.765,361.412c91.484,0,165.647-74.163,165.647-165.647S287.249,30.118,195.765,30.118S30.118,104.28,30.118,195.765S104.28,361.412,195.765,361.412z M360.12,384l91.645,91.645l23.88-23.88L384,360.12L360.12,384z M233.034,233.033c5.881-5.881,15.415-5.881,21.296,0c5.881,5.881,5.881,15.416,0,21.296c-32.345,32.345-84.786,32.345-117.131,0c-5.881-5.881-5.881-15.415,0-21.296c5.881-5.881,15.416-5.881,21.296,0C179.079,253.616,212.45,253.616,233.034,233.033zM135.529,180.706c-12.475,0-22.588-10.113-22.588-22.588c0-12.475,10.113-22.588,22.588-22.588c12.475,0,22.588,10.113,22.588,22.588C158.118,170.593,148.005,180.706,135.529,180.706z M256,180.706c-12.475,0-22.588-10.113-22.588-22.588c0-12.475,10.113-22.588,22.588-22.588s22.588,10.113,22.588,22.588C278.588,170.593,268.475,180.706,256,180.706z"></path>
                    </svg>
                    <h6 class="mb-2">Your search results will appear here</h6>
                    <p class="fs-sm mb-0">Start typing in the search field above to see instant search results.</p>
                </div>
            </div>

            <!-- Results container -->
            <div v-else class="container">
                <div v-if="searchResults.length > 0">
                    <div v-for="campaign in searchResults" :key="campaign.id" class="d-sm-flex align-items-center mb-3 mobile-mb-3 mobile-mt-5">
                        <article class="card w-100">
                            <div class="d-sm-none" style="margin-top: -44px"></div>
                            <div class="row g-0">
                                <div class="col-sm-4 col-md-3 rounded overflow-hidden pb-2 pb-sm-0 pe-sm-2">
                                    <a :href="campaign.show_route" class="position-relative d-flex h-100 bg-body-tertiary" style="min-height: 174px">
                                        <img :src="campaign.first_image" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" :alt="campaign.title" loading="lazy">
                                        <div class="ratio d-none d-sm-block" style="--fn-aspect-ratio: calc(180 / 240 * 100%)"></div>
                                        <div class="ratio ratio-16x9 d-sm-none"></div>
                                    </a>
                                </div>

                                <div class="col-sm-8 col-md-9 align-self-center">
                                    <div class="card-body d-flex justify-content-between p-3 py-sm-4 ps-sm-2 ps-md-3 pe-md-4 mt-n1 mt-sm-0">
                                        <div class="position-relative pe-3">
                                            <span class="badge text-body-emphasis bg-body-secondary mb-2">{{ truncate(campaign.title, 25) }}</span>

                                            <div class="h5 mb-2">₦{{ formatCurrency(campaign.funds_raised) }}
                                                <sup class="fs-6 fw-lighter">raised</sup>
                                            </div>

                                            <a class="stretched-link d-block fs-sm text-body text-decoration-none mb-2" :href="campaign.show_route">out of ₦{{ formatCurrency(campaign.goal) }} goal.</a>

                                            <!-- Success progress bar -->
                                            <div class="fs-sm mb-2">{{ campaign.progress }}%</div>
                                            <div class="progress" role="progressbar" :aria-valuenow="campaign.progress" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
                                                <div class="progress-bar bg-success rounded-pill" :style="{ width: `${campaign.progress}%` }"></div>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <div class="fs-xs text-body-secondary mb-3">
                                                Created: {{ formatDate(campaign.created_at) }}
                                            </div>

                                            <div class="d-flex justify-content-end gap-2 mb-3">
                                                <!-- Organizer -->
                                                <div class="d-flex align-items-center">
                                                    <span class="mb-0 me-2">by <b>{{ campaign.first_name }} {{ campaign.last_name }}</b></span>
                                                </div>
                                            </div>

                                            <ul class="list-unstyled flex-row flex-wrap justify-content-end fs-sm mb-2">
                                                <li class="d-flex align-items-center me-2 me-md-3">
                                                    <i class="ci-clock fs-base me-1"></i>
                                                    {{ campaign.days_left_text }}
                                                </li>

                                                <li v-if="campaign.status_badge.text.toLowerCase() !== 'completed' && campaign.days_left_text.toLowerCase() !== 'expired'" class="d-flex align-items-center me-2 me-md-3">
                                                    <!-- Share button -->
                                                    <a data-bs-toggle="modal" data-bs-target=".share-modal" class="btn btn-sm btn-secondary px-2 mb-0" :data-url="campaign.show_route" :data-title="campaign.title">
                                                        <i class="ci-share-2"></i>
                                                    </a>
                                                </li>
                                            </ul>

                                            <div class="badge" :class="campaign.status_badge.class">{{ campaign.status_badge.text }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
                <div v-else class="text-center py-4">
                    <h6 class="mb-2">No results found for "{{ searchQuery }}"</h6>
                    <p class="fs-sm mb-0">Try different search terms</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    /* Mobile-only styles */
    @media (max-width: 576px) {
        .mobile-mb-3 {
            margin-bottom: 4rem !important;
        }
        .mobile-mt-5 {
            margin-top: 3rem !important;
        }
    }
</style>
