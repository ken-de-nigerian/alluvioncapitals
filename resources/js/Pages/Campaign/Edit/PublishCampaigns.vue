<script setup lang="ts">
    import { Head, usePage, useForm } from "@inertiajs/vue3";
    import { ref, onMounted } from "vue";
    import { route } from "ziggy-js";

    const page = usePage();

    // Reactive state
    const planCards = ref<NodeListOf<HTMLElement> | null>(null);
    const addonSwitches = ref<NodeListOf<HTMLInputElement> | null>(null);
    const totalAmountElement = ref<HTMLElement | null>(null);
    const selectedPlanInput = ref<HTMLInputElement | null>(null);
    const selectedAddonsInput = ref<HTMLInputElement | null>(null);
    const isLoading = ref(false);

    const selectedPlan = ref<{ plan: string; price: number } | null>(null);
    const selectedAddons = ref<Array<{ addon: string; price: number }>>([]);
    const totalAmount = ref(0);

    const props = defineProps({
        campaign: Object,
    });

    // ** FIX HERE: parse has_ad_promotion JSON string first **
    const hasAdPromotion = props.campaign?.has_ad_promotion
        ? JSON.parse(props.campaign.has_ad_promotion)
        : null;

    const initialPlan = hasAdPromotion?.selected_plan ?? null;
    const initialAddons = hasAdPromotion?.selected_addons ?? [];

    const form = useForm({
        selected_plan: hasAdPromotion?.selected_plan
            ? JSON.stringify(hasAdPromotion.selected_plan)
            : "",
        selected_addons: hasAdPromotion?.selected_addons
            ? JSON.stringify(hasAdPromotion.selected_addons)
            : "",
    });

    // Initialize the component
    onMounted(() => {

        // Get DOM elements
        planCards.value = document.querySelectorAll(".plan-card");
        addonSwitches.value = document.querySelectorAll(".addon-switch");
        totalAmountElement.value = document.getElementById("totalAmount");
        selectedPlanInput.value = document.getElementById("selectedPlan");
        selectedAddonsInput.value = document.getElementById("selectedAddons");

        let initializedPlan = false;

        // 1. Handle pre-selected plan
        if (initialPlan?.plan) {
            const matchedPlanCard = Array.from(planCards.value).find(
                (card) => card.dataset.plan === initialPlan.plan
            );

            if (matchedPlanCard) {
                selectedPlan.value = {
                    plan: initialPlan.plan,
                    price: initialPlan.price,
                };
                selectPlan(matchedPlanCard as HTMLElement);
                initializedPlan = true;
            }
        }

        // 2. Fallback to featured plan
        if (!initializedPlan) {
            const featuredPlan = document.querySelector(".plan-card.featured");
            if (featuredPlan) {
                selectPlan(featuredPlan as HTMLElement);
            }
        }

        // 3. Initialize addons
        selectedAddons.value = [];
        if (Array.isArray(initialAddons) && initialAddons.length > 0) {
            initialAddons.forEach((addon) => {
                const switchEl = document.querySelector(
                    `.addon-switch[data-addon="${addon.addon}"]`
                ) as HTMLInputElement;

                if (switchEl) {
                    switchEl.checked = true;
                    selectedAddons.value.push({
                        addon: addon.addon,
                        price: addon.price,
                    });
                }
            });
        }

        updateTotal();

        // 4. Set up plan selection handlers
        planCards.value.forEach((card) => {
            card.addEventListener("click", function (e: Event) {
                const target = e.target as HTMLElement;
                if (
                    !target.classList.contains("select-plan") &&
                    !target.closest(".select-plan")
                ) {
                    selectPlan(card);
                }
            });

            const selectButton = card.querySelector(".select-plan");
            if (selectButton) {
                selectButton.addEventListener("click", function (e: Event) {
                    e.stopPropagation();
                    selectPlan(card);
                });
            }
        });

        // 5. Set up addon handlers
        addonSwitches.value.forEach((switchEl) => {
            switchEl.addEventListener("change", function () {
                const addon = this.dataset.addon || "";
                const price = parseFloat(this.dataset.price || "0");

                if (this.checked) {
                    if (!selectedAddons.value.some((item) => item.addon === addon)) {
                        selectedAddons.value.push({ addon, price });
                    }
                } else {
                    selectedAddons.value = selectedAddons.value.filter(
                        (item) => item.addon !== addon
                    );
                }

                updateTotal();
            });
        });
    });

    // Plan selection function
    function selectPlan(card: HTMLElement) {
        if (!planCards.value) return;

        // Remove the selected class from all cards
        planCards.value.forEach((c) => {
            c.classList.remove("border-primary", "border-2");
            const btn = c.querySelector(".select-plan");
            if (btn) {
                btn.classList.remove("btn-primary");
                btn.classList.add("btn-outline-primary");
                btn.textContent = "Select";
            }
        });

        // Add selected class to clicked card
        card.classList.add("border-primary", "border-2");
        const selectBtn = card.querySelector(".select-plan");
        if (selectBtn) {
            selectBtn.classList.remove("btn-outline-primary");
            selectBtn.classList.add("btn-primary");
            selectBtn.textContent = "Selected";
        }

        // Update the selected plan
        selectedPlan.value = {
            plan: card.dataset.plan || "",
            price: parseFloat(card.dataset.price || "0"),
        };

        updateTotal();
    }

    // Update total amount
    function updateTotal() {
        totalAmount.value = selectedPlan.value ? selectedPlan.value.price : 0;

        selectedAddons.value.forEach((addon) => {
            totalAmount.value += addon.price;
        });

        // Update UI
        if (totalAmountElement.value) {
            totalAmountElement.value.textContent = totalAmount.value.toLocaleString(
                "en-US",
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }
            );
        }
    }

    // Submit handler
    function submit() {
        if (!selectedPlan.value) {
            alert("Please select a promotion plan");
            return;
        }

        // Update form data
        form.selected_plan = JSON.stringify({
            plan: selectedPlan.value.plan,
            price: selectedPlan.value.price,
        });

        form.selected_addons = JSON.stringify(selectedAddons.value);

        // Set loading state
        isLoading.value = true;

        // Submit the form
        form.post(route("campaigns.edit.publish.live", props.campaign?.id), {
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        });
    }
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Publish Campaigns`" />

    <!-- Page content -->
    <main class="content-wrapper">
        <div class="container pt-4 pt-md-5 pb-5 my-1 mt-sm-3 mt-md-0 mb-sm-2 mb-md-3 mb-lg-4 mb-xl-5">
            <h1 class="pt-lg-2 pt-xl-3 mb-lg-4">Effective promotion of your campaign</h1>
            <p class="mb-2 mb-sm-3">Maximize your campaign's reach while supporting our community initiatives. All promotion fees go to our nonprofit partners.</p>

            <form @submit.prevent="submit">
                <input type="hidden" name="selected_plan" id="selectedPlan">
                <input type="hidden" name="selected_addons" id="selectedAddons">

                <!-- Pricing plans -->
                <div class="container-fluid px-0">
                    <div class="overflow-x-hidden">
                        <div class="pt-5 pb-3 mb-1">
                            <div class="row g-4">
                                <!-- Starter Plan -->
                                <div class="col-12 col-md-4">
                                    <div class="card h-100 bg-body-tertiary border-0 rounded-5 p-3 plan-card" data-plan="starter" data-price="1250">
                                        <div class="card-body p-2 p-xl-3">
                                            <svg class="d-block mb-3 mb-xl-4" xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none">
                                                <path class="text-primary" d="M51.729 34.356c.085-.254.17-.508.17-.763-2.119 9.068-9.237 16.187-18.305 18.305.254-.085.508-.085.678-.169l-3.475-13.051a10.99 10.99 0 0 0 7.797-7.797l13.136 3.475zm.169-12.034c-.085-.254-.085-.508-.169-.763l-13.051 3.475a10.99 10.99 0 0 0-7.797-7.797l3.475-13.051c-.254-.085-.508-.085-.678-.17 8.983 2.203 16.102 9.322 18.22 18.305zM21.644 4.271l3.475 13.051a10.99 10.99 0 0 0-7.797 7.797L4.271 21.644c-.085.254-.085.509-.17.763C6.22 13.339 13.339 6.22 22.406 4.102c-.254 0-.509.085-.763.17zm3.475 34.407l-3.559 13.136c.254.085.508.085.763.169-9.068-2.119-16.186-9.237-18.305-18.305.085.254.085.508.17.763l13.051-3.475c1.102 3.644 4.153 6.695 7.881 7.712z" fill="currentColor"></path>
                                                <path class="text-secondary-emphasis" d="M45.797 53h-5.085a.4.4 0 1 1 0-.847h5.085c3.475 0 6.356-2.881 6.356-6.356v-5.085a.4.4 0 1 1 .847 0v5.085A7.2 7.2 0 0 1 45.797 53zM28 53c-2.203 0-4.322-.254-6.441-.847-.254-.085-.339-.254-.339-.508l3.39-12.712c-3.644-1.102-6.441-3.983-7.542-7.542L4.356 34.78c-.254.085-.424-.085-.508-.339C3.254 32.322 3 30.203 3 28s.254-4.322.847-6.441c.085-.254.254-.339.508-.339l12.712 3.39c1.102-3.644 3.983-6.441 7.542-7.542L21.22 4.356c-.085-.254.085-.424.339-.508C23.678 3.254 25.797 3 28 3s4.322.254 6.441.847c.254.085.339.254.339.508l-3.39 12.712c3.644 1.102 6.441 3.983 7.542 7.542l12.712-3.39c.254-.085.424.085.509.339.593 2.119.847 4.237.847 6.441s-.254 4.322-.847 6.441c-.085.254-.254.339-.509.339l-12.712-3.39c-1.102 3.644-3.983 6.441-7.542 7.542l3.39 12.712c.085.254-.085.424-.339.509-2.119.593-4.237.847-6.441.847zm-5.847-1.525c1.949.508 3.814.678 5.848.678s3.898-.254 5.847-.678l-3.305-12.288c-1.61.339-3.475.339-5.085 0l-3.305 12.288zm3.136-13.22a11.73 11.73 0 0 0 5.424 0c3.644-1.017 6.441-3.813 7.458-7.457.254-.848.339-1.78.339-2.712s-.085-1.864-.339-2.712c-1.017-3.644-3.814-6.441-7.458-7.458a11.73 11.73 0 0 0-5.424 0c-3.644 1.017-6.441 3.814-7.458 7.458-.254.847-.339 1.78-.339 2.712s.085 1.864.339 2.712c.932 3.559 3.813 6.441 7.458 7.457zm13.898-7.712l12.288 3.305c.508-1.949.678-3.814.678-5.847s-.254-3.898-.678-5.847l-12.288 3.305c.169.847.254 1.695.254 2.542s-.085 1.695-.254 2.542zm-34.661-8.39c-.508 1.949-.678 3.814-.678 5.848s.254 3.898.678 5.847l12.288-3.305c-.17-.847-.254-1.695-.254-2.542s.085-1.695.254-2.542L4.525 22.153zM22.153 4.525l3.305 12.288c1.61-.339 3.475-.339 5.085 0l3.305-12.288c-1.949-.508-3.814-.678-5.847-.678s-3.898.254-5.847.678zM15.288 53h-5.085A7.2 7.2 0 0 1 3 45.797v-5.085a.4.4 0 0 1 .424-.424.4.4 0 0 1 .424.424v5.085c0 3.475 2.881 6.356 6.356 6.356h5.085a.4.4 0 1 1 0 .847zm22.034-1.864c-.169 0-.339-.085-.424-.254-.085-.254 0-.424.254-.593 5.932-2.458 10.763-7.203 13.22-13.136.085-.254.339-.339.593-.254s.339.339.254.593c-2.542 6.102-7.542 11.102-13.644 13.644h-.254zm-18.644 0h-.169c-6.102-2.542-11.102-7.542-13.644-13.644-.085-.254 0-.424.254-.593.254-.085.424 0 .593.254 2.458 5.932 7.203 10.763 13.136 13.22.254.085.339.339.254.593a.65.65 0 0 1-.424.169zm32.034-32.034c-.17 0-.339-.085-.424-.254-2.458-5.932-7.203-10.763-13.136-13.22-.254-.085-.339-.339-.254-.593s.339-.339.593-.254c6.102 2.542 11.102 7.458 13.644 13.644.085.254 0 .424-.254.593 0 .085-.085.085-.169.085zm-45.424 0h-.17c-.254-.085-.339-.339-.254-.593 2.542-6.102 7.542-11.102 13.644-13.644.254-.085.424 0 .593.254.085.254 0 .424-.254.593-5.932 2.458-10.763 7.203-13.22 13.136 0 .169-.169.254-.339.254zm47.288-3.39a.4.4 0 0 1-.424-.424v-5.085c0-3.475-2.881-6.356-6.356-6.356h-5.085a.4.4 0 0 1-.424-.424.4.4 0 0 1 .424-.424h5.085A7.2 7.2 0 0 1 53 10.203v5.085a.4.4 0 0 1-.424.424zm-49.152 0A.4.4 0 0 1 3 15.288v-5.085A7.2 7.2 0 0 1 10.203 3h5.085a.4.4 0 0 1 .424.424.4.4 0 0 1-.424.424h-5.085c-3.475 0-6.356 2.881-6.356 6.356v5.085a.4.4 0 0 1-.424.424z" fill="currentColor"></path>
                                            </svg>

                                            <h3 class="fs-lg fw-normal">Community Starter</h3>
                                            <div class="d-flex align-items-center pb-1 pb-xl-0 mb-2 mb-xl-3">
                                                <div class="h1 mb-0">₦<span class="plan-price">1,250</span></div>
                                                <div class="fs-sm ms-2">/ month</div>
                                            </div>
                                            <p class="fs-sm mb-xl-4">Perfect for grassroots campaigns wanting to test their message while giving back.</p>
                                            <button type="button" class="btn btn-lg btn-outline-primary w-100 select-plan">Start Promoting</button>
                                            <ul class="list-unstyled gap-md-3 fs-sm text-dark-emphasis pt-4 mt-lg-1 mt-xl-2 mb-0">
                                                <li class="d-flex">
                                                    <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                    7-Day Visibility: Basic exposure for your campaign
                                                </li>
                                                <li class="d-flex">
                                                    <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                    Social Proof: "Community Supporter" badge
                                                </li>
                                                <li class="d-flex">
                                                    <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                    Impact Report: See how your donation helps
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Featured Plan -->
                                <div class="col-12 col-md-4" style="margin-top: 50px;">
                                    <div class="position-relative h-100">
                                        <div class="card position-relative h-100 z-2 bg-body-tertiary border-0 rounded-5 p-3 plan-card featured" data-plan="accelerator" data-price="2490">
                                            <div class="card-body p-2 p-xl-3">
                                                <svg class="d-block mb-3 mb-xl-4" xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none">
                                                    <path class="text-primary" d="M44.136 9.966c5.79 4.746 9.491 11.864 9.491 19.932 0 14.142-11.485 25.627-25.627 25.627S2.373 44.041 2.373 29.898 13.858 4.271 28 4.271v5.695c-11.01 0-19.932 8.922-19.932 19.932S16.99 49.831 28 49.831s19.932-8.922 19.932-19.932c0-7.973-4.746-14.807-11.485-18.034h.095 7.593V9.966z" fill="currentColor"></path>
                                                    <path class="text-primary" d="M44.136 9.966v1.898h-7.593V9.966 2.373h7.593v7.593z" fill="currentColor"></path>
                                                    <path class="text-primary" d="M28 21.356c-4.746 0-8.542 3.797-8.542 8.542s3.797 8.542 8.542 8.542 8.542-3.797 8.542-8.542-3.796-8.542-8.542-8.542zm0-5.695a14.22 14.22 0 0 1 14.237 14.237A14.22 14.22 0 0 1 28 44.136a14.22 14.22 0 0 1-14.237-14.237A14.22 14.22 0 0 1 28 15.661z" fill="currentColor"></path>
                                                    <path class="text-primary" d="M36.543 2.373v7.593h-.949-7.593V4.271.475h8.542v1.898zM28 27.051c1.614 0 2.847 1.234 2.847 2.848S29.614 32.746 28 32.746s-2.847-1.234-2.847-2.848 1.234-2.847 2.848-2.847z" fill="currentColor"></path>
                                                    <path class="text-secondary-emphasis" d="M28 56A26.09 26.09 0 0 1 1.898 29.898c0-14.237 11.485-25.817 25.627-26.102V.475c0-.285.19-.475.475-.475h8.542c.285 0 .475.19.475.475V11.39h6.644V2.847h-3.322c-.285 0-.475-.19-.475-.475s.19-.475.475-.475h3.797c.285 0 .475.19.475.475v9.491c0 .285-.19.475-.475.475h-7.593c-.285 0-.475-.19-.475-.475v-1.424h-4.271c-.285 0-.475-.19-.475-.475s.19-.475.475-.475h4.271V.949h-7.593v28.949c0 .285-.19.475-.475.475s-.475-.19-.475-.475V27.62c-1.044.19-1.898 1.139-1.898 2.278A2.35 2.35 0 0 0 28 32.271a2.35 2.35 0 0 0 2.373-2.373c0-.285.19-.475.475-.475s.475.19.475.475c0 1.803-1.519 3.322-3.322 3.322s-3.322-1.519-3.322-3.322c0-1.708 1.234-3.037 2.847-3.322V21.83c-4.271.285-7.593 3.797-7.593 8.068A8.06 8.06 0 0 0 28 37.966c4.461 0 8.068-3.607 8.068-8.068 0-3.132-1.709-5.885-4.461-7.213-.19-.095-.285-.38-.19-.664.095-.19.38-.285.664-.19 3.132 1.519 5.031 4.651 5.031 8.068 0 4.936-4.081 9.017-9.017 9.017s-9.016-4.081-9.016-9.017c0-4.841 3.797-8.732 8.542-9.017v-4.746c-7.403.285-13.288 6.359-13.288 13.763 0 7.593 6.169 13.763 13.763 13.763s13.763-6.169 13.763-13.763c0-5.79-3.702-11.01-9.207-13.003-.285-.095-.38-.38-.285-.57s.38-.38.569-.285c5.885 2.088 9.776 7.688 9.776 13.858 0 8.068-6.644 14.712-14.712 14.712s-14.712-6.644-14.712-14.712c0-7.973 6.359-14.427 14.237-14.712v-4.746c-10.536.285-18.983 8.922-18.983 19.458 0 10.725 8.732 19.458 19.458 19.458s19.458-8.732 19.458-19.458c0-5.315-2.088-10.251-5.885-13.858-.19-.19-.19-.475 0-.664a.46.46 0 0 1 .664 0c3.986 3.892 6.17 9.017 6.17 14.617A20.38 20.38 0 0 1 28 50.4 20.38 20.38 0 0 1 7.593 29.993c0-11.105 8.922-20.122 19.932-20.407V4.841c-13.668.285-24.678 11.39-24.678 25.153 0 13.858 11.295 25.152 25.153 25.152s25.153-11.295 25.153-25.152a25.23 25.23 0 0 0-5.6-15.851c-.19-.19-.095-.475.095-.664s.475-.095.664.095c3.797 4.651 5.79 10.441 5.79 16.42C54.102 44.325 42.427 56 28 56z" fill="currentColor"></path>
                                                </svg>

                                                <h3 class="fs-lg fw-normal">Impact Accelerator</h3>
                                                <div class="d-flex align-items-center pb-1 pb-xl-0 mb-2 mb-xl-3">
                                                    <div class="h1 mb-0">₦<span class="plan-price">2,490</span></div>
                                                    <div class="fs-sm ms-2">/ month</div>
                                                </div>
                                                <p class="fs-sm mb-xl-4">Our most popular package - serious reach with meaningful social impact.</p>
                                                <button type="button" class="btn btn-lg btn-primary w-100 select-plan">Boost My Impact</button>
                                                <div class="h6 fs-sm pt-4 mt-lg-1 mt-xl-2">Everything in Community Starter +</div>
                                                <ul class="list-unstyled gap-md-3 fs-sm text-dark-emphasis mb-0">
                                                    <li class="d-flex">
                                                        <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                        14-Day Priority Placement: Double the exposure
                                                    </li>
                                                    <li class="d-flex">
                                                        <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                        Impact Badge: Shows your social commitment
                                                    </li>
                                                    <li class="d-flex">
                                                        <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                        Performance Dashboard: Track your campaign's reach
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="position-absolute top-0 start-0 w-100 z-1 fs-sm fw-semibold text-white text-center" style="margin-top: -27px">MOST IMPACT</div>
                                        <div class="position-absolute top-0 start-0 bg-primary rounded-5 ms-n1" style="width: calc(100% + 8px); height: calc(100% + 36px); margin-top: -32px"></div>
                                    </div>
                                </div>

                                <!-- Premium Plan -->
                                <div class="col-12 col-md-4">
                                    <div class="position-relative h-100">
                                        <div class="card position-relative h-100 z-1 bg-body-tertiary border-0 rounded-5 p-3 plan-card" data-plan="changemaker" data-price="3700">
                                            <div class="card-body p-2 p-xl-3">
                                                <svg class="d-block mb-3 mb-xl-4" xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none">
                                                    <g class="text-primary" fill="currentColor">
                                                        <path d="M49.812 40.352v5.69h-11.38s-.664.664-1.897 1.422V24.23l3.793.948c1.043 0 1.897.854 1.897 1.897v7.587l1.897.948c1.897.948 5.69 2.655 5.69 4.742zM19.465 24.23v23.235c-1.233-.759-1.897-1.422-1.897-1.422H6.188v-5.69c0-2.086 3.793-3.793 5.69-4.742l1.897-.948v-7.587c0-1.043.853-1.897 1.897-1.897l3.793-.948z"></path>
                                                        <path d="M30.845 16.644A2.79 2.79 0 0 0 28 13.799c-1.517 0-2.845 1.233-2.845 2.845s1.328 2.845 2.845 2.845a2.79 2.79 0 0 0 2.845-2.845zm-7.492-11.38s1.802.948 4.647.948 4.647-.948 4.647-.948c3.888 4.647 3.888 10.432 3.888 10.432v8.535 23.235c-1.707 1.043-4.647 2.371-8.535 2.371s-6.828-1.328-8.535-2.371V24.23v-8.535s0-5.785 3.888-10.432z"></path>
                                                    </g>
                                                    <path class="text-secondary-emphasis" d="M44.122 56c-.285 0-.474-.19-.474-.474v-5.69c0-.285.19-.474.474-.474s.474.19.474.474v5.69c0 .285-.19.474-.474.474zm-32.244 0c-.284 0-.474-.19-.474-.474v-5.69c0-.285.19-.474.474-.474s.474.19.474.474v5.69c0 .285-.19.474-.474.474zm28.45-1.897c-.285 0-.474-.19-.474-.474v-3.793c0-.285.19-.474.474-.474s.474.19.474.474v3.793c0 .285-.19.474-.474.474zm-24.657 0c-.284 0-.474-.19-.474-.474v-3.793c0-.285.19-.474.474-.474s.474.19.474.474v3.793c0 .285-.19.474-.474.474zm32.244-1.897c-.285 0-.474-.19-.474-.474v-1.897c0-.285.19-.474.474-.474s.474.19.474.474v1.897c0 .285-.19.474-.474.474zm-39.831 0c-.285 0-.474-.19-.474-.474v-1.897c0-.285.19-.474.474-.474s.474.19.474.474v1.897c0 .285-.19.474-.474.474zM28 50.31c-6.069 0-9.863-3.129-10.622-3.793H6.188c-.285 0-.474-.19-.474-.474v-5.69c0-2.276 3.319-3.888 5.5-4.931l2.086-1.043v-7.302c0-1.328 1.043-2.371 2.371-2.371.284 0 .474.19.474.474s-.19.474-.474.474a1.46 1.46 0 0 0-1.422 1.422v7.587c0 .19-.095.379-.285.379l-2.371 1.138c-1.707.853-4.931 2.466-4.931 4.078v5.216h10.906c.095 0 .284.095.379.095 0 0 3.793 3.699 10.052 3.699s10.052-3.604 10.052-3.699c.095-.095.19-.095.379-.095h10.906v-5.121c0-1.612-3.224-3.224-4.931-4.078l-2.371-1.138c-.19-.095-.285-.285-.285-.379V27.17a1.46 1.46 0 0 0-1.423-1.423c-.285 0-.474-.19-.474-.474s.19-.474.474-.474c1.328 0 2.371 1.043 2.371 2.371v7.302l2.086 1.043c2.181 1.043 5.5 2.75 5.5 4.931v5.69c0 .285-.19.474-.474.474h-11.19C37.863 47.18 34.07 50.31 28 50.31zm0-3.793c-.285 0-.474-.19-.474-.474v-17.07c0-.285.19-.474.474-.474s.474.19.474.474v17.07c0 .285-.19.474-.474.474zm8.535-3.793c-.285 0-.474-.19-.474-.474V15.695c0-.095-.095-5.5-3.509-9.863-.664.285-2.276.854-4.552.854s-3.888-.569-4.552-.854c-3.509 4.362-3.509 9.768-3.509 9.863v26.554c0 .284-.19.474-.474.474s-.474-.19-.474-.474V15.695c0-.284.095-6.923 4.837-11.759L27.621.142c.19-.19.474-.19.664 0l3.793 3.793c4.837 4.837 4.837 11.475 4.837 11.759v26.554c.095.284-.095.474-.379.474zM24.112 5.074c.759.285 2.086.664 3.888.664s3.13-.379 3.888-.664c-.095-.19-.285-.285-.379-.379L28.095 1.28l-3.414 3.414c-.285.095-.379.285-.569.379zM28 19.963c-1.802 0-3.319-1.517-3.319-3.319s1.517-3.319 3.319-3.319 3.319 1.517 3.319 3.319-1.517 3.319-3.319 3.319zm0-5.69c-1.328 0-2.371 1.043-2.371 2.371s1.043 2.371 2.371 2.371 2.371-1.043 2.371-2.371-1.043-2.371-2.371-2.371z" fill="currentColor"></path>
                                                </svg>

                                                <h3 class="fs-lg fw-normal">Changemaker Suite</h3>

                                                <div class="d-flex align-items-center pb-1 pb-xl-0 mb-2 mb-xl-3">
                                                    <div class="h1 mb-0">₦<span class="plan-price">3,700</span></div>
                                                    <div class="fs-sm ms-2">/ month</div>
                                                </div>

                                                <p class="fs-sm mb-xl-4">Maximum visibility with premium benefits and greater social contribution.</p>

                                                <button type="button" class="btn btn-lg btn-outline-primary w-100 select-plan">Become a Changemaker</button>

                                                <div class="h6 fs-sm pt-4 mt-lg-1 mt-xl-2">All Impact Accelerator features +</div>
                                                <ul class="list-unstyled gap-md-3 fs-sm text-dark-emphasis mb-0">
                                                    <li class="d-flex">
                                                        <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                        28-Day Premium Placement: Extended maximum visibility
                                                    </li>

                                                    <li class="d-flex">
                                                        <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                        Gold Supporter Badge: Premium recognition
                                                    </li>

                                                    <li class="d-flex">
                                                        <i class="ci-check fs-base text-body-secondary me-2" style="margin-top: 3px"></i>
                                                        Impact Certificate: Document your contribution
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Premium Add-ons -->
                <h2 class="pt-5 mt-n3 mt-sm-n2 mt-lg-1 mb-xl-4">Amplification Boosters</h2>

                <!-- Certification -->
                <div class="border-bottom py-4">
                    <div class="row py-md-1 py-lg-2 py-xl-3 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input addon-switch" role="switch" id="certify" data-addon="certify" data-price="435">
                                <label for="certify" class="form-check-label h6 fs-6 ms-md-2 mb-0">
                                    <span class="d-flex align-items-center">
                                        <span class="badge bg-success me-2">POPULAR</span>
                                        Trust Verification
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="col-8 col-md-5">
                            <p class="fs-sm mb-0"><strong>10x more engagement:</strong> "Verified for Impact" badge with expert review.</p>
                        </div>

                        <div class="col-4 col-md-2">
                            <div class="h5 text-end text-nowrap mb-0">₦<span class="addon-price">435</span></div>
                        </div>
                    </div>
                </div>

                <!-- Daily Boosts -->
                <div class="border-bottom py-4">
                    <div class="row py-md-1 py-lg-2 py-xl-3 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input addon-switch" role="switch" id="lifts" data-addon="lifts" data-price="529">
                                <label for="lifts" class="form-check-label h6 fs-6 ms-md-2 mb-0">
                                    <span class="d-flex align-items-center">
                                        <span class="badge bg-warning text-dark me-2">BEST VALUE</span>
                                        Daily Visibility Boosts
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="col-8 col-md-5">
                            <p class="fs-sm mb-0"><strong>70 top placements:</strong> Daily pushes to prime visibility spots.</p>
                        </div>

                        <div class="col-4 col-md-2">
                            <div class="h5 text-end text-nowrap mb-0">₦<span class="addon-price">529</span></div>
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="border-bottom py-4">
                    <div class="row py-md-1 py-lg-2 py-xl-3 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input addon-switch" role="switch" id="analytics" data-addon="analytics" data-price="615">
                                <label for="analytics" class="form-check-label h6 fs-6 ms-md-2 mb-0">Impact Analytics Suite</label>
                            </div>
                        </div>

                        <div class="col-8 col-md-5">
                            <p class="fs-sm mb-0"><strong>Data-driven decisions:</strong> Track views, shares, and demographic impact.</p>
                        </div>

                        <div class="col-4 col-md-2">
                            <div class="h5 text-end text-nowrap mb-0">₦<span class="addon-price">615</span></div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Campaign Promotion Fee Notice -->
            <div class="alert alert-secondary mt-4">
                Behind every ad you run is a story of community transformation. Your support fuels our nonprofit partners' work, and we'll regularly show you the chapters you're helping write through quarterly impact reports.
            </div>
        </div>
    </main>

    <!-- Prev / Next navigation (Footer) -->
    <footer class="sticky-bottom bg-body pb-3">
        <div class="progress rounded-0" role="progressbar" aria-label="Dark example" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
            <div class="progress-bar bg-dark d-none-dark" style="width: 100%"></div>
            <div class="progress-bar bg-light d-none d-block-dark" style="width: 100%"></div>
        </div>

        <div class="container d-flex align-items-center gap-3 pt-3">
            <div class="h5 mb-0">Total: ₦<span id="totalAmount">0.00</span></div>
            <button type="submit" class="btn btn-primary animate-slide-end ms-auto" @click="submit" :disabled="isLoading">
                <span v-if="isLoading" class="spinner spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span>
                {{ isLoading ? 'Processing...' : 'Submit and publish' }}
            </button>
        </div>
    </footer>
</template>
