<script setup lang="ts">
    import { usePage } from "@inertiajs/vue3";
    import { ref, computed } from "vue";

    const page = usePage();

    const props = defineProps({
        campaign: Object,
    });

    // Reactive state for toggling full content
    const showFullContent = ref(false);

    // Computed property to truncate HTML content to 50 words
    const truncatedSummary = computed(() => {
        if (showFullContent.value) return props.campaign.summary;

        // Convert HTML to text for word counting
        const div = document.createElement("div");
        div.innerHTML = props.campaign.summary || "";
        const text = div.textContent || div.innerText || "";
        const words = text.trim().split(/\s+/);

        if (words.length <= 50) return props.campaign.summary;

        // Truncate to 50 words
        const truncatedText = words.slice(0, 50).join(" ");
        let charCount = truncatedText.length;
        let resultHtml = "";
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = props.campaign.summary || "";

        // Traverse DOM nodes to reconstruct HTML up to charCount
        function traverseNodes(node: Node): boolean {
            if (charCount <= 0) return false;

            if (node.nodeType === Node.TEXT_NODE) {
                const text = node.textContent || "";
                if (charCount >= text.length) {
                    charCount -= text.length;
                    resultHtml += text;
                    return true;
                } else {
                    resultHtml += text.slice(0, charCount);
                    charCount = 0;
                    return false;
                }
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                const element = node as HTMLElement;
                resultHtml += `<${element.tagName.toLowerCase()}`;
                for (const attr of element.attributes) {
                    resultHtml += ` ${attr.name}="${attr.value}"`;
                }
                resultHtml += ">";
                for (const child of element.childNodes) {
                    if (!traverseNodes(child)) return false;
                }
                resultHtml += `</${element.tagName.toLowerCase()}>`;
                return true;
            }
            return true;
        }

        for (const child of tempDiv.childNodes) {
            if (!traverseNodes(child)) break;
        }

        return resultHtml + (words.length > 50 ? "..." : "");
    });
</script>

<template>
    <div :class="[$page.url.includes('tab=description') || !$page.url.includes('tab=') ? 'd-block' : 'd-none']">
        <h2 class="h3">Description</h2>

        <div class="content-container">
            <!-- Render HTML content safely -->
            <div v-html="truncatedSummary"></div>
            <!-- Show Read More/Read Less button if content exceeds 50 words -->
            <button v-if="(campaign.summary || '').replace(/<[^>]+>/g, '').trim().split(/\s+/).length > 50" class="btn btn-link text-light-emphasis p-0 mt-2" @click="showFullContent = !showFullContent">
                {{ showFullContent ? 'Read Less' : 'Read More' }}
            </button>
        </div>

        <div class="d-flex fs-sm text-body-secondary border-top pt-4 mt-4 mt-md-5">
            <div>Donations: <span class="text-dark-emphasis">{{ campaign.donations_count }}</span></div>
            <hr class="vr my-1 mx-3">
            <div>Duration: <span class="text-dark-emphasis">{{ campaign.days_left_text }}</span></div>
        </div>
    </div>
</template>
