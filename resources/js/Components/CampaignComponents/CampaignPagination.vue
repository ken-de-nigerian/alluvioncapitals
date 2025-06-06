<script>
    import { Link } from '@inertiajs/vue3';
    export default {
        name: 'Pagination',
        components: { Link },
        props: {
            campaigns: {
                type: Object,
                required: true
            }
        },
        computed: {
            prevLink() {
                return this.campaigns.links[0]; // First link is always previous
            },
            nextLink() {
                return this.campaigns.links[this.campaigns.links.length - 1]; // Last link is always next
            },
            visiblePages() {
                // Skip first (previous) and last (next) links
                const middleLinks = this.campaigns.links.slice(1, -1);

                return middleLinks.map(link => ({
                    label: link.label.replace(/<[^>]*>?/gm, ''),
                    url: link.url,
                    active: link.active
                }));
            },
            shouldShowEllipsis() {
                // Show ellipsis if there are more than 5 pages (3 visible + ellipsis + last)
                return this.visiblePages.length > 5;
            },
            displayedPages() {
                if (!this.shouldShowEllipsis) return this.visiblePages;

                const firstThree = this.visiblePages.slice(0, 3);
                const lastPage = this.visiblePages[this.visiblePages.length - 1];

                return [...firstThree, {label: '...', url: null}, lastPage];
            }
        }
    };
</script>

<template>
    <nav class="pt-4 mt-2 mt-sm-3" aria-label="Pagination">
        <ul class="pagination pagination-lg">
            <li class="page-item me-auto" :class="{ 'disabled': !prevLink.url }">
                <Link
                    v-if="prevLink.url"
                    class="page-link d-flex align-items-center h-100 fs-lg px-2"
                    :href="prevLink.url"
                    aria-label="Previous page"
                    preserve-scroll
                >
                    <i class="ci-chevron-left mx-1"></i>
                </Link>
                <span v-else class="page-link d-flex align-items-center h-100 fs-lg px-2">
                    <i class="ci-chevron-left mx-1"></i>
                </span>
            </li>

            <template v-for="(page, index) in displayedPages">
                <li v-if="page.label === '...'" class="page-item" :key="`ellipsis-${index}`">
                    <span class="page-link pe-none">...</span>
                </li>
                <li v-else class="page-item" :class="{ 'active': page.active }" :key="index"
                    :aria-current="page.active ? 'page' : null">
                    <Link
                        v-if="page.url"
                        class="page-link"
                        :href="page.url"
                        preserve-scroll
                    >
                        {{ page.label }}
                        <span v-if="page.active" class="visually-hidden">(current)</span>
                    </Link>
                    <span v-else class="page-link">
                        {{ page.label }}
                        <span v-if="page.active" class="visually-hidden">(current)</span>
                    </span>
                </li>
            </template>

            <li class="page-item ms-auto" :class="{ 'disabled': !nextLink.url }">
                <Link
                    v-if="nextLink.url"
                    class="page-link d-flex align-items-center h-100 fs-lg px-2"
                    :href="nextLink.url"
                    aria-label="Next page"
                    preserve-scroll
                >
                    <i class="ci-chevron-right mx-1"></i>
                </Link>
                <span v-else class="page-link d-flex align-items-center h-100 fs-lg px-2">
                    <i class="ci-chevron-right mx-1"></i>
                </span>
            </li>
        </ul>
    </nav>
</template>
