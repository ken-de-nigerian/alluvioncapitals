<script setup lang="ts">
    import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
    import { route } from "ziggy-js";
    import { onMounted, ref } from "vue";
    import Cleave from "cleave.js";
    import iziToast from "izitoast";
    import "izitoast/dist/css/iziToast.min.css";

    import LoadingButton from "../../Components/Button/LoadingButton.vue";
    import CampaignMetaDetails from "../../Components/CampaignComponents/CampaignMetaDetails.vue";
    import SelectedReward from "../../Components/CampaignComponents/SelectedReward.vue";
    import axios from "axios";

    const page = usePage();
    const phoneInput = ref(null);
    const countrySelect = ref<HTMLSelectElement | null>(null);
    const stateSelect = ref<HTMLSelectElement | null>(null);

    // Store country and state names along with IDs
    const countryData = ref<Record<string, { id: string; name: string }>>({});
    const stateData = ref<Record<string, { id: string; name: string }>>({});

    // Caching mechanism
    const cachedStates = ref<Record<string, any>>({});
    const debounceTimeout = ref<NodeJS.Timeout | null>(null);

    // Reactive state for selected payment gateway
    const selectedGateway = ref<string | null>(null);

    const props = defineProps({
        auth: Object,
        campaign: Object,
        reward: Object,
        selected_amount: String,
        gateways: Object,
    });

    // Initialize form with non-shipping fields
    const form = useForm({
        amount: props.selected_amount || "",
        reward_id: props.reward?.id || "",
        requires_shipping: props.reward?.requires_shipping || false,
        slug: props.campaign?.slug || "",
        first_name: props.auth.user?.first_name || "",
        last_name: props.auth.user?.last_name || "",
        email: props.auth.user?.email || "",
        phone_number: props.auth.user?.phone_number || "",
        anonymous: false,
        comments: "",
        accept_terms: true,
        gateway: "",
        ...(props.reward?.requires_shipping
            ? {
                country: "",
                country_id: "",
                state: "",
                state_id: "",
                city: "",
                postal_code: "",
                address: "",
            }
            : {}),
    });

    const truncate = (text: string, maxLength: number): string => {
        return text.length > maxLength ? text.substring(0, maxLength) + "..." : text;
    };

    onMounted(() => {
        // Initialize Cleave for phone input
        if (phoneInput.value) {
            new Cleave(phoneInput.value, {
                numericOnly: true,
                blocks: [0, 3, 0, 4, 4],
                delimiters: ["(", ")", " ", "-", " "],
                maxLength: 16,
            });
        }

        // Fetch countries and initialize states if applicable
        if (props.reward?.requires_shipping) {
            fetchCountries().then(() => {
                if (form.country_id) {
                    fetchStates(form.country_id, form.state_id);
                }
            });
        }

        // Set up event listeners for country and state selects
        if (countrySelect.value) {
            countrySelect.value.addEventListener("change", function () {
                form.country_id = this.value;
                form.country = "";
                form.state_id = "";
                form.state = "";
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
            stateSelect.value.addEventListener("change", function () {
                form.state_id = this.value;
                form.state = "";
            });
        }

        // Set up payment gateway buttons
        document.querySelectorAll(".payment-gateway-btn").forEach((button) => {
            button.addEventListener("click", function () {
                selectedGateway.value = this.dataset.gateway || null;
                form.gateway = selectedGateway.value; // Update form with selected gateway
                document.querySelectorAll(".payment-gateway-btn").forEach((btn) => btn.classList.remove("active"));
                this.classList.add("active");
            });
        });
    });

    const submit = async () => {
        if (!selectedGateway.value) {
            showToast("warning", "Please select a payment gateway.");
            return;
        }

        form.processing = true;

        const submitData = {
            ...form.data(),
            ...(props.reward?.requires_shipping
                ? {
                    country: countryData.value[form.country_id]?.name || form.country,
                    state: stateData.value[form.state_id]?.name || form.state,
                }
                : {}),
            gateway: selectedGateway.value,
        };

        try {
            const response = await axios.post(route("campaigns.donate.make.payment"), submitData);

            if (response.data.status === "success" && response.data.redirect_url) {
                window.location.href = response.data.redirect_url;
            } else {
                showToast("error", "Payment initialization failed. Please try again.");
            }
        } catch (error) {
            if (error.response && error.response.status === 400) {
                const responseErrors = error.response.data.errors;

                showToast("error", Object.values(responseErrors)[0][0]);
            } else if (error.response && error.response.status === 422) {
                const errors = error.response.data.errors;

                form.errors = {};

                // Set each error in form's error object
                for (let field in errors) {
                    form.errors[field] = errors[field][0];
                }
            } else if (error.response && error.response.data?.message) {
                showToast("error", error.response.data.message);
            } else {
                showToast("error", "An unexpected error occurred.");
            }
        } finally {
            form.processing = false;
        }
    };

    const fetchCountries = async () => {
        try {
            const response = await fetch("/api/countries");
            const data = await response.json();
            const countries = data.data || data;

            countries.forEach((country: { id: string; name: string }) => {
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
            if (selectedStateId && stateSelect.value) {
                stateSelect.value.value = selectedStateId;
            }
            return;
        }

        try {
            const response = await fetch(`/api/states?country=${encodeURIComponent(countryId)}`);
            const data = await response.json();
            const states = data.data || data;

            states.forEach((state: { id: string; name: string }) => {
                stateData.value[state.id] = state;
            });

            cachedStates.value[countryId] = states;
            populateStates(states, selectedStateId);

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
        countries.forEach((country) => {
            const option = document.createElement("option");
            option.value = country.id;
            option.textContent = country.name;
            countrySelect.value?.appendChild(option);
        });

        if (form.country_id && countrySelect.value) {
            countrySelect.value.value = form.country_id;
        }
    };

    const populateStates = (states: Array<{ id: string; name: string }>, selectedStateId: string | null = null) => {
        if (!stateSelect.value) return;

        stateSelect.value.innerHTML = '<option value="">Select State</option>';
        states.forEach((state) => {
            const option = document.createElement("option");
            option.value = state.id;
            option.textContent = state.name;
            stateSelect.value?.appendChild(option);
        });

        if (selectedStateId && stateSelect.value) {
            stateSelect.value.value = selectedStateId;
        }
    };

    const showToast = (type: string, message: string) => {
        const options = {
            message,
            position: "topRight",
            timeout: 4000,
            progressBar: true,
        };

        switch (type) {
            case "success":
                iziToast.success({ ...options, title: "Success" });
                break;
            case "error":
                iziToast.error({ ...options, title: "Error" });
                break;
            case "info":
                iziToast.info({ ...options, title: "Info" });
                break;
            case "warning":
                iziToast.warning({ ...options, title: "Warning" });
                break;
            default:
                iziToast.show({ ...options, title: "Notice" });
        }
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Donate`" />

    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav class="pb-2" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><Link href="/">Home</Link></li>
                <li class="breadcrumb-item"><Link :href="route('campaigns.index')">Campaigns</Link></li>
                <li class="breadcrumb-item"><Link :href="route('campaigns.show', campaign.slug)">Show</Link></li>
                <li class="breadcrumb-item active" aria-current="page">{{ truncate(campaign.title, 25) }}</li>
            </ol>
        </nav>

        <div class="row pt-1 pt-sm-3 pt-lg-4 pb-2 pb-md-3 pb-lg-4 pb-xl-5">
            <!-- Donor details form -->
            <div class="col-lg-8 col-xl-7 mb-5 mb-lg-0">
                <div class="d-flex align-items-start">
                    <div class="w-100">
                        <h1 class="h5 mb-md-4">Donor Information</h1>

                        <form @submit.prevent="submit">

                            <input type="hidden" name="amount" id="amount" v-model="form.amount" />
                            <input type="hidden" name="reward_id" id="reward_id" v-model="form.reward_id" />
                            <input type="hidden" name="requires_shipping" id="requires_shipping" v-model="form.requires_shipping" />
                            <input type="hidden" name="slug" id="slug" v-model="form.slug" />

                            <!-- Personal Information -->
                            <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 mb-4">
                                <div class="col">
                                    <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                                    <input id="first_name" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.first_name }" v-model="form.first_name" autocomplete="off" @focus="form.clearErrors('first_name')" placeholder="Enter firstname"/>
                                    <div v-if="form.errors.first_name" class="invalid-feedback">{{ form.errors.first_name }}</div>
                                </div>

                                <div class="col">
                                    <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                                    <input id="last_name" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.last_name }" v-model="form.last_name" autocomplete="off" @focus="form.clearErrors('last_name')" placeholder="Enter lastname"/>
                                    <div v-if="form.errors.last_name" class="invalid-feedback">{{ form.errors.last_name }}</div>
                                </div>

                                <div class="col">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input id="email" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.email }" v-model="form.email" autocomplete="off" @focus="form.clearErrors('email')" placeholder="Enter email address"/>
                                    <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                                </div>

                                <div class="col">
                                    <label for="phone_number" class="form-label">Phone number <span class="text-danger">*</span></label>
                                    <input ref="phoneInput" id="phone_number" type="tel" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.phone_number }" v-model="form.phone_number" autocomplete="off" autofocus @focus="form.clearErrors('phone_number')" placeholder="(___) ___-____"/>
                                    <div v-if="form.errors.phone_number" class="invalid-feedback">{{ form.errors.phone_number }}</div>
                                </div>
                            </div>

                            <!-- Shipping Information (Conditional) -->
                            <div v-if="reward?.requires_shipping" class="shipping-address-section mb-5">
                                <h1 class="h5 mb-md-2">Shipping Information</h1>
                                <div class="alert alert-secondary mb-3 small text-start" role="alert">
                                    Your address is required to ship your thank-you gift. We'll only use this for delivery purposes.
                                </div>

                                <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 mb-4">
                                    <div class="col">
                                        <label class="form-label" for="country-select">Country <span class="text-danger">*</span></label>
                                        <select id="country-select" ref="countrySelect" class="form-select form-control-lg" :class="{ 'is-invalid': form.errors.country }" v-model="form.country_id" @change="form.clearErrors('country')">
                                            <option value="">Loading countries...</option>
                                        </select>
                                        <div v-if="form.errors.country" class="invalid-feedback">{{ form.errors.country }}</div>
                                    </div>

                                    <div class="col">
                                        <label class="form-label" for="state-select">State/Province <span class="text-danger">*</span></label>
                                        <select id="state-select" ref="stateSelect" class="form-select form-control-lg" :class="{ 'is-invalid': form.errors.state }" v-model="form.state_id" @change="form.clearErrors('state')" :disabled="!form.country_id">
                                            <option value="">Select State</option>
                                        </select>
                                        <div v-if="form.errors.state" class="invalid-feedback">{{ form.errors.state }}</div>
                                    </div>

                                    <div class="col">
                                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                        <input id="city" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.city }" v-model="form.city" @focus="form.clearErrors('city')" placeholder="Enter city"/>
                                        <div v-if="form.errors.city" class="invalid-feedback">{{ form.errors.city }}</div>
                                    </div>

                                    <div class="col-12">
                                        <label for="postal_code" class="form-label">Zip/Postal Code <span class="text-danger">*</span></label>
                                        <input id="postal_code" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.postal_code }" v-model="form.postal_code" @focus="form.clearErrors('postal_code')" placeholder="Enter zip/postal code"/>
                                        <div v-if="form.errors.postal_code" class="invalid-feedback">{{ form.errors.postal_code }}</div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="address" class="form-label">Street/House Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="address" :class="{ 'is-invalid': form.errors.address }" v-model="form.address" @focus="form.clearErrors('address')" placeholder="Enter street address"/>
                                        <div v-if="form.errors.address" class="invalid-feedback">{{ form.errors.address }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Privacy Options -->
                            <h3 class="h6">
                                Donate Privately
                                <i class="ci-info text-body-secondary align-middle ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-custom-class="popover-sm" data-bs-content="Your name and details will not be shared publicly."></i>
                            </h3>

                            <div class="form-check mb-lg-4 mb-4">
                                <input type="checkbox" class="form-check-input" id="stay-anonymous" v-model="form.anonymous" />
                                <label for="stay-anonymous" class="form-check-label">Keep my donation anonymous</label>
                            </div>

                            <!-- Login Prompt (Conditional) -->
                            <div v-if="auth.user === null" class="nav mb-4">
                                <div class="alert alert-secondary mt-2 small text-start" role="alert">
                                    Already have an account?
                                    <Link :href="route('login')" class="alert-heading h6">Log in</Link> to prefill your details and speed up
                                    the donation process.
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <h2 class="h5 mb-3">How Would You Like to Donate?</h2>
                            <div class="mb-4">
                                <div class="d-flex flex-wrap gap-3">
                                    <button v-for="gateway in gateways" type="button" class="btn btn-outline-secondary px-4 py-2 payment-gateway-btn" :data-gateway="gateway.name">
                                        <img :src="gateway.icon" class="me-2 mb-0 rounded-5" alt="" loading="lazy" style="width: 30px;" />
                                        {{ gateway.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- Additional Comments -->
                            <div class="mb-4">
                                <label for="additional-comments" class="form-label">Additional Comments (Optional)</label>
                                <textarea id="additional-comments" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.comments }" v-model="form.comments" autocomplete="off" @focus="form.clearErrors('comments')" rows="3" placeholder="Any additional message or comments" aria-describedby="commentsHelp"></textarea>
                                <div v-if="form.errors.comments" class="invalid-feedback">{{ form.errors.comments }}</div>
                                <small id="commentsHelp" class="form-text text-muted">You can leave a message for us here.</small>
                            </div>

                            <!-- Terms Acceptance -->
                            <div class="form-check mb-lg-4">
                                <input type="checkbox" class="form-check-input" id="accept-terms" name="accept_terms" v-model="form.accept_terms"/>
                                <label for="accept-terms" class="form-check-label nav align-items-center">
                                    I accept the
                                    <a class="nav-link text-decoration-underline fw-normal ms-1 p-0" href="#">Terms and Conditions</a>
                                </label>
                                <div v-if="form.errors.accept_terms" class="invalid-feedback">{{ form.errors.accept_terms }}</div>
                            </div>

                            <!-- Submit Button -->
                            <LoadingButton type="submit" :custom-classes="'btn btn-lg btn-primary w-100 mt-4 d-lg-flex'" :processing="form.processing">
                                Donate Now <i class="ci-arrow-right fs-base ms-2 me-n1 align-middle"></i>
                            </LoadingButton>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Campaign summary (sticky sidebar) -->
            <aside class="col-lg-4 offset-xl-1">
                <div class="position-sticky top-0">
                    <!-- Campaign meta visible on screens > 991px (lg breakpoint) -->
                    <CampaignMetaDetails :campaign="campaign" />

                    <!-- Selected Reward Component -->
                    <SelectedReward :reward="reward" />
                </div>
            </aside>
        </div>
    </div>
</template>
