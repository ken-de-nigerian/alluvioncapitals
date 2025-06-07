<template>
    <div v-if="showLoader" class="pageloader" :class="{ 'fade-out': isFading }">
        <div class="container h-100">
            <div class="row justify-content-center align-items-center text-center h-100">
                <div class="col-12 mb-auto pt-4"></div>
                <div class="col-auto">
                    <img src="/public/assets/img/logo.svg" alt="AdminUIUX Logo" class="height-60 mb-3" @error="handleImageError" />
                    <p class="h6 mb-0">AdminUIUX</p>
                    <p class="h3 mb-4">Investment</p>
                    <div class="loader10 mb-2 mx-auto"></div>
                </div>
                <div class="col-12 mt-auto pb-4">
                    <p class="text-secondary">Please wait we are preparing awesome things to preview...</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'Pageloader',
        data() {
            return {
                showLoader: true,
                isFading: false,
            };
        },
        mounted() {

            window.addEventListener('load', this.hideLoader);

            document.addEventListener('DOMContentLoaded', () => {
                console.log('Pageloader: DOMContentLoaded fired');
                this.hideLoader();
            });

            setTimeout(() => {
                if (this.showLoader) {
                    this.hideLoader();
                }
            }, 5000);
        },
        beforeUnmount() {
            // Clean up event listeners
            window.removeEventListener('load', this.hideLoader);
            document.removeEventListener('DOMContentLoaded', this.hideLoader);
        },
        methods: {
            hideLoader() {
                if (this.showLoader) {
                    this.isFading = true;
                    setTimeout(() => {
                        this.showLoader = false;
                    }, 500);
                }
            },
            handleImageError() {
                this.hideLoader();
            },
        },
    };
</script>

<style>
    .pageloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        transition: opacity 0.5s ease;
    }
    .pageloader.fade-out {
        opacity: 0;
    }
    .loader10 {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
