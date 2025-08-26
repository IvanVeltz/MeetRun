function initCarteDepartements() {
  // const divNum = document.getElementById('num');
  const checkboxes = document.querySelectorAll('input[name="departements[]"'); // adapte si ton form a un autre nom
  const autrePatys = document.getElementById('departement_autre');
  const selected = new Set();

  if (checkboxes.length === 0) {
    console.error('div #num ou checkboxes de départements introuvables');
    return;
  }

  // Pré-remplit les départements sélectionnés
  checkboxes.forEach(cb => {
    if (cb.checked) {
      selected.add(cb.value);
    }
  });

  // Affichage initial
  document.querySelectorAll('#svg-wrapper path.land').forEach(path => {
    const depClass = Array.from(path.classList).find(cls => cls.startsWith('departement'));
    if (!depClass) return;

    const code = depClass.replace('departement', '');

    const checkbox = Array.from(checkboxes).find(cb => cb.value === code);
    if (!checkbox) return;

    // Applique la classe visuelle si sélectionné
    if (checkbox.checked) {
      path.classList.add('selected');
    }

    path.style.cursor = 'pointer';

    path.addEventListener('click', () => {
      checkbox.checked = !checkbox.checked;

      if (checkbox.checked) {
        path.classList.add('selected');
        selected.add(code);
      } else {
        path.classList.remove('selected');
        selected.delete(code);
      }

      // updateDisplay();
      checkbox.dispatchEvent(new Event('change', { bubbles: true })); // important pour AJAX
    });
  });

  syncMapSelection(checkboxes);
}

function syncMapSelection(checkboxes){
  document.querySelectorAll('#svg-wrapper path.land').forEach(path=> {
    const depClass = Array.from(path.classList).find(cls => cls.startsWith('departement'));
    if (!depClass) return;

    const code = depClass.replace('departement', '');
    const checkbox = Array.from(checkboxes).find(cb => cb.value === code);
    
    if (!checkbox) return;

    // Mise à jour visuelle : ajoute/enlève la classe selected
    if (checkbox.checked) {
      path.classList.add('selected');
    } else {
      path.classList.remove('selected');
    }
  });
}

// DOM ready
document.addEventListener('DOMContentLoaded', initCarteDepartements);
