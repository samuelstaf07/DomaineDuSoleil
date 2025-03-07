import './bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import 'bootstrap'

var swiper = new Swiper(".mySwiper", {
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    slidesPerView: 4,
    spaceBetween: 50,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },
    loop: true,
});
