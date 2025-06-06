<script setup lang="ts">
    import { Head, usePage, useForm, Link } from "@inertiajs/vue3";
    import LoadingButton from "../../../Components/Button/LoadingButton.vue";
    import { route } from "ziggy-js";
    import { defineAsyncComponent } from 'vue';

    // Dynamically import heavy components
    const DatePicker = defineAsyncComponent(() => import("../../../Components/Forms/DatePicker.vue"));
    const QuillEditor = defineAsyncComponent(() =>
        import("@vueup/vue-quill").then(module => {
            // Import CSS only when QuillEditor is loaded
            import("@vueup/vue-quill/dist/vue-quill.snow.css");
            return module.QuillEditor;
        })
    );

    const page = usePage();

    // Helper function for timezone-safe date handling
    const getLocalDate = () => {
        const now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), now.getDate());
    };

    const props = defineProps<{
        categories: object;
        old?: {
            title?: string;
            category_id?: string;
            goal?: string;
            expires_at?: string;
            summary?: string;
        };
    }>();

    const form = useForm({
        title: props.old?.title || '',
        category_id: props.old?.category_id || '',
        goal: props.old?.goal || '',
        expires_at: props.old?.expires_at || new Date().toISOString().split('T')[0],
        summary: props.old?.summary || '',
    });

    const submit = () => {
        form.post(route('campaigns.add.store.details'), {
            preserveScroll: true,
        });
    };

    const deadlineConfig = {
        minDate: 'today',
        defaultDate: getLocalDate(),
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d',
        allowInput: true,
        clickOpens: true,
    };
</script>

<template>
    <Head :title="`${page.props.app.name} | Account - Campaigns: Add Campaign Details`" />

    <main class="content-wrapper">
        <div class="container pt-3 pt-sm-4 pt-md-5 pb-5" style="padding-bottom: 10rem !important;">
            <div class="row pt-lg-2 pt-xl-3 pb-1 pb-sm-2 pb-md-3 pb-lg-4 pb-xl-5">
                <aside class="col-lg-3 col-xl-4 mb-3" style="margin-top: -100px">
                    <div class="sticky-top overflow-y-auto" style="padding-top: 100px">
                        <ul class="nav flex-lg-column flex-nowrap gap-4 gap-lg-0 text-nowrap pb-2 pb-lg-0">
                            <li class="nat-item">
                                <a class="nav-link d-inline-flex px-0 px-lg-3 pe-none">
                                    <i class="ci-arrow-right-circle fs-lg me-2"></i> Campaign Details
                                </a>
                            </li>
                            <li class="nat-item"><a class="nav-link d-inline-flex px-0 px-lg-3 disabled"><i class="ci-arrow-down-circle fs-lg me-2"></i> Contact Info</a></li>
                            <li class="nat-item"><a class="nav-link d-inline-flex px-0 px-lg-3 disabled"><i class="ci-arrow-down-circle fs-lg me-2"></i> Photos and videos</a></li>
                            <li class="nat-item"><a class="nav-link d-inline-flex px-0 px-lg-3 disabled"><i class="ci-arrow-down-circle fs-lg me-2"></i> Supporting Documents</a></li>
                            <li class="nat-item"><a class="nav-link d-inline-flex px-0 px-lg-3 disabled"><i class="ci-arrow-down-circle fs-lg me-2"></i> Ad promotion</a></li>
                        </ul>
                    </div>
                </aside>

                <form class="col-lg-9 col-xl-8" @submit.prevent="submit">
                    <h1 class="h2 mb-n2 mb-lg-3">Campaign Details</h1>

                    <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 pb-3 pb-sm-4 mb-xl-2">
                        <div class="col">
                            <label for="title" class="form-label">Campaign title *</label>
                            <input id="title" type="text" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.title }" v-model="form.title" @focus="form.clearErrors('title')" placeholder="Enter campaign title" />
                            <div v-if="form.errors.title" class="invalid-feedback">{{ form.errors.title }}</div>
                        </div>

                        <div class="col">
                            <label for="category_id" class="form-label">Category *</label>
                            <select id="category_id" class="form-select form-select-lg" :class="{ 'is-invalid': form.errors.category_id }" v-model="form.category_id" @focus="form.clearErrors('category_id')">
                                <option value="">Select Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                            <div v-if="form.errors.category_id" class="invalid-feedback">{{ form.errors.category_id }}</div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 pb-3 pb-sm-4 mb-xl-2">
                        <div class="col">
                            <label for="goal" class="form-label">Funding Goal *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">₦</span>
                                <input id="goal" type="number" class="form-control form-control-lg" :class="{ 'is-invalid': form.errors.goal }" v-model="form.goal" @focus="form.clearErrors('goal')" placeholder="0.00" />
                                <div v-if="form.errors.goal" class="invalid-feedback">{{ form.errors.goal }}</div>
                            </div>
                        </div>

                        <div class="col">
                            <label for="expires_at" class="form-label">Deadline *</label>
                            <DatePicker id="expires_at" v-model="form.expires_at" :config="deadlineConfig" :class="{ 'is-invalid': form.errors.expires_at }" @focus="form.clearErrors('expires_at')"/>
                            <div v-if="form.errors.expires_at" class="invalid-feedback">{{ form.errors.expires_at }}</div>
                        </div>
                    </div>

                    <h1 class="h2 pb-lg-2">Summary</h1>
                    <p class="small mb-3"><b>Note:</b> * Tell us about your campaign so that everyone has clear info and supports your cause.</p>

                    <div class="pb-4 mb-2">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <QuillEditor v-model:content="form.summary" contentType="html" toolbar="full" />
                                <p v-if="form.errors.summary" class="small mb-3 text-danger">{{ form.errors.summary }}</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="sticky-bottom bg-body pb-3">
        <div class="progress rounded-0" role="progressbar" aria-label="Dark example" aria-valuenow="14.29" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
            <div class="progress-bar bg-dark d-none-dark" style="width: 14.29%"></div>
            <div class="progress-bar bg-light d-none d-block-dark" style="width: 14.29%"></div>
        </div>

        <div class="container d-flex gap-3 pt-3">
            <template v-if="page.props.auth.user.role === 'admin'">
                <Link class="btn btn-outline-dark animate-slide-start" :href="route('admin.campaigns.index')">
                    <i class="ci-arrow-left animate-target fs-base ms-n1 me-2"></i> Back
                </Link>
            </template>

            <template v-else>
                <Link class="btn btn-outline-dark animate-slide-start" :href="route('user.campaigns.index')">
                    <i class="ci-arrow-left animate-target fs-base ms-n1 me-2"></i> Back
                </Link>
            </template>

            <LoadingButton type="button" :custom-classes="'btn btn-dark animate-slide-end ms-auto'" :processing="form.processing" @click="submit">
                Next <i class="ci-arrow-right animate-target fs-base ms-2 me-n1 align-middle"></i>
            </LoadingButton>
        </div>
    </footer>
</template>
