const removeButton = document.getElementById("remove-picture-btn");
const imgThumbnail = document.getElementById("img-thumbnail");
const existingPicture = document.getElementById('existing-picture')

if (removeButton && imgThumbnail) {
    removeButton.addEventListener("click", () => {
        imgThumbnail.src = ""; // Efface l'image
        existingPicture.style.display = "none"; // masque la div de l'image actuelle
    });
}
