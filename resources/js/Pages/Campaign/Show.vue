<script setup lang="ts">
    import {Head, Link, usePage} from "@inertiajs/vue3";
    import { route } from "ziggy-js";
    import {ref} from "vue";

    import CampaignMedia from "../../Components/CampaignComponents/CampaignMedia.vue";
    import CampaignTabNavigation from "../../Components/CampaignComponents/CampaignTabNavigation.vue";
    import CampaignTitleShare from "../../Components/CampaignComponents/Campaign-Title-Share.vue";
    import CampaignMetaDetails from "../../Components/CampaignComponents/CampaignMetaDetails.vue";
    import DonationAmounts from "../../Components/DonationComponents/DonationAmounts.vue";
    import RewardsSection from "../../Components/CampaignComponents/RewardsSection.vue";
    import RelatedCampaigns from "../../Components/CampaignComponents/RelatedCampaigns.vue";
    import CampaignDescriptionTab from "../../Components/CampaignComponents/CampaignDescriptionTab.vue";
    import CampaignDonationsTab from "../../Components/CampaignComponents/CampaignDonationsTab.vue";
    import CampaignUpdatesTab from "../../Components/CampaignComponents/CampaignUpdatesTab.vue";
    import CampaignCommentsTab from "../../Components/CampaignComponents/CampaignCommentsTab.vue";

    const page = usePage();

    const props = defineProps({
        campaign: Object,
        relatedCampaigns: Object,
        rewards: Object,
        donations: Object,
        comments: Object,
        updates: Object,
        amounts: Array,
        currentTab: String
    });

    const truncate = (text: string, maxLength: number): string => {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    };

    const selectedRewardId = ref('');
    const selectedAmount = ref('');

    const handleRewardSelect = (reward: { id: string; amount: number }) => {
        selectedRewardId.value = reward.id;
        selectedAmount.value = reward.amount.toString();
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Campaign - ${campaign.title}`" />

    <!-- Campaign details section -->
    <section class="container pt-4 pb-5 mb-xxl-3">
        <!-- Breadcrumb -->
        <nav class="pb-2" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <Link :href="route('home')">Home</Link>
                </li>

                <li class="breadcrumb-item">
                    <Link :href="route('campaigns.index')">Campaigns</Link>
                </li>

                <li class="breadcrumb-item active" aria-current="page">{{ truncate(campaign.title, 25) }}</li>
            </ol>
        </nav>

        <!-- Title + Share button-->
        <Campaign-Title-Share :campaign="campaign" />

        <div class="row">
            <!-- Gallery (slider) + Description -->
            <div class="col-lg-8 pb-3 pb-sm-0 mb-4 mb-sm-5 mb-lg-0">
                <!-- Slider + Video Component -->
                <CampaignMedia :campaign="campaign" />

                <!-- Nav tabs -->
                <CampaignTabNavigation :campaign="campaign" :current-tab="currentTab || 'description'"/>

                <div class="pt-4 mt-sm-1 mt-md-3">
                    <!-- Campaign description -->
                    <CampaignDescriptionTab :campaign="campaign" />

                    <!-- Campaign donations -->
                    <CampaignDonationsTab :donations="donations" :campaign="campaign" />

                    <!-- Campaign updates -->
                    <CampaignUpdatesTab :updates="updates" :campaign="campaign" />

                    <!-- Campaign comments -->
                    <CampaignCommentsTab :comments="comments" :campaign="campaign" />
                </div>
            </div>

            <!-- Sidebar with campaign detail and owner info -->
            <aside class="col-lg-4">
                <div class="position-sticky top-0">
                    <!-- Campaign meta visible on screens > 991px (lg breakpoint) -->
                    <CampaignMetaDetails :campaign="campaign" />

                    <!-- Donate Section -->
                    <DonationAmounts :campaign="campaign" :amounts="amounts" :selected-reward-id="selectedRewardId" :selected-amount="selectedAmount" @update:selected-reward-id="selectedRewardId = $event" @update:selected-amount="selectedAmount = $event" />
                </div>
            </aside>
        </div>
    </section>

    <!-- Rewards Section -->
    <RewardsSection v-if="rewards?.length > 1" :rewards="rewards" @select-reward="handleRewardSelect" />

    <!-- Related campaigns -->
    <RelatedCampaigns :relatedCampaigns="relatedCampaigns" />
</template>
