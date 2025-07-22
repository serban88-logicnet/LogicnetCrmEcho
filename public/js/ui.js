$(function () {
    $('.toggle-section').on('click', function () {
        const target = $(this).data('target');
        $(target).slideToggle();
    });
});
