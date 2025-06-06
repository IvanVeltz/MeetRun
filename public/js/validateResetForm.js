const resetPasswordInput = document.getElementById('reset_password_form_plainPassword_first'); // On récupere le "premier" mdp
const resetConfirmPasswordInput = document.getElementById('reset_password_form_plainPassword_second'); // on recupere le second
const resetPasswordButton = document.getElementById('resetPasswordButton'); // On recupere le bouton de validation
const resetLengthCheck = document.getElementById('resetLength');
const resetUppercaseCheck = document.getElementById('resetUppercase');
const resetLowercaseCheck = document.getElementById('resetLowercase');
const resetNumberCheck = document.getElementById('resetNumber');
const resetSpecialCharCheck = document.getElementById('resetSpecialChar');
const resetMatchCheck = document.getElementById('resetMatch');

document.addEventListener("DOMContentLoaded", () => {
    function validateResetPassword() {
        const password = resetPasswordInput.value; // la valeur du premier mdp
        const confirmPassword = resetConfirmPasswordInput.value; // la valuer du second

        // Vérifications individuelles des mdp
        const isLengthValid = password.length >= 12;
        const isUppercaseValid = /[A-Z]/.test(password);
        const isLowercaseValid = /[a-z]/.test(password);
        const isNumberValid = /\d/.test(password);
        const isSpecialCharValid = /[\W_]/.test(password);
        const isMatching = password === confirmPassword && password.length > 0;

        // Mise à jour visuelle des critères
        updateValidation(resetLengthCheck, isLengthValid);
        updateValidation(resetUppercaseCheck, isUppercaseValid);
        updateValidation(resetLowercaseCheck, isLowercaseValid);
        updateValidation(resetNumberCheck, isNumberValid);
        updateValidation(resetSpecialCharCheck, isSpecialCharValid);
        updateValidation(resetMatchCheck, isMatching);

        // Activation du bouton seulement si tous les critères sont validés
        const allValid = isLengthValid && isUppercaseValid && isLowercaseValid && isNumberValid && isSpecialCharValid && isMatching;
        resetPasswordButton.disabled = !allValid;
    }


    function updateValidation(element, isValid) {
        element.classList.toggle("valid", isValid);
        element.classList.toggle("invalid", !isValid);
    }

    resetPasswordInput.addEventListener('input', validateResetPassword);
    resetConfirmPasswordInput.addEventListener('input', validateResetPassword);
})