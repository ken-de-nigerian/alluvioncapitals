import GLightbox from 'glightbox';

export default {
    install: (app) => {
        app.config.globalProperties.$glightbox = GLightbox({
            selector: '[data-glightbox]',
            touchNavigation: true,
            loop: true,
            autoplayVideos: true,
        });
    }
};
