jQuery(document).ready(function ($) {
    const imgPreview = $('#avatar_preview img');
    const removeButton = $('#remove_avatar');

    removeImage();

    function loadAndPreviewImage() {
        var input = this;

        if (input.files && input.files[0]) {
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
    function isValidEmail(email) {
        // Utiliza una expresión regular para validar el formato del correo
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        return emailPattern.test(email);
    }
    function handleRegistration(e, ajaxUrl) {
        e.preventDefault();

        // Limpiar mensajes de error anteriores
        $('.error-message').remove();
        $('.error-field').removeClass('error-field');

        // Obtener los valores de los campos
        const username = $('#form-field-username');
        const emailuser = $('#form-field-email_user');
        const role = $('#form-field-role_user');
        const password = $('#form-field-password');
        const imageInput = document.getElementById('form-field-avatar_upload');

        // Validar que el campo de nombre de usuario no esté vacío
        if (!username.val() && username.val().trim() === '') {
            username.addClass('error-field');
            username.after('<span class="error-message">El campo de nombre de usuario está vacío</span>');
            return;
        }

        // Validar que el campo de correo electrónico no esté vacío y sea válido
        if (!emailuser.val() && emailuser.val().trim() === '' || !isValidEmail(emailuser.val())) {
            emailuser.addClass('error-field');
            emailuser.after('<span class="error-message">Por favor, ingrese un correo electrónico válido</span>');
            return;
        }

        // Validar que se haya seleccionado un rol
        if (!role.val() || role.val() == 0) {
            role.addClass('error-field');
            role.after('<span class="error-message">Error: el rol de usuario está vacío</span>');
            return;
        }

        // Validar que se haya seleccionado una imagen
        if (imageInput.files.length === 0) {
            alert('Error: debe seleccionar una imagen de perfil');
            return;
        }

        const hashValue = val =>
        crypto.subtle
            .digest('SHA-256', new TextEncoder('utf-8').encode(val))
            .then(h => {
                let hexes = [],
                    view = new DataView(h);
                for (let i = 0; i < view.byteLength; i += 4)
                    hexes.push(('00000000' + view.getUint32(i).toString(16)).slice(-8));
                return hexes.join('');
            });

        // Realizar la hash del campo de contraseña
        hashValue(password.val()).then(pass => {
            const jsonData = {
                action: 'general_register',
                form_fields: {
                    user_login: username.val(),
                    user_pass: pass,
                    email_pass: emailuser.val(),
                    user_role: role.val() == 1 ? 'traveler' : 'hoster',
                    image_base64: imgPreview.attr('src')
                }
            };

            // Realizar la petición AJAX
            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: JSON.stringify(jsonData),
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                success: function (response) {
                    alert(response.message);
                }
            });
        })
    }

    $('#browse_avatar').on('click', function (e) {
        e.preventDefault();
        $('#form-field-avatar_upload').click();
    });

    $('#send_btn_register').on('click', function (e) {
        e.preventDefault();
        const ajaxUrl = ajax_object.ajaxurl;
        handleRegistration(e, ajaxUrl);
    });

    $('#form-field-avatar_upload').on('change', loadAndPreviewImage);

    $('#remove_avatar').on('click', removeImage);
});
