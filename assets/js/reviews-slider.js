/**
 * DEVA Reviews Slider using Swiper.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if Swiper is available (from Elementor or CDN)
    if (typeof Swiper !== 'undefined') {
        initializeReviewsSlider();
    } else {
        // Load Swiper from CDN if not available
        loadSwiperAndInit();
    }
});

function initializeReviewsSlider() {
    const reviewsSlider = document.querySelector('.reviews-slider.swiper');
    
    if (reviewsSlider) {
        new Swiper(reviewsSlider, {
            slidesPerView: 1.2,
            spaceBetween: 20,
            grabCursor: true,
            loop: false,
            freeMode: {
                enabled: true,
                sticky: false,
            },
            breakpoints: {
                // Mobile
                480: {
                    slidesPerView: 1.2,
                    spaceBetween: 15,
                },
                // Tablet
                768: {
                    slidesPerView: 2.5,
                    spaceBetween: 20,
                },
                // Desktop
                1024: {
                    slidesPerView: 3.5,
                    spaceBetween: 20,
                },
                // Large Desktop
                1200: {
                    slidesPerView: 3.5,
                    spaceBetween: 25,
                }
            },
            // Auto-resize observer
            observer: true,
            observeParents: true,
        });
    }
}

function loadSwiperAndInit() {
    // Load Swiper CSS
    const swiperCSS = document.createElement('link');
    swiperCSS.rel = 'stylesheet';
    swiperCSS.href = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
    document.head.appendChild(swiperCSS);
    
    // Load Swiper JS
    const swiperJS = document.createElement('script');
    swiperJS.src = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
    swiperJS.onload = function() {
        initializeReviewsSlider();
    };
    document.head.appendChild(swiperJS);
}
