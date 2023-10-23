/*
* Modified functions, events plugins and theme homey
*/

(function ($) {
    "use strict";
    $('.guest-apply-btn .btn').on('click', function () {
        $('.search-guests-wrap').css("display", "none");
    });

    $('.search-button button').on('click', function (e) {
        e.preventDefault();
        const guest = {
            adults: parseInt($(".banner-caption-side-search .search_adult_guest:first").val()) || 0,
            childs: parseInt($(".banner-caption-side-search .search_child_guest:first").val()) || 0,
            pets: parseInt($(".banner-caption-side-search .search_pet_guest:first").val()) || 0,
            arrive_date: $(".search-date-range-arrive [name=\"arrive\"]:first").val() || '0000-00-00',
            arrive_city: $(".search-destination [name=\"city\"].selectpicker:first").val() || ''
        }
        if (guest.adults === 0) return;
        if (localStorage.getItem('guest_data')) localStorage.removeItem('guest_data');
        localStorage.setItem('guest_data', JSON.stringify(guest));
        window.location = window.location.origin + '/register';
    });

    $('.login-register.list-inline a:nth-child(1)').on('click', function (e) {
        e.preventDefault();
        if (localStorage.getItem('guest_data')) localStorage.removeItem('guest_data');
        window.location = window.location.origin + '/register';
    });

})(jQuery);