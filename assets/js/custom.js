/*
* Modified functions, events plugins and theme homey
*/

(function ($) {
    "use strict";
    const toasts = new Toasts({
        width: 500,
        timing: 'ease',
        duration: '.5s',
        position: 'top-right' // top-left | top-center | top-right | bottom-left | bottom-center | bottom-right
    });
    $('.guest-apply-btn .btn').on('click', function () {
        $('.search-guests-wrap').css("display", "none");
    });

    $('.search-button button').on('click', function (e) {
        e.preventDefault();
        const guest = {
            adults: parseInt($(".banner-caption-side-search .search_adult_guest:first").val()) || 0,
            childs: parseInt($(".banner-caption-side-search .search_child_guest:first").val()) || 0,
            pets: parseInt($(".banner-caption-side-search .search_pet_guest:first").val()) || 0,
            arrival_date: $(".search-date-range-arrive [name=\"arrive\"]:first").val() || '0000-00-00',
            arrival_city: $(".search-destination [name=\"city\"].selectpicker:first").val() || ''
        }

        const errorMessages = {
            adults: "The number of adults must be at least 1 person",
            arrival_date: "The arrival date is invalid or empty",
            arrival_city: "Arrival city is invalid or empty"
        }

        const invalidField = Object.keys(guest).find(field => {
            if (field !== 'childs' && field !== 'pets') {
                const value = guest[field].toString();
                return /^(null|undefined|0|0000-00-00|)$/.test(value);
            }
        });

        if (invalidField) {
            toasts.push({
                title: 'Error',
                content: errorMessages[invalidField],
                style: 'warning'
            });
        } else if (guest.adults === 0) {
            toasts.push({
                title: 'Error',
                content: errorMessages.adults,
                style: 'warning'
            });
        } else {
            if (localStorage.getItem('guest_data')) localStorage.removeItem('guest_data');
            localStorage.setItem('guest_data', JSON.stringify(guest));
            window.location = window.location.origin + '/register';
        }
    });

    if ($('.login-register.list-inline a')) {
        $('.login-register.list-inline a')[0].on('click', function (e) {
            e.preventDefault();
            if (localStorage.getItem('guest_data')) localStorage.removeItem('guest_data');
            window.location = window.location.origin + '/login';
        });

        $('.login-register.list-inline a')[1].on('click', function (e) {
            e.preventDefault();
            if (localStorage.getItem('guest_data')) localStorage.removeItem('guest_data');
            window.location = window.location.origin + '/register';
        });
    }
    /* INITIALIZE PHONE INPUTS WITH THE intlTelInput FEATURE*/
    let input = document.querySelector("#form-field-field_phone");

    let iti = intlTelInput(input);

    $(window).on("load", function () {

        intlTelInputGlobals.loadUtils("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/15.0.2/js/utils.js");

        intlTelInput(input, {
            initialCountry: "us",
            separateDialCode: true,
            nationalMode: false,
        });

        let countryData = window.intlTelInputGlobals.getCountryData();

        console.log(countryData);


    });

    /* ADD A PATTERN MASK IN PHONE INPUT AND REMOVE PREVIOUS VALUE - ON COUNTRY CHANGE */
    $("#form-field-field_phone").on('focus', function (e, countryData) {

        $("#form-field-field_phone").val("").trigger("input");

        let placeholder = $("#form-field-field_phone").attr("placeholder");

        let pattern = placeholder.replace(/-/g, " ");
        let phoneNumber = pattern.replace(/\d/gi, "0");

        $("#field_phone").mask(phoneNumber);



    });
    $("#form-field-field_phone").focusout(function (e, countryData) {
        let phone_number = $("#form-field-field_phone").val();
        phone_number = iti.getNumber(intlTelInputUtils.numberFormat.E164);

        console.log(phone_number);
    });
})(jQuery);

