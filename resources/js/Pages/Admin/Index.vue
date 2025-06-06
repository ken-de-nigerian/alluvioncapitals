<script setup lang="ts">
    import { Head, usePage, Link, router } from "@inertiajs/vue3";
    import { route } from "ziggy-js";
    import debounce from 'lodash/debounce';
    import Pagination from "../../Components/Navigation/Pagination.vue";
    import Chart from 'chart.js/auto';
    import {onMounted, ref, watch} from "vue";

    const page = usePage();

    const props = defineProps({
        user:Object,
        campaigns: Object,
        filters: Object,
        campaign_count: Number,
        donations: Number,
        first_date: String,
        last_date: String,
        flutterwave_amount: Number,
        monnify_amount: Number,
        paystack_amount: Number,
        stripe_amount: Number
    });

    const search = ref(props.filters?.search || '');

    // Debounced search function
    const performSearch = debounce(() => {
        router.get(route('admin.dashboard'), { search: search.value }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 500);

    watch(search, (newValue) => {
        if (newValue !== props.filters?.search) {
            performSearch();
        }
    });

    const formatCurrency = (amount: number): string => {
        return amount.toLocaleString('en-NG');
    };

    const truncate = (text: string, maxLength: number): string => {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };

    onMounted(() => {
        const ctx = document.getElementById('earningsChart').getContext('2d');
        let earningsChart = null;

        // Initialize the chart with empty or provided data
        function initChart(labels = [], datasets = []) {
            if (earningsChart) {
                earningsChart.destroy(); // Destroy existing chart to prevent memory leaks
            }

            earningsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets.map((dataset, index) => {
                        const colors = [
                            { bg: 'rgba(51,179,107,0.35)', border: '#33b36b', point: '#33b36b' }, // Green (approved)
                            { bg: 'rgba(255,193,7,0.35)', border: '#ffc107', point: '#ffc107' }, // Yellow (pending)
                            { bg: 'rgba(245,82,102,0.35)', border: '#f55266', point: '#f55266' } // Red (failed)
                        ];

                        return {
                            label: dataset.label,
                            fill: true,
                            data: dataset.data,
                            backgroundColor: colors[index % colors.length].bg,
                            borderWidth: 2,
                            borderColor: colors[index % colors.length].border,
                            pointBackgroundColor: colors[index % colors.length].point,
                            pointBorderWidth: 8,
                            pointBorderColor: colors[index % colors.length].bg,
                            pointHoverBorderColor: colors[index % colors.length].border,
                            pointHoverBorderWidth: 6
                        };
                    })
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { color: 'rgba(133,140,151,0.18)' },
                            grid: { color: 'rgba(133,140,151,0.18)' },
                            ticks: {
                                callback: function (value) {
                                    return '₦' + value.toLocaleString('en-NG'); // Format y-axis as currency
                                }
                            }
                        },
                        x: {
                            border: { color: 'rgba(133,140,151,0.18)' },
                            grid: { color: 'rgba(133,140,151,0.18)' }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: 12 },
                                color: '#333'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `${context.dataset.label}: ₦${context.parsed.y.toLocaleString('en-NG')}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Fetch data from the server
        function fetchDonationData(period) {
            const spinner = document.getElementById('chartLoading');
            const canvas = document.getElementById('earningsChart');

            // Show loading spinner and hide canvas
            spinner.classList.remove('d-none');
            canvas.classList.add('d-none');

            fetch(`/api/donations-chart?period=${period}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Ensure data is in the expected format
                    const labels = data.labels || [];
                    const datasets = data.datasets || [];

                    if (earningsChart) {
                        // Update existing chart
                        earningsChart.data.labels = labels;
                        earningsChart.data.datasets = datasets.map((dataset, index) => {
                            const colors = [
                                { bg: 'rgba(51,179,107,0.35)', border: '#33b36b', point: '#33b36b' },
                                { bg: 'rgba(255,193,7,0.35)', border: '#ffc107', point: '#ffc107' },
                                { bg: 'rgba(245,82,102,0.35)', border: '#f55266', point: '#f55266' }
                            ];
                            return {
                                label: dataset.label,
                                fill: true,
                                data: dataset.data,
                                backgroundColor: colors[index % colors.length].bg,
                                borderWidth: 2,
                                borderColor: colors[index % colors.length].border,
                                pointBackgroundColor: colors[index % colors.length].point,
                                pointBorderWidth: 8,
                                pointBorderColor: colors[index % colors.length].bg,
                                pointHoverBorderColor: colors[index % colors.length].border,
                                pointHoverBorderWidth: 6
                            };
                        });
                        earningsChart.update();
                    } else {
                        // Initialize new chart
                        initChart(labels, datasets);
                    }
                })
                .catch(error => {
                    console.error('Error fetching donation data:', error);
                    spinner.innerHTML = '<p class="text-danger">Failed to load chart data. Please try again.</p>';
                })
                .finally(() => {
                    // Hide spinner and show canvas
                    spinner.classList.add('d-none');
                    canvas.classList.remove('d-none');
                });
        }

        // Handle filter changes
        function handleFilterChange() {
            const period = document.getElementById('periodSelect').value;
            fetchDonationData(period);
        }

        // Set up event listener for period select
        const periodSelect = document.getElementById('periodSelect');
        if (periodSelect) {
            periodSelect.addEventListener('change', handleFilterChange);
        }

        // Initial load with a default period
        handleFilterChange();
    });
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Dashboard`" />
    <!-- Dashboard content -->
    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between gap-3 pb-3 mb-2 mb-md-3">
            <h1 class="h2 mb-0">Dashboard</h1>
        </div>

        <!-- Stats -->
        <div class="row g-3 g-xl-4 pb-3 mb-2 mb-sm-3">
            <div class="col-md-3 col-sm-6">
                <div class="h-100 bg-success-subtle rounded-4 text-center p-4">
                    <h2 class="fs-sm pb-2 mb-1">Flutterwave</h2>
                    <div class="h4 pb-1 mb-2">₦{{ formatCurrency(flutterwave_amount) }}</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="h-100 bg-info-subtle rounded-4 text-center p-4">
                    <h2 class="fs-sm pb-2 mb-1">Monnify</h2>
                    <div class="h4 pb-1 mb-2">₦{{ formatCurrency(monnify_amount) }}</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-12">
                <div class="h-100 bg-warning-subtle rounded-4 text-center p-4">
                    <h2 class="fs-sm pb-2 mb-1">Paystack</h2>
                    <div class="h4 pb-1 mb-2">₦{{ formatCurrency(paystack_amount) }}</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-12">
                <div class="h-100 bg-primary-subtle rounded-4 text-center p-4">
                    <h2 class="fs-sm pb-2 mb-1">Stripe</h2>
                    <div class="h4 pb-1 mb-2">₦{{ formatCurrency(stripe_amount) }}</div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-xl-4 pb-3 mb-2 mb-sm-3">
            <div class="col-md-6 col-sm-6">
                <div class="h-100 bg-secondary-subtle rounded-4 text-center p-4">
                    <h2 class="fs-sm pb-2 mb-1">Donations Received</h2>
                    <div class="h2 pb-1 mb-2">₦{{ formatCurrency(user.balance) }}</div>
                    <p class="fs-sm text-body-secondary mb-0">From {{ first_date }} - {{ last_date }}</p>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="h-100 bg-secondary-subtle rounded-4 text-center p-4">
                    <h2 class="fs-sm pb-2 mb-1">Campaigns</h2>
                    <div class="h2 pb-1 mb-2">{{ campaign_count }}</div>
                    <p class="fs-sm text-body-secondary mb-0">Active & Inactive</p>
                </div>
            </div>
        </div>

        <!-- Earnings chart -->
        <div class="pb-3 mb-2 mb-sm-3">
            <div class="border rounded-4 py-4 px-3 px-sm-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4">
                    <!-- Title -->
                    <h2 class="h5 text-center mb-3 mb-sm-0">Donations History</h2>

                    <!-- Period select -->
                    <div class="position-relative mb-3 mb-sm-0" style="width: 190px;">
                        <i class="ci-calendar position-absolute top-50 start-0 translate-middle-y z-1 ms-3"></i>
                        <select id="periodSelect" class="form-select rounded-pill ps-5" aria-label="Select period">
                            <option value="current_month">Current Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="last_3_months">Last 3 Months</option>
                            <option value="last_6_months">Last 6 Months</option>
                            <option value="last_year">Last Year</option>
                        </select>
                    </div>
                </div>

                <!-- Chart -->
                <div id="chartLoading" class="d-flex justify-content-center align-items-center" style="height: 350px; min-height: 300px;">
                    <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <canvas id="earningsChart" style="height: 350px; max-height: 350px;" class="d-none"></canvas>
            </div>
        </div>

        <!-- Campaigns -->
        <div class="border rounded-4 py-4 px-3 px-sm-4">
            <template v-if="props.campaigns.data.length > 0">
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mb-2 mb-sm-3 mb-md-4">
                    <h2 class="h5 text-center text-sm-start mb-0">Campaigns</h2>
                    <div class="position-relative w-100" style="max-width: 250px">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                        <input type="search" class="product-search form-control form-icon-start rounded-pill" v-model="search" placeholder="Search campaigns...">
                    </div>
                </div>

                <table class="table align-middle fs-sm mb-4">
                    <thead>
                    <tr>
                        <th class="ps-0" scope="col">
                            <span class="fw-normal text-body">Campaign</span>
                        </th>
                        <th class="d-none d-md-table-cell" scope="col">
                            <span class="fw-normal text-body">Expires</span>
                        </th>
                        <th class="d-none d-md-table-cell" scope="col">
                            <span class="fw-normal text-body">Status</span>
                        </th>
                        <th class="text-end d-none d-sm-table-cell" scope="col">
                            <span class="fw-normal text-body">Goal</span>
                        </th>
                        <th class="text-end pe-0" scope="col">
                            <span class="fw-normal text-body">Raised</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="product-list">
                    <tr v-for="campaign in props.campaigns.data" :key="campaign.id">
                        <td class="py-3 ps-0">
                            <div class="d-flex align-items-start align-items-md-center hover-effect-scale position-relative">
                                <div class="ratio bg-body-secondary rounded-2 overflow-hidden flex-shrink-0" style="--cz-aspect-ratio: calc(52 / 66 * 100%); width: 66px">
                                    <img :src="campaign.first_image" class="hover-effect-target" :alt="campaign.title" loading="lazy">
                                </div>
                                <div class="ps-2 ms-1">
                                    <div class="badge fs-xs rounded-pill d-md-none mb-1" :class="campaign.status_badge.class">{{ campaign.status_badge.text }}</div>
                                    <h6 class="product mb-1 mb-md-0">
                                        <Link class="fs-sm fw-medium hover-effect-underline stretched-link" :href="campaign.show_route" target="_blank">{{ truncate(campaign.title, 25) }}</Link>
                                    </h6>
                                    <div class="fs-body-emphasis d-sm-none mb-1">₦{{ formatCurrency(campaign.goal) }} goal</div>
                                    <div class="fs-body-emphasis d-md-none">{{ campaign.days_left_text }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell py-3">
                            {{ campaign.days_left_text }}
                        </td>
                        <td class="d-none d-md-table-cell py-3">
                            <div class="badge fs-xs rounded-pill" :class="campaign.status_badge.class">{{ campaign.status_badge.text }}</div>
                        </td>
                        <td class="tendered text-end d-none d-sm-table-cell py-3">₦{{ formatCurrency(campaign.goal) }}</td>
                        <td class="earning text-end py-3 pe-0">₦{{ formatCurrency(campaign.funds_raised) }}</td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <Pagination :categories="campaigns" />
            </template>
            <template v-else>
                <h2 class="h6 pt-2 mb-2">You have no campaigns</h2>
                <p class="fs-sm mb-4" style="max-width: 640px">
                    You haven't added any campaigns yet. Create your first campaign to start reaching supporters
                    and raising funds for your cause.
                </p>
                <Link :href="route('campaigns.add.details')" class="btn btn-dark">
                    <i class="ci-plus fs-base ms-n1 me-2"></i>
                    Create Campaign
                </Link>
            </template>
        </div>
    </div>
</template>
