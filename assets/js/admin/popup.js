$(document).ready(function() {
    $('.popup-gallery').magnificPopup({
        delegate: 'a:not(.not-image)' +
            ':not(.fc-daygrid-day-number)' +
            ':not(.fc-col-header-cell-cushion)' +
            ':not(.fc-daygrid-events)' +
            ':not(.btn-yellow)' +
            ':not(.btn-lightGreen)' +
            ':not(.btn-lightGreen2)' +
            ':not(.btn-red)',
        type: 'image',
        gallery: {
            enabled: true,
            tCounter: '%curr% sur %total%'
        }
    });
})