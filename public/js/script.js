// Modal new event
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('eventModal');
  const openBtn = document.getElementById('openModalBtn');
  const closeBtn = modal.querySelector('.close-btn');

  openBtn.addEventListener('click', (e) => {
    e.preventDefault();
    modal.style.display = "block";
  })

  closeBtn.addEventListener('click', () => {
    modal.style.display = "none";
  })

  // Ferme le modal en cliquant à l'extérieur
  window.addEventListener('click', function (e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
})

// Affichage addFlash
document.addEventListener('DOMContentLoaded', function () {
  const messages = document.querySelectorAll('.flash-message');
  messages.forEach((msg) => {
    setTimeout(() => {
      msg.style.opacity = '0';
      msg.style.transform = 'translateX(20px)';
      setTimeout(() => msg.remove(), 500); // suppression après transition
    }, 5000); // 5 secondes
  });
});

// Changement de la navbar au scorll
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
  if (window.scrollY > 50) {
    navbar.classList.add('navbar--scrolled');
  } else {
    navbar.classList.remove('navbar--scrolled');
  }
});

// Efface l'image lors de la modif de profil
const removeButton = document.getElementById("remove-picture-btn");
const imgThumbnail = document.getElementById("img-thumbnail");
const existingPicture = document.getElementById('existing-picture')

if (removeButton && imgThumbnail) {
  removeButton.addEventListener("click", () => {
    imgThumbnail.src = ""; // Efface l'image
    existingPicture.style.display = "none"; // masque la div de l'image actuelle
  });
}

// // verif mot de passe
// const passwordInput = document.getElementById('registration_form_plainPassword_first'); // On récupere le "premier" mdp
// const confirmPasswordInput = document.getElementById('registration_form_plainPassword_second'); // on recupere le second
// const registerButton = document.getElementById('registration'); // On recupere le bouton de validation
// const lengthCheck = document.getElementById('length');
// const uppercaseCheck = document.getElementById('uppercase');
// const lowercaseCheck = document.getElementById('lowercase');
// const numberCheck = document.getElementById('number');
// const specialCharCheck = document.getElementById('specialChar');
// const matchCheck = document.getElementById('match');
// const agreeTermsCheckBox = document.getElementById('registration_form_agreeTerms');

// document.addEventListener("DOMContentLoaded", () => {

//   function validateForm() {
//     const password = passwordInput.value; // la valeur du premier mdp
//     const confirmPassword = confirmPasswordInput.value; // la valuer du second

//     // Vérifications individuelles des mdp
//     const isLengthValid = password.length >= 12;
//     const isUppercaseValid = /[A-Z]/.test(password);
//     const isLowercaseValid = /[a-z]/.test(password);
//     const isNumberValid = /\d/.test(password);
//     const isSpecialCharValid = /[\W_]/.test(password);
//     const isMatching = password === confirmPassword && password.length > 0;

//     // Mise à jour visuelle des critères
//     updateValidation(lengthCheck, isLengthValid);
//     updateValidation(uppercaseCheck, isUppercaseValid);
//     updateValidation(lowercaseCheck, isLowercaseValid);
//     updateValidation(numberCheck, isNumberValid);
//     updateValidation(specialCharCheck, isSpecialCharValid);
//     updateValidation(matchCheck, isMatching);

//     // Verifications si les conditions d'utilisations sont cochées
//     const agreeTerms = agreeTermsCheckBox.checked

//     // Activation du bouton seulement si tous les critères sont validés
//     const allValid = isLengthValid && isUppercaseValid && isLowercaseValid && isNumberValid && isSpecialCharValid && isMatching && agreeTerms;
//     registerButton.disabled = !allValid;

//   }

//   function updateValidation(element, isValid) {
//     element.classList.toggle("valid", isValid);
//     element.classList.toggle("invalid", !isValid);
//   }
//   if (passwordInput && confirmPasswordInput && agreeTermsCheckBox) {
//     passwordInput.addEventListener('input', validateForm);
//     confirmPasswordInput.addEventListener('input', validateForm);
//     agreeTermsCheckBox.addEventListener('change', validateForm);
//   }
// })

// MENU BURGER 
const toggleBtn = document.getElementById("nav-toggle");
const closeBtn = document.getElementById("nav-close");
const navbarWrapper = document.getElementById("navbarWrapper");
const profilBtn = document.getElementById("profilDropdown");
const dropdownMenu = document.querySelector(".dropdown-menu");

toggleBtn.addEventListener("click", () => {
  navbarWrapper.classList.toggle("show");
});

closeBtn.addEventListener("click", () => {
  navbarWrapper.classList.remove("show");
});

profilBtn.addEventListener("click", () => {
  dropdownMenu.classList.toggle("show");
});

