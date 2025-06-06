<script setup lang="ts">
    import {Link} from "@inertiajs/vue3";
    import {route} from "ziggy-js";
</script>

<template>
    <footer class="footer bg-dark pb-4" data-bs-theme="dark">
        <div class="container pb-md-3">

            <!-- Fundraising Features -->
            <div class="border-bottom py-5">
                <div class="container">
                    <div class="row row-cols-2 row-cols-md-4 g-4 gx-sm-5 py-sm-1 py-md-2 py-lg-3 mb-n2 mb-md-0">
                        <!-- Feature 1: Quick Donations -->
                        <div class="col mb-2 mb-md-0">
                            <i class="ci-credit-card fs-2 text-primary mb-3"></i>
                            <h6 class="pb-2 mb-1">Quick Donations</h6>
                            <p class="fs-sm text-body mb-0">Give in just a few clicks with our streamlined donation process.</p>
                        </div>

                        <!-- Feature 2: Secure Payments -->
                        <div class="col mb-2 mb-md-0">
                            <i class="ci-check-shield fs-2 text-primary mb-3"></i>
                            <h6 class="pb-2 mb-1">Secure Payments</h6>
                            <p class="fs-sm text-body mb-0">Bank-level security protects every transaction you make.</p>
                        </div>

                        <!-- Feature 3: Impact Tracking -->
                        <div class="col mb-2 mb-md-0">
                            <i class="ci-bar-chart-2 fs-2 text-primary mb-3"></i>
                            <h6 class="pb-2 mb-1">Track Your Impact</h6>
                            <p class="fs-sm text-body mb-0">See exactly how your donations are making a difference.</p>
                        </div>

                        <!-- Feature 4: Tax Receipts -->
                        <div class="col mb-2 mb-md-0">
                            <i class="ci-file-text fs-2 text-primary mb-3"></i>
                            <h6 class="pb-2 mb-1">Instant Tax Receipts</h6>
                            <p class="fs-sm text-body mb-0">Get donation receipts immediately for tax purposes.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription + Links -->
            <div class="py-5">
                <div class="row py-sm-1 py-md-2 py-lg-3">

                    <!-- Subscription + Social buttons -->
                    <div class="col-lg-5 mb-4 mb-sm-5 mb-lg-0">
                        <div class="mb-4">
                            <h6 class="mb-3">Stay updated on our causes 💙</h6>
                            <p class="fs-sm text-muted mb-3">Subscribe to receive impact stories and campaign updates</p>
                            <form class="needs-validation d-flex gap-2 pb-sm-2 pb-lg-0" novalidate>
                                <input type="email" class="form-control form-control-lg w-100 rounded-pill" placeholder="Your email address" style="max-width: 340px" required>
                                <button type="submit" class="btn btn-lg btn-primary rounded-pill px-4">
                                    Join
                                </button>
                            </form>
                            <div class="form-text mt-2">We respect your privacy. Unsubscribe anytime.</div>
                        </div>

                        <div class="d-flex gap-3">
                            <Link class="btn btn-icon btn-secondary rounded-circle" href="/" aria-label="Instagram">
                                <i class="ci-instagram fs-base"></i>
                            </Link>

                            <Link class="btn btn-icon btn-secondary rounded-circle" href="/" aria-label="Facebook">
                                <i class="ci-facebook fs-base"></i>
                            </Link>

                            <Link class="btn btn-icon btn-secondary rounded-circle" href="/" aria-label="YouTube">
                                <i class="ci-youtube fs-base"></i>
                            </Link>

                            <Link class="btn btn-icon btn-secondary rounded-circle" href="/" aria-label="Telegram">
                                <i class="ci-telegram fs-base"></i>
                            </Link>
                        </div>
                    </div>

                    <!-- Columns with links that are turned into accordion on screens < 500px wide (sm breakpoint) -->
                    <div class="col-lg-7">
                        <div class="accordion ps-lg-4" id="footerLinks">
                            <div class="row">
                                <div class="accordion-item col-sm-4 border-0">
                                    <h6 class="accordion-header" id="campaignsHeading">
                                        <span class="text-dark-emphasis d-none d-sm-block">Campaigns</span>
                                        <button type="button" class="accordion-button collapsed py-3 d-sm-none" data-bs-toggle="collapse" data-bs-target="#campaignsLinks" aria-expanded="false" aria-controls="campaignsLinks">Campaigns</button>
                                    </h6>

                                    <div class="accordion-collapse collapse d-sm-block" id="campaignsLinks" aria-labelledby="campaignsHeading" data-bs-parent="#footerLinks">
                                        <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" :href="route('campaigns.index', { tab: 'all' })" :class="{ 'active': $page.url.includes('tab=all') || !$page.url.includes('tab=') }">
                                                    All
                                                </Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" :href="route('campaigns.index', { tab: 'featured' })" :class="{ 'active': $page.url.includes('tab=featured') }">
                                                    Featured
                                                </Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" :href="route('campaigns.index', { tab: 'latest' })" :class="{ 'active': $page.url.includes('tab=latest') }">
                                                    Latest
                                                </Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" :href="route('campaigns.index', { tab: 'popular' })" :class="{ 'active': $page.url.includes('tab=popular') }">
                                                    Popular
                                                </Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" :href="route('campaigns.index', { tab: 'ended' })" :class="{ 'active': $page.url.includes('tab=ended') }">
                                                    Ended
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                    <hr class="d-sm-none my-0">
                                </div>

                                <div class="accordion-item col-sm-4 border-0">
                                    <h6 class="accordion-header" id="membersHeading">
                                        <span class="text-dark-emphasis d-none d-sm-block">Pages</span>
                                        <button type="button" class="accordion-button collapsed py-3 d-sm-none" data-bs-toggle="collapse" data-bs-target="#membersLinks" aria-expanded="false" aria-controls="membersLinks">Pages</button>
                                    </h6>

                                    <div class="accordion-collapse collapse d-sm-block" id="membersLinks" aria-labelledby="membersHeading" data-bs-parent="#footerLinks">
                                        <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" :href="route('categories.index')">Categories</Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">Pricing</Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">Charity campaigning</Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">About Us</Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">Sign up as a nonprofit</Link>
                                            </li>
                                        </ul>
                                    </div>
                                    <hr class="d-sm-none my-0">
                                </div>

                                <div class="accordion-item col-sm-4 border-0">
                                    <h6 class="accordion-header" id="supportHeading">
                                        <span class="text-dark-emphasis d-none d-sm-block">Support</span>
                                        <button type="button" class="accordion-button collapsed py-3 d-sm-none" data-bs-toggle="collapse" data-bs-target="#supportLinks" aria-expanded="false" aria-controls="supportLinks">Support</button>
                                    </h6>

                                    <div class="accordion-collapse collapse d-sm-block" id="supportLinks" aria-labelledby="supportHeading" data-bs-parent="#footerLinks">
                                        <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">FAQs</Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">How it works</Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">Search guide</Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">Contact</Link>
                                            </li>

                                            <li class="d-flex w-100 pt-1">
                                                <Link class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="/">Help Center</Link>
                                            </li>
                                        </ul>
                                    </div>
                                    <hr class="d-sm-none my-0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <p class="fs-xs text-body text-center text-lg-start mb-0">
                © All rights reserved. Made with <i class="ci-heart-filled align-middle"></i> by <span class="animate-underline"><a class="animate-target text-white text-decoration-none" href="/" target="_blank" rel="noreferrer">Nwaneri Kenneth</a></span>
            </p>
        </div>

        <!-- Additional spacing to accommodate the sticky offcanvas toggle button -->
        <div class="d-lg-none" style="height: 3.75rem"></div>
    </footer>
</template>
