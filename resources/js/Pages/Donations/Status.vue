<script setup lang="ts">
    import {Head, Link, usePage} from "@inertiajs/vue3";
    import { route } from "ziggy-js";
    import { onMounted } from "vue";
    import CampaignCard from "../../Components/CampaignComponents/CampaignCard.vue";

    const page = usePage();

    const props = defineProps({
        campaign: Object,
        donation: Object,
        relatedCampaigns: Object,
        showConfetti: Boolean,
    });

    // Load Confetti script dynamically
    const loadConfetti = () => {
        if (!props.showConfetti) return;

        const script = document.createElement("script");
        script.src = "https://run.confettipage.com/here.js";
        script.dataset.confetticode = "U2FsdGVkX1+uarLIXjpz8KMb3BuJCvqsgFESX2e381w55IXrc0wLxyUYYfdDZ/dz0lDQLIe03dQJSq7FhMqo+eYeW9ON/iu96G1Cz8NVARge2TwenuHuiDV56HF6PEN6n8wRBgJ762kdDpq1llR4wf42tbiR6cMJLcKGrylbMhbFOnPC5NUcE4zz6UpCeqK9Bfuymx7DeEuMp805rQInZERpaWH9w2XrAzBYRRvq15fmZT/in/Yhovxzx31/HCVHlsdL7HNIFI7nWFdCNOl5POSLrkhJPmQap0dTK4zRrK5TLksdAkHHSZM27sMYv3D2YYfzJP5jfVfG1copCn7X6ASkhi8uOy0lP5SEJgcJjEoy6qyq9Tvpbx3rPp0qk7x/lpx4oSuGQQbp+1bULhLyAatpoX3D7vumLP7LQP/cRvPSoANakCndAUfezcOBZ6a8ObC/DaWLs+oEWuJ27izlDE2yo2lGguJ6yNNOU67oaxT9SwZ3sbe0b5Eyqm4088BF8aO+fIVI0r5xobmJb0Q7lA+MuBCCNr35yAkXLPkStCOE5d3xNEL8FdEhcyx8UXcHSIsJ9s3cYerTYX6X7UaYU4ZRk/7psX0Ot2HcEJ15JszX1MRW9pv8fReOPYMI7uQK8nR6BPfP+8EtM842Dt0VgKlXFs1t4WWSyBKVSlFe7xIPcapq2btGAyrczrlmjRo+krNHocWepacDRjUsK9qVfdaQVmzQetmYbCzdEuqqM8xpZzzQUSch8x1u/48GxQY3+2+X8i6wzvn5mfCZTy56W7vjvK6K1zkerL6fG+akwvmdZefg2nSKia/JcxLAF4Pl";
        document.body.appendChild(script);
    };

    // Hide ConfettiPage links
    const hideConfettiLinks = () => {
        document.querySelectorAll('a[href*="confettipage.com"]').forEach(link => {
            link.style.display = 'none';
        });

        const fixedLink = document.querySelector('a[href="https://confettipage.com"][style*="position: fixed"]');
        if (fixedLink) fixedLink.style.display = 'none';
    };

    onMounted(() => {
        if (props.showConfetti) {
            loadConfetti();
        }
        hideConfettiLinks();
    });

    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        const day = date.getDate();
        const suffix = ['st', 'nd', 'rd'][day % 10] || 'th';
        return date.toLocaleDateString('en-US', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        }).replace(/(\d+)/, `$1${suffix}`);
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Donation Successful`" />

    <div class="row row-cols-1 row-cols-lg-2 g-0 mx-auto" style="max-width: 1920px">
        <!-- Thank you content column -->
        <div class="col d-flex flex-column justify-content-center py-5 px-xl-4 px-xxl-5">
            <div class="w-100 pt-sm-2 pt-md-3 pt-lg-4 pb-lg-4 pb-xl-5 px-3 px-sm-4 pe-lg-0 ps-lg-5 mx-auto ms-lg-auto me-lg-4" style="max-width: 740px">
                <div class="d-flex align-items-sm-center border-bottom pb-4 pb-md-5">
                    <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle flex-shrink-0" style="width: 3rem; height: 3rem; margin-top: -.125rem">
                        <i class="ci-check fs-4"></i>
                    </div>

                    <div class="w-100 ps-3">
                        <div class="fs-sm mb-1">Reference: {{ donation.transaction_reference }}</div>
                        <div class="d-sm-flex align-items-center">
                            <h1 class="h4 mb-0 me-3">Thank you for donating!</h1>
                            <div class="nav mt-2 mt-sm-0 ms-auto">
                                <Link class="nav-link text-decoration-underline p-0" :href="route('campaigns.index')">Back to campaigns</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column gap-4 pt-3 pb-5 mt-3">
                    <div>
                        <h3 class="h6 mb-2">Donor</h3>
                        <p class="fs-sm mb-0">{{ donation.first_name }} {{ donation.last_name }}</p>
                    </div>

                    <div>
                        <h3 class="h6 mb-2">Date</h3>
                        <p class="fs-sm mb-0">{{ formatDate(donation.created_at) }}</p>
                    </div>

                    <div>
                        <h3 class="h6 mb-2">Campaign</h3>
                        <p class="fs-sm mb-0">{{ campaign.title }}</p>
                    </div>
                </div>

                <div class="bg-success rounded px-4 py-4" style="--cz-bg-opacity: .2">
                    <div class="py-3">
                        <h2 class="h4 text-center pb-2 mb-1">🎉 You're amazing!</h2>
                        <p class="fs-sm text-center mb-4">
                            Thank you so much for your generous donation! Because of kind people like you, we're making progress.
                            You've helped us get closer to our goal - we appreciate!
                        </p>

                        <!-- Share button -->
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target=".share-modal" aria-label="Share Campaign" :data-url="campaign.show_route" :data-title="campaign.title">
                                <i class="ci-share-2 fs-base me-2"></i> Spread the love
                            </button>
                        </div>
                    </div>
                </div>
                <p class="fs-sm pt-4 pt-md-5 mt-2 mt-sm-3 mt-md-0 mb-0">Need help?<a class="fw-medium ms-2" :href="'mailto:site@email.com'">Contact us</a></p>
            </div>
        </div>

        <!-- Related campaigns -->
        <div class="col pt-sm-3 p-md-5 ps-lg-5 py-lg-4 pe-lg-4 p-xxl-5">
            <div class="position-relative d-flex align-items-center h-100 py-5 px-3 px-sm-4 px-xl-5">
                <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-tertiary rounded-5 d-none d-md-block"></span>
                <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-tertiary d-md-none"></span>
                <div class="position-relative w-100 z-2 mx-auto pb-2 pb-sm-3 pb-md-0" style="max-width: 636px">

                    <h2 class="h4 text-center pb-3">More causes you might love</h2>

                    <div class="row row-cols-2 g-3 g-sm-4 mb-4">
                        <!-- Campaign -->
                        <div v-for="campaign in props.relatedCampaigns" class="col">
                            <CampaignCard :campaign="campaign" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    .cp-dwp--popup{
        display: none !important;
    }
    a[href="https://confettipage.com"][target="_blank"] {
        display: none !important;
    }
</style>
