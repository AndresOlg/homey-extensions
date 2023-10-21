const toasts = new Toasts({
    width: 300,
    timing: 'ease',
    duration: '.2s',
    position: 'top-right' // top-left | top-center | top-right | bottom-left | bottom-center | bottom-right
});
function validateInput(inputType, value, field) {


    if ([null, '', undefined].includes(value) && inputType != 'avatar_input') {
        field.addClass('error-field');
        toasts.push({
            dismissAfter: 2000,
            title: 'Error empty fields found',
            content: `The field ${inputType} is required`,
            style: 'error'
        });
    }

    switch (inputType) {
        case "username":
            const usernamePattern = /^[A-Za-z0-9_]+$/;

            if (!value) {
                field.after('<span class="error-message">The username field is empty</span>');
                return "invalid field";
            }

            if (value.length < 5 || value.length > 20) {
                field.after('<span class="error-message">Username must be between 5 and 20 characters</span>');
                return "invalid field";
            }

            if (!usernamePattern.test(value)) {
                field.addClass('error-field');
                field.after('<span class="error-message">Username contains invalid characters</span>');
                return "invalid field";
            }
            break;

        case "email":
            const emailPattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
            if (!emailPattern.test(value)) {
                field.addClass('error-field');
                field.after('<span class="error-message">Please enter a valid email address</span>');
                return "invalid field";
            }
            break;

        case "firstname":
            if (value.length < 5 || value.length > 20) {
                field.addClass('error-field');
                field.after('<span class="error-message">Firstname must be between 5 and 20 characters</span>');
                return "invalid field";
            }
            break;

        case "avatar_input":
            if (!field.hasOwnProperty('files')) {
                toasts.push({
                    title: 'Error profile image not selected',
                    content: `The field ${inputType} is required`,
                    style: 'error'
                });
                return "invalid field";
            } else if (field.files.length === 0) {
                toasts.push({
                    title: 'Error profile image not selected',
                    content: `The field ${inputType} is required`,
                    style: 'error'
                });
                return "invalid field";
            }

            break;

        case "role":
            if (value == 0) {
                field.addClass('error-field');
                toasts.push({
                    dismissAfter: 2000,
                    title: 'Error role user not selected',
                    content: `The field ${inputType} is required`,
                    style: 'error'
                });
            }

            break;

        case "password":
            const messagge = validatePassword(value);
            if (messagge == null) {
                field.after(`<span class="success-message">The password is strong</span>`);
            } else {
                field.after(`<span class="error-message">The password does not meet the strength criteria</span>`);
                field.after(`<br/><span class="error-message">${messagge}</span>`);
            }
            break;
    }

    // If there are no issues, the input is valid
    return null;
}

function validatePassword(password) {
    var pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/;

    // Check if the password matches the pattern
    if (pattern.test(password)) {
        return null;
    } else {
        // Check what elements are missing
        var missingElements = [];

        if (!/(?=.*[a-z])/.test(password)) {
            missingElements.push("a lowercase letter");
        }
        if (!/(?=.*[A-Z])/.test(password)) {
            missingElements.push("an uppercase letter");
        }
        if (!/(?=.*\d)/.test(password)) {
            missingElements.push("a digit");
        }
        if (!/(?=.*[@$!%*?&#])/.test(password)) {
            missingElements.push("a special character (@, $, !, %, *, ?, or #)");
        }
        if (password.length < 8) {
            missingElements.push("a minimum length of 8 characters");
        }
        if (password.length > 16) {
            missingElements.push("a maximum length of 16 characters");
        }

        return "Missing: " + missingElements.join(", ");
    }
}




