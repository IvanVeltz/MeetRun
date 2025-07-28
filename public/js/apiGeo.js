const inputCp = document.getElementById('profil_form_postalCode');
const selectCity = document.getElementById('selectCity'); 
const latInput = document.getElementById('profil_form_latitude');
const lonInput = document.getElementById('profil_form_longitude');


let villes = []; // Stocker les villes récupérées

inputCp.addEventListener('input', () => {
    const cp = inputCp.value.trim();

    // Réinitialiser la liste
    selectCity.innerHTML = '';
    latInput.value = '';
    lonInput.value = '';
    villes = [];

    if (cp.length !== 5) return; // attendre un code postal complet

    fetch(`https://geo.api.gouv.fr/communes?codePostal=${cp}&fields=nom,centre&format=json&geometry=centre`)
        .then((response) => response.json())
        .then((data) => {
            villes = data;

            if (villes.length === 0) {
                const option = document.createElement('option');
                option.textContent = 'Aucune ville trouvée';
                option.disabled = true;
                selectCity.appendChild(option);
                return;
            }

            villes.forEach((ville) => {
                const option = document.createElement('option');
                option.value = ville.nom;
                option.textContent = ville.nom;
                selectCity.appendChild(option);
            });

            // Mettre les coordonnées de la première ville par défaut
            updateCoordinates(villes[0]);
        });
});

// Quand l'utilisateur sélectionne une ville dans la liste
selectCity.addEventListener('change', () => {
    const selectedName = selectCity.value;
    const selectedVille = villes.find(v => v.nom === selectedName);
    if (selectedVille) {
        updateCoordinates(selectedVille);
    }
});

function updateCoordinates(ville) {
    if (ville.centre && ville.centre.coordinates) {
        latInput.value = ville.centre.coordinates[1];
        lonInput.value = ville.centre.coordinates[0];
    }
}
