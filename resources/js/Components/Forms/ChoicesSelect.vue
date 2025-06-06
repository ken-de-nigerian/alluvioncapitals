<script setup lang="ts">
    import {onMounted, onBeforeUnmount, watch, ref, PropType} from 'vue';
    import Choices from 'choices.js';
    import 'choices.js/public/assets/styles/choices.css';

    interface Option {
        value: string | number;
        label: string;
    }

    const props = defineProps({
        modelValue: {
            type: [String, Number, Array] as PropType<string | number | (string | number)[]>,
            default: ''
        },
        options: {
            type: Array as PropType<Option[]>,
            default: () => []
        },
        placeholder: {
            type: String,
            default: 'Select an option'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        searchEnabled: {
            type: Boolean,
            default: true
        },
        removeItemButton: {
            type: Boolean,
            default: true
        },
        required: {
            type: Boolean,
            default: false
        }
    });

    const emit = defineEmits(['update:modelValue']);
    const selectElement = ref<HTMLSelectElement | null>(null);
    const choicesInstance = ref<Choices | null>(null);
    const wrapperClasses = ref({
        'choices-select-wrapper': true,
        'is-required': props.required,
        'is-invalid': false
    });

    onMounted(() => {
        if (!selectElement.value) return;

        try {
            // Remove required attribute from the actual select element
            selectElement.value.removeAttribute('required');

            // Initialize Choices with proper class configuration
            choicesInstance.value = new Choices(selectElement.value, {
                removeItemButton: props.removeItemButton,
                searchEnabled: props.searchEnabled,
                shouldSort: false,
                placeholderValue: props.placeholder,
                duplicateItemsAllowed: false,
                // Fixed classNames configuration
                classNames: {
                    containerOuter: 'choices',
                    containerInner: 'choices__inner',
                    input: 'choices__input',
                    inputCloned: 'choices__input--cloned',
                    list: 'choices__list',
                    listItems: 'choices__list--multiple',
                    listSingle: 'choices__list--single',
                    listDropdown: 'choices__list--dropdown',
                    item: 'choices__item',
                    itemSelectable: 'choices__item--selectable',
                    itemDisabled: 'choices__item--disabled',
                    itemChoice: 'choices__item--choice',
                    placeholder: 'choices__placeholder',
                    group: 'choices__group',
                    groupHeading: 'choices__heading',
                    button: 'choices__button',
                    activeState: 'is-active',
                    focusState: 'is-focused',
                    openState: 'is-open',
                    disabledState: 'is-disabled',
                    highlightedState: 'is-highlighted',
                    selectedState: 'is-selected',
                    flippedState: 'is-flipped',
                    loadingState: 'is-loading',
                    noResults: 'has-no-results',
                    noChoices: 'has-no-choices'
                }
            });

            // Add required class separately
            if (props.required) {
                const container = selectElement.value.closest('.choices');
                if (container) {
                    container.classList.add('required');
                }
            }

            // Set initial value
            if (props.modelValue) {
                setChoicesValue(props.modelValue);
            }

            // Set initial options
            if (props.options.length) {
                setChoicesOptions(props.options);
            }

            // Listen for changes
            selectElement.value.addEventListener('change', handleChange);

        } catch (error) {
            console.error('Choices initialization error:', error);
        }
    });

    const setChoicesValue = (value: string | number | (string | number)[]) => {
        if (!choicesInstance.value) return;

        if (Array.isArray(value)) {
            choicesInstance.value.setValue(value);
        } else {
            choicesInstance.value.setChoiceByValue(String(value));
        }
    };

    const setChoicesOptions = (options: Option[]) => {
        if (!choicesInstance.value) return;

        choicesInstance.value.setChoices(
            options.map(option => ({
                value: String(option.value),
                label: option.label
            })),
            'value',
            'label',
            true
        );
    };

    const handleChange = (event: Event) => {
        const target = event.target as HTMLSelectElement;
        let newValue: string | number | (string | number)[];

        if (props.multiple) {
            newValue = Array.from(target.selectedOptions).map(option => option.value);
        } else {
            newValue = target.value;
        }

        emit('update:modelValue', newValue);

        // Update validation state
        if (props.required) {
            wrapperClasses.value['is-invalid'] = !target.value;
        }
    };

    watch(() => props.modelValue, (newValue) => {
        if (newValue) {
            setChoicesValue(newValue);
        }
    });

    watch(() => props.options, (newOptions) => {
        if (newOptions.length) {
            setChoicesOptions(newOptions);
        }
    }, { deep: true });

    onBeforeUnmount(() => {
        if (choicesInstance.value) {
            choicesInstance.value.destroy();
            choicesInstance.value = null;
        }

        if (selectElement.value) {
            selectElement.value.removeEventListener('change', handleChange);
        }
    });
</script>

<template>
    <select ref="selectElement" :multiple="multiple" class="form-select" @change="handleChange" :aria-required="required ? 'true' : null">
        <option v-if="!multiple" value="">{{ placeholder }}</option>
        <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>

<style>
    /* Required field styling */
    .choices-required .choices__inner {
        border-color: #495057;
    }

    .choices-required--invalid .choices__inner {
        border-color: #dc3545;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .choices-required--invalid .choices__inner:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }

    /* Hide the native select but keep it accessible */
    select.form-select {
        position: absolute !important;
        height: 1px !important;
        width: 1px !important;
        overflow: hidden !important;
        clip: rect(1px, 1px, 1px, 1px) !important;
        white-space: nowrap !important;
    }
    .choices__inner {
        display: inline-block;
        vertical-align: top;
        width: 100%;
        background-color: transparent;
        padding: 7.5px 7.5px 3.75px;
        border: 1px solid #4e5562;
        border-radius: 0.5rem;
        font-size: 14px;
        min-height: 44px;
        overflow: hidden;
    }

    .choices {
        margin-bottom: 0
    }

    .choices[data-type*=select-one]:after {
        display: none
    }

    .choices[data-type*=select-one] .form-select {
        padding-right: 3.25rem
    }

    .choices[data-type*=select-one] .choices__button {
        background-size: 9px;
        box-shadow: none!important;
        left: auto;
        margin-left: 0;
        margin-right: 2rem;
        opacity: .4;
        right: 0;
        transition: opacity .15s ease-in-out
    }

    .choices[data-type*=select-one] .choices__button:hover {
        opacity: .8
    }

    .filter-select:has(.choices__item:not(.choices__placeholder)) {
        --cz-form-control-border-color: #181d25
    }

    .choices[data-type*=select-multiple] .form-select,.choices[data-type*=text] .form-select {
        background-image: none;
        padding: .53rem .53rem .28rem
    }

    .choices[data-type*=select-multiple] .form-select.form-select-lg,.choices[data-type*=text] .form-select.form-select-lg {
        padding: .685rem .685rem .435rem
    }

    .choices[data-type*=select-multiple] .form-select.form-select-sm,.choices[data-type*=text] .form-select.form-select-sm {
        padding: .375rem .375rem .125rem
    }

    .choices__placeholder {
        color: var(--cz-tertiary-color);
        opacity: 1
    }

    .is-focused .form-select {
        border-color: var(--cz-form-control-focus-border-color)
    }

    .is-focused .choices__inner, .is-open .choices__inner {
        border: 1px solid #4e5562;
        border-radius: 0.5rem;
    }

    .is-disabled .form-select {
        background-color: var(--cz-tertiary-bg);
        border-color: var(--cz-border-color);
        border-style: dashed;
        color: var(--cz-tertiary-color)
    }

    .choices__list--dropdown,.choices__list[aria-expanded] {
        background-color: var(--cz-body-bg);
        border: var(--cz-border-width) solid var(--cz-light-border-subtle)!important;
        border-radius: var(--cz-border-radius)!important;
        box-shadow: var(--cz-box-shadow)!important;
        font-size: .875rem;
        margin: .3125rem 0!important;
        padding: .75rem;
        z-index: 10
    }

    .choices__list--dropdown .choices__placeholder,.choices__list[aria-expanded] .choices__placeholder {
        display: none!important
    }

    .choices__list--dropdown .choices__list,.choices__list[aria-expanded] .choices__list {
        max-height: 240px
    }

    .choices__list--single {
        display: flex;
        padding: 4px 16px 4px 7px;
    }

    .choices__list--single .choices__item {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .choices__list--dropdown .choices__item,.choices__list[aria-expanded] .choices__item {
        align-items: center;
        border-radius: calc(var(--cz-border-radius)*.75)!important;
        color: var(--cz-component-color);
        display: flex;
        padding: .5rem .75rem!important
    }

    .choices__list--dropdown .choices__item.is-highlighted,.choices__list[aria-expanded] .choices__item.is-highlighted {
        background-color: var(--cz-component-active-bg);
        color: var(--cz-component-active-color)
    }

    .choices .choices__input {
        background-color: transparent!important;
        color: var(--cz-body-color);
        margin: 0 0 .25rem;
        padding: .25rem 0 .25rem .375rem
    }

    .choices .choices__input::-moz-placeholder {
        color: var(--cz-tertiary-color);
        opacity: 1
    }

    .choices .choices__input::placeholder {
        color: var(--cz-tertiary-color);
        opacity: 1
    }

    .choices:not([data-type*=select-multiple]):not([data-type*=text]) .choices__input {
        background: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22' height='22' fill='%239ca3af' viewBox='0 0 32 32'%3E%3Cpath d='m21.6 20.4-3.8-3.8c1.2-1.5 2-3.5 2-5.6 0-4.9-4-8.9-8.9-8.9S2 6.1 2 11s4 8.9 8.9 8.9c2.1 0 4.1-.8 5.6-2l3.8 3.8c.3.3.9.3 1.2 0 .5-.4.5-1 .1-1.3M16.1 16l-.1.1c-1.3 1.2-3 2-5 2-3.9 0-7.1-3.2-7.1-7.1S7.1 3.9 11 3.9s7.1 3.2 7.1 7.1c0 1.9-.8 3.7-2 5'/%3E%3C/svg%3E") no-repeat .125rem .75rem;
        border-color: var(--cz-border-color)!important;
        margin-bottom: .3125rem!important;
        margin-top: -.375rem!important;
        padding-left: 1.75rem!important
    }

    .choices__list--dropdown .choices__item--selectable:after,.choices__list[aria-expanded] .choices__item--selectable:after {
        background-color: currentcolor;
        content: "";
        flex-shrink: 0;
        height: 1.3125rem;
        margin-left: auto;
        margin-right: -.25rem;
        margin-top: .1875rem;
        -webkit-mask: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cpath d='M20.619 5.381a.875.875 0 0 1 0 1.238l-11 11a.875.875 0 0 1-1.238 0l-5-5A.875.875 0 1 1 4.62 11.38L9 15.763 19.381 5.38a.875.875 0 0 1 1.238 0Z'/%3E%3C/svg%3E") no-repeat 50% 50%;
        mask: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cpath d='M20.619 5.381a.875.875 0 0 1 0 1.238l-11 11a.875.875 0 0 1-1.238 0l-5-5A.875.875 0 1 1 4.62 11.38L9 15.763 19.381 5.38a.875.875 0 0 1 1.238 0Z'/%3E%3C/svg%3E") no-repeat 50% 50%;
        -webkit-mask-size: cover;
        mask-size: cover;
        opacity: 0;
        width: 1.3125rem
    }

    .choices__list--dropdown .choices__item--selectable.is-highlighted:after,.choices__list[aria-expanded] .choices__item--selectable.is-highlighted:after {
        opacity: .85
    }

    .choices__heading {
        border-color: var(--cz-border-color);
        color: var(--cz-heading-color);
        font-size: .875rem;
        font-weight: 600;
        margin-bottom: .3125rem;
        padding: 1rem .75rem .75rem
    }

    .choices.is-disabled .choices__list--multiple .choices__item,.choices__list--multiple .choices__item {
        background-color: var(--cz-emphasis-color);
        border-color: var(--cz-emphasis-color);
        margin-bottom: .25rem;
        margin-right: .25rem
    }

    .choices.is-disabled .choices__list--multiple .choices__item .choices__button,.choices__list--multiple .choices__item .choices__button {
        border-left: 1px solid hsla(0,0%,100%,.3);
        border-right: 0;
        margin-left: .5rem;
        margin-right: -.25rem;
        padding-left: 1rem;
        padding-right: .375rem
    }

    .choices.is-disabled .choices__list--multiple .choices__item.is-highlighted,.choices__list--multiple .choices__item.is-highlighted {
        background-color: var(--cz-secondary-color);
        border-color: var(--cz-secondary-color)
    }

    .choices.is-disabled .choices__list--multiple .choices__item {
        opacity: .45
    }

    .choices:has(.is-invalid) .form-select,.was-validated .choices:has(.form-select:invalid) .form-select {
        border-color: var(--cz-form-invalid-border-color)
    }

    .choices:has(.is-invalid)~.invalid-feedback,.choices:has(.is-invalid)~.invalid-tooltip,.was-validated .choices:has(.form-select:invalid)~.invalid-feedback,.was-validated .choices:has(.form-select:invalid)~.invalid-tooltip {
        display: block
    }

    .choices:has(.is-valid)~.valid-feedback,.choices:has(.is-valid)~.valid-tooltip,.was-validated .choices:has(.form-select:valid)~.valid-feedback,.was-validated .choices:has(.form-select:valid)~.valid-tooltip {
        display: block
    }

    [data-bs-theme=dark] .choices[data-type*=select-one] .choices__button {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMSIgaGVpZ2h0PSIyMSIgdmlld0JveD0iMCAwIDIxIDIxIj48ZyBmaWxsPSIjZmZmIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Im0yLjU5Mi4wNDQgMTguMzY0IDE4LjM2NC0yLjU0OCAyLjU0OEwuMDQ0IDIuNTkyeiIvPjxwYXRoIGQ9Ik0wIDE4LjM2NCAxOC4zNjQgMGwyLjU0OCAyLjU0OEwyLjU0OCAyMC45MTJ6Ii8+PC9nPjwvc3ZnPg==")
    }

    [data-bs-theme=dark] .choices.is-disabled .choices__list--multiple .choices__item,[data-bs-theme=dark] .choices__list--multiple .choices__item {
        color: #222934
    }

    [data-bs-theme=dark] .choices.is-disabled .choices__list--multiple .choices__item .choices__button,[data-bs-theme=dark] .choices__list--multiple .choices__item .choices__button {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMSIgaGVpZ2h0PSIyMSIgdmlld0JveD0iMCAwIDIxIDIxIj48ZyBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Im0yLjU5Mi4wNDQgMTguMzY0IDE4LjM2NC0yLjU0OCAyLjU0OEwuMDQ0IDIuNTkyeiIvPjxwYXRoIGQ9Ik0wIDE4LjM2NCAxOC4zNjQgMGwyLjU0OCAyLjU0OEwyLjU0OCAyMC45MTJ6Ii8+PC9nPjwvc3ZnPg==");
        border-color: #cad0d9
    }

    [data-bs-theme=dark] .filter-select:has(.choices__item:not(.choices__placeholder)) {
        --cz-form-control-border-color: #fff
    }

    [data-bs-theme=dark] .choices:has([data-bs-theme=light]) .form-select {
        --cz-form-control-bg: #fff;
        --cz-form-control-border-color: #cad0d9;
        --cz-form-control-focus-bg: #fff;
        --cz-form-control-focus-border-color: #181d25;
        --cz-form-select-bg-img: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%234e5562' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E")
    }

    [data-bs-theme=dark] .choices:has([data-bs-theme=light]) .choices__button {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMSIgaGVpZ2h0PSIyMSIgdmlld0JveD0iMCAwIDIxIDIxIj48ZyBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Im0yLjU5Mi4wNDQgMTguMzY0IDE4LjM2NC0yLjU0OCAyLjU0OEwuMDQ0IDIuNTkyeiIvPjxwYXRoIGQ9Ik0wIDE4LjM2NCAxOC4zNjQgMGwyLjU0OCAyLjU0OEwyLjU0OCAyMC45MTJ6Ii8+PC9nPjwvc3ZnPg==")
    }

    [data-bs-theme=dark] .choices:has([data-bs-theme=light]) .choices.is-disabled .choices__list--multiple .choices__item,[data-bs-theme=dark] .choices:has([data-bs-theme=light]) .choices__list--multiple .choices__item {
        color: #fff
    }

    [data-bs-theme=dark] .choices:has([data-bs-theme=light]) .choices.is-disabled .choices__list--multiple .choices__item .choices__button,[data-bs-theme=dark] .choices:has([data-bs-theme=light]) .choices__list--multiple .choices__item .choices__button {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMSIgaGVpZ2h0PSIyMSIgdmlld0JveD0iMCAwIDIxIDIxIj48ZyBmaWxsPSIjZmZmIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Im0yLjU5Mi4wNDQgMTguMzY0IDE4LjM2NC0yLjU0OCAyLjU0OEwuMDQ0IDIuNTkyeiIvPjxwYXRoIGQ9Ik0wIDE4LjM2NCAxOC4zNjQgMGwyLjU0OCAyLjU0OEwyLjU0OCAyMC45MTJ6Ii8+PC9nPjwvc3ZnPg==");
        border-color: hsla(0,0%,100%,.3)
    }

    [data-bs-theme=dark] .choices:has([data-bs-theme=light]) .filter-select:has(.choices__item:not(.choices__placeholder)) {
        --cz-form-control-border-color: #181d25
    }
</style>
