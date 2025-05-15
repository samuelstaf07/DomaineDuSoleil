$(document).ready(function() {
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const postId = button.getAttribute('data-id');
        const confirmBtn = deleteModal.querySelector('#confirmDeleteBtn');
        confirmBtn.href = `/admin/events/${postId}/delete`;
    });
});