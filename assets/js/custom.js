/*
* Modified functions, events plugins and theme homey
*/

(function ($) {
    "use strict";
    const guest = {
        adults: parseInt($("[name='adult_guest']").val()) || 0,
        childs: parseInt($("[name='child_guest']").val()) || 0,
        pets: parseInt($("[name='child_guest']").val()) || 0,
        arrive_date: parseInt($("[name='child_guest']").val()) || 0,
        arrive_city: parseInt($("[name='child_guest']").val()) || 0
    }
    $('.guest-apply-btn .btn').on('click', function () {
        $('.search-guests-wrap').css("display", "none");
        if (guest.adults === 0) return;

        if (localStorage.getItem('guest_data')) localStorage.removeItem('guest_data');

        localStorage.setItem('guest_data', JSON.stringify(guest));
        window.location = window.location.origin + '/register';
    });
})(jQuery);