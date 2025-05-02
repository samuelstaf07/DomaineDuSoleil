$(document).ready(function() {
    let visibleComments = 0;
    const postsItems = $('.postsItems');
    const afficherPlusButton = $('.voir-plus');

    function addVisibleComment() {
        visibleComments += 10;
        const commentsToDisplay = postsItems.slice(0, visibleComments);
        commentsToDisplay.removeClass('d-none').addClass('d-flex');

        if (visibleComments >= postsItems.length) {
            afficherPlusButton.addClass('d-none');
        }
    }

    addVisibleComment();

    afficherPlusButton.on('click', addVisibleComment);
})