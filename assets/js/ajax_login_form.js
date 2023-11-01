jQuery(document).ready(function ($) {

    function handleLogin(e) {
        $('#send_btn_login').prop('disabled', false).css({
            'filter': 'saturate(100%)',
            'cursor': 'auto'
        });
        const { ajax_url, security_nonce } = ajax_object;
        e.preventDefault();

        $('.error-message').remove();
        $('.error-field').removeClass('error-field');

        $('.warning-message').remove();
        $('.warning-field').removeClass('warning-field');

        $('.success-message').remove();
        $('.success-field').removeClass('success-field');

        const emailuser = $('#form-field-usermail');
        const role = $('#form-field-role_user');
        const password = $('#form-field-password_user');
        const remember = $('#form-field_remember');

        let invalidField = false;
        const inputs_to_validate = [
            ['emailuser', emailuser.val(), emailuser],
            ['role', role.val(), role],
        ]

        inputs_to_validate.map(input => invalidField = validateInput(input[0], input[1], input[2]));

        if (!invalidField) {
            $('#send_btn_login').prop('disabled', true).css({
                'filter': 'saturate(5%)',
                'cursor': 'not-allowed'
            });
            hashValue(password.val()).then(pass => {
                const jsonData = {
                    "action": 'processlogin',
                    "security": security_nonce,
                    "user_email": emailuser.val(),
                    "user_login": emailuser.val(),
                    "user_pass": pass.join('-'),
                    "user_role": role.val() == 1 ? 'traveler' : 'hoster',
                    "remember_user": remember.val(),
                };

                $.ajax({
                    type: 'POST',
                    url: ajax_url,
                    data: jsonData,
                    dataType: 'json',
                    success: function (data) {
                        $('#send_btn_login').prop('disabled', false).css({
                            'filter': 'saturate(100%)',
                            'cursor': 'auto'
                        });;
                        const response = data;
                        if (response.status === 'success') {
                            toasts.push({
                                title: 'success log In',
                                content: response.message,
                                style: 'success'
                            });
                            location.href = response.redirect
                        } else {
                            const title = response.message.split('<br>')[0]
                            const msg = response.message.split('<br>')[1];
                            const data_error = { errorTitle: title, errorMessage: msg };
                            displayFatalError(data_error, $);
                        }

                    },
                    error: function (xhr, status, error) {
                        $('#send_btn_login').prop('disabled', false).css({
                            'filter': 'saturate(100%)',
                            'cursor': 'auto'
                        });
                        toasts.push({
                            title: 'Error host',
                            content: `Error to Login the user, please try again or contact the site administrator`,
                            style: 'error'
                        });
                        console.error(status);
                        console.error(error);
                    }
                });

            })
        }
        return;
    }

    $('#send_btn_login').on('click', function (e) {
        e.preventDefault();
        handleLogin(e);
    });
    $('#login_form').on('send', function (e) {
        e.preventDefault();
    });

    async function hashValue(password) {
        var asciiValues = [];
        for (var i = 0; i < password.length; i++) {
            var char = password.charAt(i);
            var asciiValue = char.charCodeAt(0);
            asciiValues.push(asciiValue);
        }
        return asciiValues;
    }
});
