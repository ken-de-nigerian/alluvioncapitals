<script setup lang="ts">
    import { Head, usePage, useForm, Link } from "@inertiajs/vue3";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import { route } from "ziggy-js";
    import "@vueup/vue-quill/dist/vue-quill.snow.css";
    import { onMounted, ref } from "vue";
    import Cleave from "cleave.js";

    const page = usePage();

    const phoneInput = ref(null);
    const countrySelect = ref<HTMLSelectElement | null>(null);
    const stateSelect = ref<HTMLSelectElement | null>(null);

    // Store country and state names along with IDs
    const countryData = ref<Record<string, {id: string, name: string}>>({});
    const stateData = ref<Record<string, {id: string, name: string}>>({});

    // Caching mechanism
    const cachedStates = ref<Record<string, any>>({});
    const debounceTimeout = ref<NodeJS.Timeout | null>(null);

    const props = defineProps({
        old: Object,
        campaign: Object
    });

    const form = useForm({
        first_name: props.old?.first_name || props.campaign?.first_name || '',
        last_name: props.old?.last_name || props.campaign?.last_name || '',
        email: props.old?.email || props.campaign?.email || '',
        phone_number: props.old?.phone_number || props.campaign?.phone_number || '',
        country: props.old?.country || props.campaign?.country || '',
        country_id: props.old?.country_id || props.campaign?.country_id || '',
        state: props.old?.state || props.campaign?.state || '',
        state_id: props.old?.state_id || props.campaign?.state_id || '',
        city: props.old?.city || props.campaign?.city || '',
        address: props.old?.address || props.campaign?.address || '',
    });

    const submit = () => {
        // Prepare the data to submit
        const submitData = {
            ...form.data(),
            // Use country and state names instead of IDs
            country: countryData.value[form.country_id]?.name || form.country,
            state: stateData.value[form.state_id]?.name || form.state,
        };

        form.transform(() => submitData).post(route('campaigns.edit.contact.info.store', props.campaign?.id), {
            preserveScroll: true,
        });
    };

    // Fetch countries on component mount
    const fetchCountries = async () => {
        try {
            const response = await fetch('/api/countries');
            const data = await response.json();
            const countries = data.data || data;

            // Store country data with IDs as keys
            countries.forEach((country: {id: string, name: string}) => {
                countryData.value[country.id] = country;
            });

            populateCountries(countries);
            return countries;
        } catch (error) {
            console.error("Error loading countries:", error);
            return [];
        }
    };

    const debounceFetchStates = (countryId: string) => {
        if (debounceTimeout.value) {
            clearTimeout(debounceTimeout.value);
        }
        debounceTimeout.value = setTimeout(() => fetchStates(countryId), 300);
    };

    const fetchStates = async (countryId: string, selectedStateId: string | null = null) => {
        if (!countryId) return;

        if (cachedStates.value[countryId]) {
            populateStates(cachedStates.value[countryId], selectedStateId);
            // After populating, set the state select value if we have one
            if (selectedStateId && stateSelect.value) {
                stateSelect.value.value = selectedStateId;
            }
            return;
        }

        try {
            const response = await fetch(`/api/states?country=${encodeURIComponent(countryId)}`);
            const data = await response.json();
            const states = data.data || data;

            // Store state data with IDs as keys
            states.forEach((state: {id: string, name: string}) => {
                stateData.value[state.id] = state;
            });

            cachedStates.value[countryId] = states;
            populateStates(states, selectedStateId);

            // After populating, set the state select value if we have one
            if (selectedStateId && stateSelect.value) {
                stateSelect.value.value = selectedStateId;
            }
        } catch (error) {
            console.error("Error loading states:", error);
        }
    };

    const populateCountries = (countries: Array<{ id: string; name: string }>) => {
        if (!countrySelect.value) return;

        countrySelect.value.innerHTML = '<option value="">Select Country</option>';
        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country.id;
            option.textContent = country.name;
            countrySelect.value?.appendChild(option);
        });

        // Set initial value if exists
        if (form.country_id && countrySelect.value) {
            countrySelect.value.value = form.country_id;
        }
    };

    const populateStates = (states: Array<{ id: string; name: string }>, selectedStateId: string | null = null) => {
        if (!stateSelect.value) return;

        stateSelect.value.innerHTML = '<option value="">Select State</option>';
        states.forEach(state => {
            const option = document.createElement('option');
            option.value = state.id;
            option.textContent = state.name;
            stateSelect.value?.appendChild(option);
        });

        // Set initial value if exists
        if (selectedStateId && stateSelect.value) {
            stateSelect.value.value = selectedStateId;
        }
    };

    onMounted(() => {
        if (phoneInput.value) {
            new Cleave(phoneInput.value, {
                numericOnly: true,
                blocks: [0, 3, 0, 4, 4],
                delimiters: ['(', ')', ' ', '-', ' '],
                maxLength: 16
            });
        }

        // Initialize country, state selects
        fetchCountries().then(() => {
            // After countries are loaded, fetch states if we have a country_id
            if (form.country_id) {
                fetchStates(form.country_id, form.state_id);
            }
        });

        // Set up event listeners
        if (countrySelect.value) {
            countrySelect.value.addEventListener('change', function() {
                form.country_id = this.value;
                form.country = ''; // Clear the name (will be set on submit)
                form.state_id = '';
                form.state = ''; // Clear the state name

                if (stateSelect.value) {
                    stateSelect.value.innerHTML = '<option value="">Select State</option>';
                    stateSelect.value.disabled = !this.value;
                }

                if (this.value) {
                    debounceFetchStates(this.value);
                }
            });
        }

        if (stateSelect.value) {
            stateSelect.value.addEventListener('change', function() {
                form.state_id = this.value;
                form.state = ''; // Clear the name (will be set on submit)
            });
        }
    });
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Campaigns: Edit Contact Info`" />

    <main class="content-wrapper">
        <div class="container pt-3 pt-sm-4 pt-md-5 pb-5">
            <div class="row pt-lg-2 pt-xl-3 pb-1 pb-sm-2 pb-md-3 pb-lg-4 pb-xl-5">
                <aside class="col-lg-3 col-xl-4 mb-3" style="margin-top: -100px">
                    <div class="sticky-top overflow-y-auto" style="padding-top: 100px">
                        <ul class="nav flex-lg-column flex-nowrap gap-4 gap-lg-0 text-nowrap pb-2 pb-lg-0">
                            <li class="nat-item">
                                <Link class="nav-link d-inline-flex position-relative px-0 px-lg-3" :href="route('campaigns.edit.details', props.campaign?.id)">
                                    <i class="ci-check-circle fs-lg me-2"></i>
                                    <span class="hover-effect-underline stretched-link">Campaign Details</span>
                                </Link>
                            </li>

                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 pe-none" aria-current="page">
                                    <i class="ci-arrow-right-circle d-none d-lg-inline-flex fs-lg me-2"></i>
                                    <i class="ci-arrow-right-circle d-lg-none fs-lg me-2"></i>
                                    Contact Info
                                </a>
                            </li>

                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 disabled">
                                    <i class="ci-arrow-down-circle fs-lg me-2"></i>
                                    Photos and videos
                                </a>
                            </li>

                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 disabled">
                                    <i class="ci-arrow-down-circle fs-lg me-2"></i>
                                    Supporting Documents
                                </a>
                            </li>

                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 disabled">
                                    <i class="ci-arrow-down-circle fs-lg me-2"></i>
                                    Ad promotion
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>

                <form class="col-lg-9 col-xl-8" @submit.prevent="submit">
                    <h1 class="h2 mb-n2 mb-lg-3">Contact Info</h1>

                    <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 pb-3 pb-sm-4 mb-xl-2">
                        <div class="col">
                            <label for="first_name" class="form-label">First name *</label>
                            <input id="first_name" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.first_name }" v-model="form.first_name" @focus="form.clearErrors('first_name')" placeholder="Enter firstname" />
                            <div v-if="form.errors.first_name" class="invalid-feedback">{{ form.errors.first_name }}</div>
                        </div>

                        <div class="col">
                            <label for="last_name" class="form-label">Last name *</label>
                            <input id="last_name" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.last_name }" v-model="form.last_name" @focus="form.clearErrors('last_name')" placeholder="Enter lastname" />
                            <div v-if="form.errors.last_name" class="invalid-feedback">{{ form.errors.last_name }}</div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 pb-3 pb-sm-4 mb-xl-2">
                        <div class="col">
                            <label for="email" class="form-label">Email *</label>
                            <input id="email" type="email" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.email }" v-model="form.email" @focus="form.clearErrors('email')" placeholder="Enter email address" />
                            <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                        </div>

                        <div class="col">
                            <label for="phone_number" class="form-label">Phone number *</label>
                            <input ref="phoneInput" id="phone_number" type="tel" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.phone_number }" v-model="form.phone_number" autocomplete="off" autofocus @focus="form.clearErrors('phone_number')" placeholder="(___) ___-____">
                            <div v-if="form.errors.phone_number" class="invalid-feedback">{{ form.errors.phone_number }}</div>
                        </div>
                    </div>

                    <h1 class="h2 pb-lg-2">Location</h1>
                    <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 pb-3 pb-sm-4 mb-xl-2">
                        <div class="col">
                            <label class="form-label" for="country-select">Country *</label>
                            <select id="country-select" ref="countrySelect" class="form-select form-control-lg" :class="{ 'is-invalid': form.errors.country }" v-model="form.country_id" @change="form.clearErrors('country')">
                                <option value="">Loading countries...</option>
                            </select>
                            <div v-if="form.errors.country" class="invalid-feedback">{{ form.errors.country }}</div>
                        </div>

                        <div class="col">
                            <label class="form-label" for="state-select">State *</label>
                            <select id="state-select" ref="stateSelect" class="form-select form-control-lg" :class="{ 'is-invalid': form.errors.state }" v-model="form.state_id" @change="form.clearErrors('state')" :disabled="!form.country_id">
                                <option value="">Select State</option>
                            </select>
                            <div v-if="form.errors.state" class="invalid-feedback">{{ form.errors.state }}</div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 pb-3 pb-sm-4 mb-xl-2">
                        <div class="col-md-12">
                            <label for="city" class="form-label">City *</label>
                            <input id="city" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.city }" v-model="form.city" @focus="form.clearErrors('city')" placeholder="Enter city">
                            <div v-if="form.errors.city" class="invalid-feedback">{{ form.errors.city }}</div>
                        </div>
                    </div>

                    <div class="pb-4 mb-2">
                        <label for="address" class="form-label">Address *</label>
                        <textarea type="text" class="form-control form-control-lg" id="address" :class="{ 'is-invalid': form.errors.address }" v-model="form.address" @focus="form.clearErrors('address')" rows="3" placeholder="Enter address">{{ props.old?.address }}</textarea>
                        <div v-if="form.errors.address" class="invalid-feedback">{{ form.errors.address }}</div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="sticky-bottom bg-body pb-3">
        <div class="progress rounded-0" role="progressbar" aria-label="Dark example" aria-valuenow="42.86" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
            <div class="progress-bar bg-dark d-none-dark" style="width: 42.86%"></div>
            <div class="progress-bar bg-light d-none d-block-dark" style="width: 42.86%"></div>
        </div>

        <div class="container d-flex gap-3 pt-3">
            <Link class="btn btn-outline-dark animate-slide-start" :href="route('campaigns.edit.details', props.campaign?.id)">
                <i class="ci-arrow-left animate-target fs-base ms-n1 me-2"></i> Back
            </Link>

            <LoadingButton type="button" :custom-classes="'btn btn-dark animate-slide-end ms-auto'" :processing="form.processing" @click="submit">
                Next <i class="ci-arrow-right animate-target fs-base ms-2 me-n1 align-middle"></i>
            </LoadingButton>
        </div>
    </footer>
</template>
