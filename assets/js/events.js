$(document).ready(function() {
    $('.popup-gallery').magnificPopup({
        delegate: 'a:not(.not-image)' +
            ':not(.btn-yellow)',
        type: 'image',
        gallery: {
            enabled: true,
            tCounter: '%curr% sur %total%'
        }
    });

    let visibleComments = 0;
    const eventsItems = $('.eventsItem');
    const afficherPlusButton = $('.voir-plus');

    function addVisibleComment() {
        visibleComments += 8;
        const commentsToDisplay = eventsItems.slice(0, visibleComments);
        commentsToDisplay.removeClass('d-none').addClass('d-flex');

        if (visibleComments >= eventsItems.length) {
            afficherPlusButton.addClass('d-none');
        }
    }

    addVisibleComment();

    afficherPlusButton.on('click', addVisibleComment);
})