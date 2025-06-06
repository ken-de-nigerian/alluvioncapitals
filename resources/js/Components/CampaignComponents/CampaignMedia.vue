<script setup lang="ts">
    import {computed} from 'vue';

    import CampaignVideoDisplay from "../../Components/CampaignComponents/CampaignVideoDisplay.vue";
    import CampaignImageDisplay from "../../Components/CampaignComponents/CampaignImageDisplay.vue";

    const props = defineProps({
        campaign: {
            type: Object,
            required: true,
        },
        images: {
            type: Array,
            default: null,
        }
    });

    // Compute an image array, defaulting to campaign.campaign_images or empty array
    const images = computed(() => {
        return props.images ?? (props.campaign.campaign_images ? JSON.parse(props.campaign.campaign_images) : []);
    });
</script>

<template>
    <div>
        <template v-if="campaign.campaign_video !== null">
            <!-- Image display -->
            <CampaignVideoDisplay  :campaign="campaign" />
        </template>

        <template v-else>
            <!-- Image display -->
            <CampaignImageDisplay :campaign="campaign" :images="images" />
        </template>
    </div>
</template>
