<template>
    <div class="date-picker position-relative">
        <flat-pickr
            :modelValue="displayValue"
            @update:modelValue="handleInput"
            :config="config"
            :placeholder="placeholder"
            :name="name"
            :disabled="disabled"
            :id="attrs.id"
            :class="['form-control form-control-lg', { 'is-invalid': attrs.class?.includes('is-invalid') }]"
            v-bind="attrs"
            @on-change="handleChange"
            @on-open="handleOpen"
            @on-close="handleClose"
            @focus="handleFocus"
        />
        <span class="position-absolute end-0 top-50 translate-middle-y pe-3">
            <i class="ci-calendar"></i>
        </span>
    </div>
</template>

<script setup lang="ts">
    import { ref, useAttrs, computed } from 'vue';
    import FlatPickr from 'vue-flatpickr-component';
    import 'flatpickr/dist/flatpickr.css';

    const attrs = useAttrs();

    const props = defineProps({
        modelValue: { type: [String, Date, Array, null], default: null },
        placeholder: { type: String, default: 'Select date' },
        name: String,
        disabled: Boolean,
        config: { type: Object, default: () => ({}) },
    });

    const emit = defineEmits(['update:modelValue', 'change', 'open', 'close', 'focus']);

    const defaultConfig = {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        ...props.config,
    };

    const config = ref(defaultConfig);

    // Use a computed property for display value to prevent direct mutation
    const displayValue = computed(() => props.modelValue);

    // Handle input from the flatpickr component
    const handleInput = (value) => {
        const normalizedValue = value ?? null;
        emit('update:modelValue', normalizedValue);
    };

    const handleChange = (selectedDates, dateStr, instance) => {
        const value = props.config?.mode === 'range' ? selectedDates : selectedDates[0] || null;
        emit('change', { selectedDates, dateStr, instance, value });
    };

    const handleOpen = (...args) => emit('open', ...args);
    const handleClose = (...args) => emit('close', ...args);
    const handleFocus = (event) => emit('focus', event);
</script>

<style>
    .flatpickr-calendar {
        border: var(--cz-border-width) solid var(--cz-light-border-subtle);
        border-radius: var(--cz-border-radius);
        box-shadow: var(--cz-box-shadow);
        padding: 0 .5rem;
        width: 325px
    }

    .flatpickr-calendar:after,.flatpickr-calendar:before {
        display: none
    }

    .flatpickr-innerContainer {
        padding-bottom: 1rem
    }

    .flatpickr-months {
        padding: .75rem 0
    }

    .flatpickr-months svg {
        vertical-align: top
    }

    .flatpickr-months .flatpickr-next-month,.flatpickr-months .flatpickr-prev-month {
        top: .75rem
    }

    .flatpickr-months .flatpickr-next-month svg,.flatpickr-months .flatpickr-prev-month svg {
        fill: var(--cz-heading-color)!important
    }

    .flatpickr-time .flatpickr-time-separator,span.flatpickr-weekday {
        color: var(--cz-tertiary-color)
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding: .25rem
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months,.numInput,.numInputWrapper {
        color: var(--cz-heading-color)!important
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months:hover,.numInputWrapper:hover {
        background-color: var(--cz-component-hover-bg)
    }

    .numInput:hover {
        background-color: transparent!important
    }

    .flatpickr-day {
        border-radius: var(--cz-border-radius);
        color: var(--cz-body-color);
        height: 38px;
        line-height: 37px
    }

    .flatpickr-day:focus:not(.flatpickr-disabled):not(.today):not(.selected):not(.startRange):not(.endRange),.flatpickr-day:hover:not(.flatpickr-disabled):not(.today):not(.selected):not(.startRange):not(.endRange) {
        background-color: var(--cz-component-active-bg);
        border-color: var(--cz-component-active-bg);
        color: var(--cz-component-active-color)
    }

    .flatpickr-day.today {
        background-color: transparent!important;
        border-color: var(--cz-component-active-color)!important;
        font-weight: 500
    }

    .flatpickr-day.today:not(.startRange):not(.endRange):not(.selected) {
        color: var(--cz-component-active-color)!important
    }

    .flatpickr-day.today.selected {
        color: #fff!important
    }

    .flatpickr-day.today:hover {
        background-color: transparent
    }

    .flatpickr-day.selected {
        background-color: var(--cz-component-active-color)!important;
        border-color: var(--cz-component-active-color)!important;
        font-weight: 500
    }

    .flatpickr-day.flatpickr-disabled {
        color: var(--cz-tertiary-color)!important;
        text-decoration: line-through
    }

    .flatpickr-day.nextMonthDay,.flatpickr-day.prevMonthDay {
        color: var(--cz-tertiary-color)
    }

    .flatpickr-day.inRange {
        background-color: var(--cz-component-active-bg)!important;
        border-color: var(--cz-component-active-bg)!important;
        box-shadow: -5px 0 0 var(--cz-component-active-bg),5px 0 0 var(--cz-component-active-bg)
    }

    .flatpickr-day.endRange,.flatpickr-day.endRange.seleced,.flatpickr-day.endRange:hover,.flatpickr-day.startRange {
        background-color: var(--cz-component-active-color)!important;
        border-color: var(--cz-component-active-color)!important
    }

    .flatpickr-day.endRange.endRange,.flatpickr-day.selected.endRange,.flatpickr-day.startRange.endRange {
        border-radius: 0 .5rem .5rem 0
    }

    .flatpickr-day.endRange.startRange,.flatpickr-day.selected.startRange,.flatpickr-day.startRange.startRange {
        border-radius: .5rem 0 0 .5rem
    }

    .flatpickr-day.endRange.startRange.endRange,.flatpickr-day.selected.startRange.endRange,.flatpickr-day.startRange.startRange.endRange {
        border-radius: .5rem
    }

    .flatpickr-day.endRange.startRange+.endRange:not(:nth-child(7n+1)),.flatpickr-day.selected.startRange+.endRange:not(:nth-child(7n+1)),.flatpickr-day.startRange.startRange+.endRange:not(:nth-child(7n+1)) {
        box-shadow: -10px 0 0 var(--cz-component-active-color)
    }

    .flatpickr-time .flatpickr-am-pm {
        color: var(--cz-emphasis-color)
    }

    .flatpickr-time input::-moz-selection {
        background-color: transparent
    }

    .flatpickr-time .flatpickr-am-pm:focus,.flatpickr-time .flatpickr-am-pm:hover,.flatpickr-time input::selection,.flatpickr-time input:focus,.flatpickr-time input:hover {
        background-color: transparent
    }

    .flatpickr-time input.flatpickr-hour {
        font-weight: 400
    }

    .flatpickr-calendar.hasTime .flatpickr-time {
        border: 0;
        height: 3.5rem;
        line-height: 3.5rem;
        max-height: 3.5rem;
        padding: .5rem 0
    }

    .flatpickr-calendar.hasTime .flatpickr-time .numInputWrapper {
        line-height: 2.5rem
    }

    .flatpickr-calendar.hasTime .flatpickr-innerContainer+.flatpickr-time {
        border-top: 1px solid var(--cz-border-color)
    }

    .numInputWrapper span {
        border-color: var(--cz-border-color)
    }

    .numInputWrapper span.arrowUp:after {
        border-bottom-color: var(--cz-component-color)!important
    }

    .numInputWrapper span.arrowDown:after {
        border-top-color: var(--cz-component-color)!important
    }

    .numInputWrapper span:hover {
        background: var(--cz-border-color)
    }

    [data-bs-theme=dark] .flatpickr-calendar {
        background: #181d25;
        border-color: #333d4c;
        box-shadow: 0 .5rem 1.875rem -.25rem rgba(8,11,18,.35)
    }
</style>
