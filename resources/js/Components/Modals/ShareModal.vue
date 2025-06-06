<script setup lang="ts">
    import { onMounted } from 'vue';

    onMounted(() => {
        const shareModal = document.querySelector('.share-modal');
        const embedTrigger = document.getElementById('embedTrigger');

        shareModal?.addEventListener('show.bs.modal', function (event) {
            const triggerButton = event.relatedTarget;
            const url = triggerButton.getAttribute('data-url');
            const title = triggerButton.getAttribute('data-title');
            const campaignId = url.split('/').pop();

            // Extract domain and protocol from the URL
            const urlObj = new URL(url);
            const baseUrl = `${urlObj.protocol}//${urlObj.host}`;

            // Encode components
            const encodedUrl = encodeURIComponent(url);
            const encodedTitle = encodeURIComponent(title);

            // Set share URLs (unchanged)
            document.getElementById('facebookShare')?.setAttribute('href', `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`);
            document.getElementById('twitterShare')?.setAttribute('href', `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`);
            document.getElementById('messengerShare')?.setAttribute('href', `fb-messenger://share/?link=${encodedUrl}&app_id=1174207874030765`);
            document.getElementById('whatsappShare')?.setAttribute('href', `whatsapp://send?text=${encodedTitle}%20${encodedUrl}`);
            document.getElementById('telegramShare')?.setAttribute('href', `https://telegram.me/share/url?url=${encodedUrl}&text=${encodedTitle}`);
            document.getElementById('emailShare')?.setAttribute('href', `mailto:?subject=${encodedTitle}&body=${encodedUrl}`);
            document.getElementById('smsShare')?.setAttribute('href', `sms://?body=${encodedTitle} ${url}`);
            const campaignUrlInput = document.getElementById('campaignURL');
            if (campaignUrlInput) campaignUrlInput.value = url;

            // Setup embed trigger
            if (embedTrigger) {
                embedTrigger.onclick = function () {
                    const embedUrlLarge = `${baseUrl}/widget/l/${campaignId}`;
                    const embedUrlSmall = `${baseUrl}/widget/s/${campaignId}`;

                    // Large widget
                    const embedIframeLarge = document.getElementById('embedIframeLarge');
                    const embedCodeLarge = document.getElementById('embedCodeLarge');
                    if (embedIframeLarge) embedIframeLarge.src = embedUrlLarge;
                    if (embedCodeLarge) embedCodeLarge.value = `<iframe src='${embedUrlLarge}' width='350' height='600'></iframe>`;

                    // Small widget
                    const embedIframeSmall = document.getElementById('embedIframeSmall');
                    const embedCodeSmall = document.getElementById('embedCodeSmall');
                    if (embedIframeSmall) embedIframeSmall.src = embedUrlSmall;
                    if (embedCodeSmall) embedCodeSmall.value = `<iframe src='${embedUrlSmall}' width='350' height='400'></iframe>`;
                };
            }
        });

        // Copy campaign URL
        document.getElementById('btn_copy_link')?.addEventListener('click', function () {
            const input = document.getElementById('campaignURL');
            if (!input) return;

            input.select();
            input.setSelectionRange(0, 99999);

            navigator.clipboard.writeText(input.value).then(() => {
                const originalText = this.innerText;
                this.innerHTML = '<i class="ci-check me-1"></i> Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });

        // Copy embed code
        document.querySelectorAll('[id^="btn_copy_code"]').forEach(button => {
            button.addEventListener('click', function () {
                const size = this.getAttribute('data-size');
                const input = document.getElementById(`embedCode${size}`);
                if (!input) return;

                input.select();
                input.setSelectionRange(0, 99999);

                navigator.clipboard.writeText(input.value).then(() => {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="ci-check me-1"></i> Copied!';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                });
            });
        });
    });
</script>

<template>
    <!-- Share Modal -->
    <div class="modal fade share-modal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-5" id="shareModalLabel">Share on</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="container">
                        <div class="row g-3">
                            <!-- Each share link has a unique ID -->
                            <div class="col-md-3 col-6">
                                <a id="facebookShare" target="_blank" class="social-share text-muted text-center d-block">
                                    <i class="ci-facebook display-4 text-info"></i>
                                    <span class="d-block mt-2">Facebook</span>
                                </a>
                            </div>

                            <div class="col-md-3 col-6">
                                <a id="twitterShare" target="_blank" class="social-share text-muted text-center d-block">
                                    <i class="ci-x display-4 text-info"></i>
                                    <span class="d-block mt-2">Twitter</span>
                                </a>
                            </div>

                            <div class="col-md-3 col-6">
                                <a id="messengerShare" class="social-share text-muted text-center d-block">
                                    <i class="ci-messenger display-4 text-info"></i>
                                    <span class="d-block mt-2">Messenger</span>
                                </a>
                            </div>

                            <div class="col-md-3 col-6">
                                <a id="whatsappShare" class="social-share text-muted text-center d-block">
                                    <i class="ci-whatsapp display-4 text-success"></i>
                                    <span class="d-block mt-2">WhatsApp</span>
                                </a>
                            </div>

                            <div class="col-md-3 col-6">
                                <a id="telegramShare" target="_blank" class="social-share text-muted text-center d-block">
                                    <i class="ci-telegram display-4 text-info"></i>
                                    <span class="d-block mt-2">Telegram</span>
                                </a>
                            </div>

                            <div class="col-md-3 col-6">
                                <a id="emailShare" class="social-share text-muted text-center d-block">
                                    <i class="ci-mail display-4 text-secondary"></i>
                                    <span class="d-block mt-2">Email</span>
                                </a>
                            </div>

                            <div class="col-md-3 col-6">
                                <a id="smsShare" class="social-share text-muted text-center d-block">
                                    <i class="ci-message-square display-4 text-warning"></i>
                                    <span class="d-block mt-2">Text message</span>
                                </a>
                            </div>

                            <div class="col-md-3 col-6">
                                <a id="embedTrigger" data-bs-toggle="modal" data-bs-target="#embedModal" href="#" title="Embed" class="social-share text-muted text-center d-block">
                                    <i class="ci-code display-4 text-secondary"></i>
                                    <span class="d-block mt-2">Embed</span>
                                </a>
                            </div>

                        </div>
                    </div>

                    <div class="row justify-content-center mt-4 mb-3">
                        <div class="col-md-10 text-center">
                            <label for="campaignURL" class="form-label">Copy Campaign URL</label>
                            <div class="input-group">
                                <input id="campaignURL" type="text" class="form-control" value="" readonly>
                                <button class="btn btn-primary ms-2" id="btn_copy_link">Copy Link</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Embed modal -->
    <div class="modal fade" id="embedModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white flex-column align-items-start pb-2">
                    <div class="d-flex justify-content-between w-100 align-items-center mb-2">
                        <h6 class="modal-title fs-5 mb-0" id="embedModalLabel">Website widget</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <p class="mb-0 small">Embed your campaign so others can donate directly on your website.</p>
                </div>

                <div class="modal-body text-center">
                    <h1 class="h4 pb-3">Choose your widget size</h1>

                    <!-- Nav pills -->
                    <div class="d-flex justify-content-center mb-4">
                        <ul class="nav nav-pills nav-fill gap-2" role="tablist" style="max-width: 400px;">
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link active" id="large-iframe-tab" data-bs-toggle="pill" data-bs-target="#large-iframe" role="tab" aria-controls="large-iframe" aria-selected="true">
                                    Large (350×600)
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="small-iframe-tab" data-bs-toggle="pill" data-bs-target="#small-iframe" type="button" role="tab" aria-controls="small-iframe" aria-selected="false">
                                    Small (350×400)
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <!-- Large iframe tab -->
                        <div class="tab-pane fade show active" id="large-iframe" role="tabpanel" aria-labelledby="large-iframe-tab">
                            <!-- Preview Widget -->
                            <div class="form-group">
                                <div style="width: 350px; height: 600px; overflow: hidden; margin: 0 auto; border-radius: 0.375rem;">
                                    <iframe id="embedIframeLarge" src="" style="width: 100%; height: 100%; border: none;" title="Campaign Preview" loading="lazy"></iframe>
                                </div>
                            </div>

                            <!-- Embed Code -->
                            <div class="form-group" style="max-width: 600px; margin: 0 auto;">
                                <label for="embedCodeLarge" class="form-label">Embed code for large widget</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" readonly id="embedCodeLarge">
                                    <button class="btn btn-primary ms-2" id="btn_copy_code_large" data-size="Large">Copy Code</button>
                                </div>
                            </div>
                        </div>

                        <!-- Small iframe tab -->
                        <div class="tab-pane fade" id="small-iframe" role="tabpanel" aria-labelledby="small-iframe-tab">
                            <!-- Preview Widget -->
                            <div class="form-group">
                                <div style="width: 350px; height: 400px; overflow: hidden; margin: 0 auto; border-radius: 0.375rem;">
                                    <iframe id="embedIframeSmall" src="" style="width: 100%; height: 100%; border: none;" title="Campaign Preview" loading="lazy"></iframe>
                                </div>
                            </div>

                            <!-- Embed Code -->
                            <div class="form-group" style="max-width: 600px; margin: 0 auto;">
                                <label for="embedCodeSmall" class="form-label">Embed code for small widget</label>
                                <div class="input-group">
                                    <input class="form-control" type="text" value="" readonly id="embedCodeSmall">
                                    <button class="btn btn-primary ms-2" id="btn_copy_code_small" data-size="Small">Copy Code</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
