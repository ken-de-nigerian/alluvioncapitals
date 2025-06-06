<script setup lang="ts">
    import { Head, usePage, useForm } from "@inertiajs/vue3";
    import { onMounted, watch } from "vue";
    import initPasswordToggle from "../../../Components/Js/PasswordToggle.js";
    import { route } from "ziggy-js";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import AdminProfileNavTabs from "../../../Components/Navigation/AdminProfileNavTabs.vue";

    const page = usePage();

    const props = defineProps({
        notificationSettings: Object,
    });

    // Separate form for sending test email
    const emailForm = useForm({
        email: '',
    });

    // Separate form for notification settings
    const notificationsForm = useForm({
        donation_received: props.notificationSettings?.donation_received ?? false,
        campaign_updated: props.notificationSettings?.campaign_updated ?? false,
        newsletter: props.notificationSettings?.newsletter ?? false,
        campaign_review: props.notificationSettings?.campaign_review ?? false,
        daily_summary: props.notificationSettings?.daily_summary ?? false,
    });

    const submit = () => {
        emailForm.post(route('admin.send.test.email'), {
            preserveScroll: true,
            onSuccess: () => {
                emailForm.reset('email');
            }
        });
    };

    const updateNotifications = () => {
        notificationsForm.post(route('admin.update.notifications'), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    onMounted(() => {
        initPasswordToggle();
    });

    watch(() => emailForm.email, () => {
        if (emailForm.errors.email) emailForm.clearErrors('email');
    });
</script>

<template>
    <div class="col-lg-9 pt-2 pt-xl-3">
        <Head :title="`${page.props.app.name} | Account - Email Settings`" />

        <h1 class="h2 pb-1 pb-sm-2 pb-md-3">Email Settings</h1>

        <!-- Nav tabs -->
        <AdminProfileNavTabs />

        <!-- Tabs content -->
        <div class="tab-content">
            <!-- Test Email tab -->
            <div class="tab-pane fade" :class="{ 'show active': page.component === 'Admin/Profile/TestEmail' }">
                <div class="card rounded mb-5">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-lg-12 position-relative">
                                    <div class="form-group">
                                        <label class="form-label fs-base" for="email">Send Test Email *</label>
                                        <div class="input-group mb-3">
                                            <input id="email" type="email" class="form-control form-control-lg" :class="{ 'is-invalid': emailForm.errors.email }" v-model="emailForm.email" autocomplete="email" @focus="emailForm.clearErrors('email')" placeholder="Enter recipient email address" />
                                            <div v-if="emailForm.errors.email" class="invalid-tooltip bg-transparent py-0">
                                                {{ emailForm.errors.email }}
                                            </div>
                                            <LoadingButton type="submit" :custom-classes="'btn btn-outline-dark rounded-start-0'" :processing="emailForm.processing">
                                                Send
                                            </LoadingButton>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="vstack gap-4 pt-sm-1">
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input" id="donation-received" v-model="notificationsForm.donation_received" @change="updateNotifications">
                        <label class="form-check-label ps-2" for="donation-received">
                            <span class="d-block h6 mb-2">Donation Received</span>
                            <span class="fs-sm">Notify me when someone donates to my campaign.</span>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input" id="campaign-updated" v-model="notificationsForm.campaign_updated" @change="updateNotifications">
                        <label class="form-check-label ps-2" for="campaign-updated">
                            <span class="d-block h6 mb-2">Campaign Updates</span>
                            <span class="fs-sm">Notify me about important updates or changes to my campaigns.</span>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input" id="newsletter" v-model="notificationsForm.newsletter" @change="updateNotifications">
                        <label class="form-check-label ps-2" for="newsletter">
                            <span class="d-block h6 mb-2">Newsletters</span>
                            <span class="fs-sm">Receive helpful tips and fundraising success stories via email.</span>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input" id="campaign-review" v-model="notificationsForm.campaign_review" @change="updateNotifications">
                        <label class="form-check-label ps-2" for="campaign-review">
                            <span class="d-block h6 mb-2">Campaign Reviews</span>
                            <span class="fs-sm">Get notified when someone leaves feedback or a review on your campaign.</span>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input" id="daily-summary" v-model="notificationsForm.daily_summary" @change="updateNotifications">
                        <label class="form-check-label ps-2" for="daily-summary">
                            <span class="d-block h6 mb-2">Daily Summary</span>
                            <span class="fs-sm">Receive a daily summary of your fundraising activity.</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
