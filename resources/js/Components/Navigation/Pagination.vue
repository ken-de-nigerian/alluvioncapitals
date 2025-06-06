<!--suppress ALL -->
<template>
    <nav class="d-flex justify-content-between">
        <!-- Mobile Pagination (visible only on small screens) -->
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination mb-0">
                <li class="page-item" :class="{ disabled: !prevLink?.url }" aria-disabled="true">
                    <Link class="page-link" :href="prevLink?.url || '#'" v-html="prevLink?.label || '« Previous'" :preserve-scroll="true" />
                </li>
                <li class="page-item" :class="{ disabled: !nextLink?.url }">
                    <Link class="page-link" :href="nextLink?.url || '#'" v-html="nextLink?.label || 'Next »'" :preserve-scroll="true" />
                </li>
            </ul>
        </div>

        <!-- Desktop Pagination (visible on sm and up) -->
        <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between w-100">
            <div>
                <p class="small text-muted mb-0">
                    Showing
                    <span class="fw-semibold">{{ categories.from }}</span>
                    to
                    <span class="fw-semibold">{{ categories.to }}</span>
                    of
                    <span class="fw-semibold">{{ categories.total }}</span>
                    results
                </p>
            </div>

            <div>
                <ul class="pagination mb-0">
                    <li v-for="(link, index) in paginatedLinks" :key="index" class="page-item" :class="{ active: link.active, disabled: !link.url }" :aria-label="getAriaLabel(link)">
                        <Link v-if="link.url" class="page-link" :href="link.url" v-html="link.label" :preserve-scroll="true" :aria-hidden="false"/>
                        <span v-else class="page-link" v-html="link.label" :aria-hidden="true"/>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
    import { Link } from '@inertiajs/vue3';

    export default {
        name: 'Pagination',
        components: { Link },
        props: {
            categories: {
                type: Object,
                required: true
            }
        },
        computed: {
            prevLink() {
                return this.categories.links.find(link => link.label.toLowerCase().includes('previous'));
            },
            nextLink() {
                return this.categories.links.find(link => link.label.toLowerCase().includes('next'));
            },
            paginatedLinks() {
                const links = this.categories.links;
                const currentPage = this.categories.current_page;
                const totalPages = this.categories.last_page;
                const range = 2; // Number of pages to show on each side of the current page
                const paginated = [];

                // Always include Previous link
                const prev = links.find(link => link.label.toLowerCase().includes('previous'));
                if (prev) paginated.push(prev);

                // Calculate page numbers to display
                let start = Math.max(1, currentPage - range);
                let end = Math.min(totalPages, currentPage + range);

                // Adjust start and end to ensure we show enough pages
                if (end - start < range * 2) {
                    if (currentPage < totalPages / 2) {
                        end = Math.min(totalPages, end + (range * 2 - (end - start)));
                    } else {
                        start = Math.max(1, start - (range * 2 - (end - start)));
                    }
                }

                // Add first page
                if (start > 1) {
                    paginated.push(links.find(link => parseInt(link.label) === 1));
                }

                // Add ellipsis after first page if needed
                if (start > 2) {
                    paginated.push({ label: '...', url: null, active: false });
                }

                // Add pages in range
                for (let i = start; i <= end; i++) {
                    const link = links.find(link => parseInt(link.label) === i);
                    if (link) paginated.push(link);
                }

                // Add ellipsis before last page if needed
                if (end < totalPages - 1) {
                    paginated.push({ label: '...', url: null, active: false });
                }

                // Add last page
                if (end < totalPages) {
                    paginated.push(links.find(link => parseInt(link.label) === totalPages));
                }

                // Always include Next link
                const next = links.find(link => link.label.toLowerCase().includes('next'));
                if (next) paginated.push(next);

                return paginated;
            }
        },
        methods: {
            getAriaLabel(link) {
                if (link.label.toLowerCase().includes('previous')) return '« Previous';
                if (link.label.toLowerCase().includes('next')) return 'Next »';
                if (link.label === '...') return 'More pages';
                return `Page ${link.label.replace(/<[^>]*>?/gm, '')}`;
            }
        }
    };
</script>
