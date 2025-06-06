<script setup lang="ts">
    import {Head, useForm, usePage} from "@inertiajs/vue3";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import {route} from "ziggy-js";

    const page = usePage();

    const props = defineProps({
        campaign: Object
    });

    const form = useForm({
        title: '',
        amount: '',
        description: '',
        physical_gift: false,
    });

    const submit = () => {
        form.post(route('admin.campaigns.rewards.store', { campaign: props.campaign.id }), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
            }
        });
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Create A Thank-You Gift`"/>

    <!-- Create Rewards -->
    <div class="col-lg-9 pt-2 pt-xl-3">
        <!-- Page title -->
        <h1 class="h2 pb-1 pb-sm-2 pb-md-3">Create a Thank-You Gift</h1>

        <!-- Reward form -->
        <form @submit.prevent="submit">
            <div class="row row-cols-1 row-cols-sm-2 g-4 mb-4">
                <!-- Reward Title -->
                <div class="col-md-6 position-relative">
                    <label for="title" class="form-label fs-base">Gift Name *</label>
                    <input id="title" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.title }" v-model="form.title" autocomplete="off" @focus="form.clearErrors('title')" placeholder="VIP Community Access">
                    <div v-if="form.errors.title" class="invalid-feedback">{{ form.errors.title }}</div>
                    <div class="form-text">What would you like to call this appreciation gift?</div>
                </div>

                <!-- Minimum Donation Amount -->
                <div class="col-md-6 position-relative">
                    <label for="amount" class="form-label fs-base">Minimum Donation *</label>
                    <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input id="amount" type="number" min="0" step="0.01" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.amount }" v-model="form.amount" autocomplete="off" @focus="form.clearErrors('amount')" placeholder="50.00">
                    </div>
                    <div v-if="form.errors.amount" class="invalid-feedback">{{ form.errors.amount }}</div>
                    <div class="form-text">The minimum donation to receive this gift</div>
                </div>
            </div>

            <!-- Reward Description -->
            <div class="col-md-12 position-relative pb-2 mb-3 mb-sm-4">
                <label for="description" class="form-label fs-base">Gift Details</label>
                <p class="small mb-3 mt-2">
                    Describe what supporters will receive as a token of your appreciation.
                    Be specific about benefits and how they'll be delivered.
                </p>
                <textarea class="form-control form-control-lg rounded-5" id="description" :class="{ 'is-invalid': form.errors.description }" v-model="form.description" autocomplete="off" @focus="form.clearErrors('description')" rows="6" placeholder="Example: Your name listed on our supporter wall"></textarea>
                <div v-if="form.errors.description" class="invalid-feedback">{{ form.errors.description }}</div>
            </div>

            <!-- Additional Options -->
            <div class="pb-2 mb-4">
                <div class="form-check fs-lg m-0">
                    <input type="checkbox" class="form-check-input" id="physical_gift" v-model="form.physical_gift">
                    <label for="physical_gift" class="form-check-label fs-base">This gift requires shipping</label>
                </div>
                <div class="form-text mt-1">Check if you'll need the supporter's mailing address</div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex gap-3">
                <LoadingButton type="submit" :custom-classes="'btn btn-lg btn-primary rounded-pill'" :processing="form.processing">
                    Save Thank-You Gift
                </LoadingButton>
            </div>
        </form>
    </div>
</template>
