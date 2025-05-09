function showLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'flex';
    }
}
function hideLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'none';
    }
}

document.addEventListener('turbo:visit', showLoader);
document.addEventListener('turbo:load', hideLoader);

document.addEventListener('turbo:before-fetch-request', showLoader);
document.addEventListener('turbo:before-fetch-response', hideLoader);
document.addEventListener('turbo:fetch-request-error', hideLoader);

document.querySelector('form[data-turbo="false"]').addEventListener('submit', function () {
    showLoader();
});