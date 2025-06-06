<script setup lang="ts">
    import { Head, usePage, useForm } from "@inertiajs/vue3";
    import { onMounted } from "vue";
    import initPasswordToggle from "../../../Components/Js/PasswordToggle.js";
    import { route } from "ziggy-js";
    import UserProfileNavTabs from "../../../Components/Navigation/UserProfileNavTabs.vue";

    const page = usePage();

    const props = defineProps({
        notificationSettings: Object,
    });

    // Separate form for notification settings
    const notificationsForm = useForm({
        donation_received: props.notificationSettings?.donation_received ?? false,
        campaign_updated: props.notificationSettings?.campaign_updated ?? false,
        newsletter: props.notificationSettings?.newsletter ?? false,
        campaign_review: props.notificationSettings?.campaign_review ?? false,
        daily_summary: props.notificationSettings?.daily_summary ?? false,
    });

    const updateNotifications = () => {
        notificationsForm.post(route('user.update.notifications'), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    onMounted(() => {
        initPasswordToggle();
    });
</script>

<template>
    <div class="col-lg-9 pt-2 pt-xl-3">
        <Head :title="`${page.props.app.name} | Account - Notification Settings`" />

        <h1 class="h2 pb-1 pb-sm-2 pb-md-3">Notification Settings</h1>

        <!-- Nav tabs -->
        <UserProfileNavTabs />

        <!-- Tabs content -->
        <div class="tab-content">
            <!-- Notification Settings tab -->
            <div class="tab-pane fade" :class="{ 'show active': page.component === 'User/Profile/NotificationSettings' }">
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
