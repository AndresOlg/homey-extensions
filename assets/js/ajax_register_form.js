jQuery(document).ready(function ($) {
    const imgPreview = $('#avatar_preview img');
    const removeButton = $('#remove_avatar');
    removeImage();

    function loadAndPreviewImage() {
        var input = document.getElementById('form-field-avatar_upload');
        if (input.files && input.files[0]) {

            const fileSize = input.files[0].size; // file size in bytes
            const maxSizeInBytes = 2 * 1024 * 1024; // 2 MB in bytes

            if (fileSize > maxSizeInBytes) {
                toasts.push({
                    title: 'Error profile image not selected',
                    content: `The field ${inputType} is required, please load other image!`,
                    style: 'warning'
                });
                input.addClass('warning-field');
                input.value = '';
                return;
            }
            var reader = new FileReader();

            reader.onload = function (e) {
                imgPreview.attr('src', e.target.result);
                removeButton.show();
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImage() {
        var input = $('#form-field-avatar_upload');
        imgPreview.attr('src', window.location.origin + '/wp-content/plugins/elementor/assets/images/placeholder.png');
        removeButton.hide();
        input.val('');
    }

    function handleRegistration(e) {
        $('#send_btn_register').prop('disabled', false).css({
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

        const username = $('#form-field-username');
        const emailuser = $('#form-field-email_user');
        const role = $('#form-field-role_user');
        const password = $('#form-field-password_user');
        const imageInput = $('#form-field-avatar_upload');
        const terms_conditions = $('#form-field_term_condition');

        let invalidField = false;
        const inputs_to_validate = [
            ['username', username.val(), username],
            ['email', emailuser.val(), emailuser],
            ['role', role.val(), role],
            ['avatar_input', imageInput.val(), imageInput],
            ['password', password.val(), password],
            ['terms_conditions', terms_conditions.is(':checked'), terms_conditions],
        ]

        inputs_to_validate.map(input => invalidField = validateInput(input[0], input[1], input[2]));

        if (!invalidField) {
            $('#send_btn_register').prop('disabled', true).css({
                'filter': 'saturate(5%)',
                'cursor': 'not-allowed'
            });
            hashValue(password.val()).then(pass => {
                const jsonData = {
                    "action": 'general_register',
                    "security": security_nonce,
                    "user_login": username.val(),
                    "user_pass": pass,
                    "user_email": emailuser.val(),
                    "user_role": role.val() == 1 ? 'traveler' : 'hoster',
                    "image_base64": imgPreview.attr('src')
                };

                $.ajax({
                    type: 'POST',
                    url: ajax_url,
                    data: jsonData,
                    dataType: 'json',
                    success: function (data) {
                        $('#send_btn_register').prop('disabled', false).css({
                            'filter': 'saturate(100%)',
                            'cursor': 'auto'
                        });;
                        const response = data;
                        if (response.status === 'success') {
                            toasts.push({
                                title: 'success registration',
                                content: response.message,
                                style: 'success'
                            });
                        } else {
                            const title = response.message.split('<br>')[0]
                            const msg = response.message.split('<br>')[1];
                            const data_error = { errorTitle: title, errorMessage: msg };
                            displayFatalError(data_error,$);
                        }

                    },
                    error: function (xhr, status, error) {
                        $('#send_btn_register').prop('disabled', false).css({
                            'filter': 'saturate(100%)',
                            'cursor': 'auto'
                        });
                        toasts.push({
                            title: 'Error host',
                            content: `Error to register the user, please try again or contact the site administrator`,
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

    $('#browse_avatar').on('click', function (e) {
        e.preventDefault();
        $('#form-field-avatar_upload').attr("accept", "image/*").click();
    });

    $('#send_btn_register').on('click', function (e) {
        e.preventDefault();
        handleRegistration(e);
    });
    $('#register_form').on('send', function (e) {
        e.preventDefault();
    });

    $('#form-field-avatar_upload').on('change', loadAndPreviewImage);

    $('#remove_avatar').on('click', removeImage);

    async function hashValue(password) {
        const encoder = new TextEncoder();
        const data = encoder.encode(password);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');
        return hashHex;
    }
});
