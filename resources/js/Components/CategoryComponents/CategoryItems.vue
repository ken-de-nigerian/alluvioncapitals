<script setup lang="ts">
    import {Link} from "@inertiajs/vue3";
    import {route} from "ziggy-js";
    import CategorySkeleton from "../../Components/CategoryComponents/CategorySkeleton.vue";

    const props = defineProps({
        categories: Object,
    })
</script>

<template>
    <div class="container mb-2 mb-sm-3 mb-lg-4 mb-xl-5 pt-5">
        <!-- Loading state -->
        <template v-if="!props.categories">
            <!-- Category skeleton -->
            <CategorySkeleton />
        </template>

        <!-- Loaded state -->
        <template v-else>
            <!-- Categories -->
            <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-4 g-lg-3 g-xl-4 pb-2 mb-4">
                <div v-for="category in props.categories" :key="category.id" class="col">
                    <Link class="vstack position-relative animate-underline hover-effect-scale rounded-4 overflow-hidden text-dark-emphasis fw-medium text-decoration-none" :href="route('categories.show', category.slug)">
                        <div class="ratio z-2 overflow-hidden" style="--cz-aspect-ratio: calc(130 / 196 * 100%)">
                            <img :src="category.image" class="hover-effect-target" alt="Image" loading="lazy">
                        </div>

                        <div class="position-relative z-2 text-center py-3">
                            <div class="animate-target d-inline">{{ category.name }}</div>
                        </div>

                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none-dark"></span>
                        <span class="position-absolute top-0 start-0 w-100 h-100 bg-white d-none d-block-dark" style="opacity: .07"></span>
                    </Link>
                </div>
            </div>
        </template>
    </div>
</template>
