// Modal new event
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('eventModal');
  const openBtn = document.getElementById('openModalBtn');
  if(modal && openBtn){
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
  }
})

// Affichage addFlash
document.addEventListener('DOMContentLoaded', function () {
  const messages = document.querySelectorAll('.flash-message');
  if(messages){

    messages.forEach((msg) => {
      setTimeout(() => {
        msg.style.opacity = '0';
        msg.style.transform = 'translateX(20px)';
        setTimeout(() => msg.remove(), 500); // suppression après transition
      }, 5000); // 5 secondes
    });
  }
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

// Pour le register form, afficher le nom de limage choisei
document.addEventListener('DOMContentLoaded', function () {
  const fileInput = document.querySelector('.js-profil-upload');
  const fileNameSpan = document.getElementById('file-name');

  if (fileInput && fileNameSpan) {
    fileInput.addEventListener('change', function () {
      fileNameSpan.textContent = fileInput.files.length > 0
          ? fileInput.files[0].name
          : 'Aucun fichier choisi';
    });
  }
});

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

// Mettre la conversation à la fin de base
document.addEventListener('DOMContentLoaded', () => {
  const chatMessages = document.getElementById('chat-messages');
  if (chatMessages) {
      chatMessages.scrollTop = chatMessages.scrollHeight;
  }
});