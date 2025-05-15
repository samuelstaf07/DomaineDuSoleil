$(document).ready(function() {
    var table = $('#myTable2').DataTable({
        pageLength: 25,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.3.0/i18n/fr-FR.json',
        },
    });

    $('#searchInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    $('#lengthSelect').on('change', function () {
        table.page.len(this.value).draw();
    });
})