<script setup lang="ts">
    import { Link, usePage } from '@inertiajs/vue3';
    import { PropType, onMounted, onUnmounted, ref } from 'vue';
    import { route } from 'ziggy-js';
    import noUiSlider from 'nouislider';
    import 'nouislider/dist/nouislider.css';
    import DatePicker from "../../Components/Forms/DatePicker.vue";

    const page = usePage();

    const props = defineProps({
        campaigns: {
            type: Object as PropType<{
                current_page: number;
                data: Campaign[];
                first_page_url: string;
                from: number;
                last_page: number;
                to: number;
                total: number;
            }>,
            required: true
        },
        filters: {
            type: Object as PropType<{
                tab?: string;
                goal_min?: number;
                goal_max?: number;
                deadline?: string | null;
                category?: string;
            }>,
            default: () => ({})
        },
        min_goal: Number,
        max_goal: Number
    });

    let slider: noUiSlider.Instance | null = null;
    const minGoal = ref<number>(page.props.filters.goal_min || page.props.min_goal);
    const maxGoal = ref<number>(page.props.filters.goal_max || page.props.max_goal);

    const initSlider = () => {
        const sliderElement = document.querySelector('.range-slider-ui') as HTMLElement;
        if (!sliderElement) return;

        const stepValue = Math.max(1, Math.floor((page.props.max_goal - page.props.min_goal) / 100)) || 1;

        noUiSlider.create(sliderElement, {
            start: [minGoal.value, maxGoal.value],
            connect: true,
            range: {
                min: page.props.min_goal,
                max: page.props.max_goal,
            },
            step: stepValue,
            tooltips: [true, true],
            format: {
                to: (value) => `₦${Math.round(value).toLocaleString('en-NG')}`,
                from: (value) => {
                    // Remove currency symbol, commas, and any non-numeric characters except the decimal point
                    const numericValue = value.toString().replace(/[₦,]/g, '').replace(/[^0-9.]/g, '');
                    return parseFloat(numericValue) || 0;
                }
            },
        });

        slider = sliderElement.noUiSlider;

        // Fix the slider update handler
        slider.on('update', (values) => {
            // Convert the formatted values back to numbers
            const minValue = parseFloat(values[0].toString().replace(/[₦,]/g, ''));
            const maxValue = parseFloat(values[1].toString().replace(/[₦,]/g, ''));

            minGoal.value = minValue;
            maxGoal.value = maxValue;
        });
    };

    const updateSlider = () => {
        if (slider) {
            // Ensure values are numbers before setting
            const min = Number(minGoal.value) || page.props.min_goal;
            const max = Number(maxGoal.value) || page.props.max_goal;

            // Validate ranges
            const validMin = Math.max(min, page.props.min_goal);
            const validMax = Math.min(max, page.props.max_goal);

            // Update the reactive values if they were corrected
            if (validMin !== minGoal.value) minGoal.value = validMin;
            if (validMax !== maxGoal.value) maxGoal.value = validMax;

            slider.set([validMin, validMax]);
        }
    };

    onMounted(() => {
        initSlider();
    });

    onUnmounted(() => {
        if (slider) {
            slider.destroy();
        }
    });

    const handleDateChange = (value) => {
        page.props.filters.deadline = value;
    };
</script>

<template>
    <section v-if="$page.url.startsWith('/campaigns')" class="pt-0 pb-4">
        <!-- Title and button START -->
        <div class="row g-3 g-sm-4 g-lg-3 g-xl-4">
            <div class="col-12">
                <!-- Meta START -->
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Filter collapse button (left) -->
                    <input type="checkbox" class="btn-check" id="btn-check-soft">
                    <label class="btn btn-outline-primary mb-0" for="btn-check-soft" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-controls="collapseFilter">
                        <i class="ci-sliders me-2"></i>Show Filters
                    </label>

                    <!-- Showing text (right) -->
                    <div class="text-sm-end me-2">
                        <h5 class="mb-0 text-muted" style="font-weight: normal; font-size: 14px;">
                            Showing {{ campaigns.from }} to {{ campaigns.to }} of {{ campaigns.total }} results
                        </h5>
                    </div>
                </div>
                <!-- Meta END -->
            </div>
        </div>
        <!-- Title and button END -->

        <!-- Collapse body START -->
        <div class="collapse" id="collapseFilter">
            <div class="card card-body p-4 mt-4">
                <!-- Form START -->
                <form class="row g-4" :action="route('campaigns.index')" method="get">
                    <!-- Hidden tab field to maintain the current tab -->
                    <input type="hidden" name="tab" :value="page.props.filters?.tab">

                    <!-- Campaign Goal Range Slider -->
                    <div class="col-md-12 col-lg-12">
                        <div class="mb-3">
                            <label class="form-label">Campaign Goal</label>
                            <div class="position-relative">
                                <div class="range-slider">
                                    <div class="range-slider-ui"></div>
                                    <div class="d-flex align-items-center mt-3">
                                        <div class="position-relative w-50">
                                            <div class="input-group">
                                                <span class="input-group-text">₦</span>
                                                <input type="number" class="form-control form-control-lg" :min="min_goal" name="goal_min" v-model="minGoal" @input="updateSlider"/>
                                            </div>
                                        </div>

                                        <i class="ci-minus text-body-emphasis mx-2"></i>

                                        <div class="position-relative w-50">
                                            <div class="input-group">
                                                <span class="input-group-text">₦</span>
                                                <input type="number" class="form-control form-control-lg" :min="min_goal" name="goal_max" v-model="maxGoal" @input="updateSlider"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="col-md-6 col-lg-6">
                        <div class="mb-3">
                            <label for="min-default-dates" class="form-label">Select date</label>
                            <DatePicker id="min-default-dates" name="deadline" :modelValue="page.props.filters?.deadline"  @update:modelValue="handleDateChange"  />
                        </div>
                    </div>

                    <!-- Campaign Category Select -->
                    <div class="col-md-6 col-lg-6">
                        <div class="mb-3">
                            <label class="form-label" for="category">Campaign Category</label>
                            <select id="category" name="category" class="form-select form-select-lg">
                                <option value="">Select category</option>
                                <option v-for="category in page.props.categories" :key="category.id" :value="category.slug" :selected="page.props.filters?.category === category.slug">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 text-end">
                        <Link :href="route('campaigns.index')" class="btn btn-link text-decoration-none">Clear all</Link>
                        <button type="submit" class="btn btn-primary ms-2">Apply Filter</button>
                    </div>
                </form>
                <!-- Form END -->
            </div>
        </div>
        <!-- Collapse body END -->
    </section>
</template>

<style>
    .range-slider {
        --cz-range-slider-height: 0.125rem;
        --cz-range-slider-bg: var(--cz-border-color);
        --cz-range-slider-connect-bg: var(--cz-emphasis-color);
        --cz-range-slider-handle-size: 0.8125rem;
        --cz-range-slider-handle-bg: var(--cz-body-bg);
        --cz-range-slider-handle-active-bg: var(--cz-emphasis-color);
        --cz-range-slider-handle-border-width: 2px;
        --cz-range-slider-handle-border-color: var(--cz-emphasis-color);
        --cz-range-slider-handle-border-radius: 50%;
        --cz-range-slider-pips-color: var(--cz-body-color);
        --cz-range-slider-pips-font-size: 0.75rem;
        --cz-range-slider-pips-border-width: var(--cz-border-width);
        --cz-range-slider-pips-border-color: #cdd5df;
        --cz-range-slider-tooltip-padding-y: 0.375rem;
        --cz-range-slider-tooltip-padding-x: 0.625rem;
        --cz-range-slider-tooltip-bg: transparent;
        --cz-range-slider-tooltip-color: var(--cz-emphasis-color);
        --cz-range-slider-tooltip-font-size: 0.75rem;
        --cz-range-slider-tooltip-border-radius: var(--cz-border-radius-sm)
    }

    .range-slider-ui {
        background-color: var(--cz-range-slider-bg);
        border: 0;
        box-shadow: none;
        height: var(--cz-range-slider-height);
        margin: 2.25rem 0 1.75rem
    }

    .range-slider-ui .noUi-connect {
        background-color: var(--cz-range-slider-connect-bg)
    }

    .range-slider-ui .noUi-handle {
        background-color: var(--cz-range-slider-handle-bg);
        border: var(--cz-range-slider-handle-border-width) solid var(--cz-range-slider-handle-border-color);
        border-radius: var(--cz-range-slider-handle-border-radius);
        box-shadow: none;
        height: var(--cz-range-slider-handle-size);
        margin-top: calc(var(--cz-range-slider-handle-size)*-.5);
        top: 50%;
        width: var(--cz-range-slider-handle-size)
    }

    .range-slider-ui .noUi-handle:after,.range-slider-ui .noUi-handle:before {
        display: none
    }

    .range-slider-ui .noUi-handle:active,.range-slider-ui .noUi-handle:focus-visible {
        background-color: var(--cz-range-slider-handle-active-bg)
    }

    .range-slider-ui .noUi-handle:focus {
        outline: none
    }

    .range-slider-ui .noUi-handle:focus-visible {
        box-shadow: 0 0 0 .25rem var(--cz-focus-ring-color)
    }

    .range-slider-ui .noUi-marker-normal {
        display: none
    }

    .range-slider-ui .noUi-marker-horizontal.noUi-marker {
        background-color: var(--cz-range-slider-pips-border-color);
        width: var(--cz-range-slider-pips-border-width)
    }

    .range-slider-ui .noUi-marker-horizontal.noUi-marker-large {
        height: .75rem
    }

    .range-slider-ui .noUi-value {
        color: var(--cz-range-slider-pips-color);
        font-size: var(--cz-range-slider-pips-font-size);
        padding-top: .125rem
    }

    .range-slider-ui .noUi-tooltip {
        background-color: var(--cz-range-slider-tooltip-bg);
        border: 0;
        border-radius: var(--cz-range-slider-tooltip-border-radius);
        color: var(--cz-range-slider-tooltip-color);
        font-size: var(--cz-range-slider-tooltip-font-size);
        font-weight: 500;
        line-height: 1.2;
        padding: var(--cz-range-slider-tooltip-padding-y) var(--cz-range-slider-tooltip-padding-x)
    }

    html:not([dir=rtl]) .range-slider-ui.noUi-horizontal .noUi-handle {
        right: calc(var(--cz-range-slider-handle-size)*-.5)
    }

    .noUi-txt-dir-rtl.noUi-horizontal .noUi-handle {
        left: -.5rem
    }
</style>
