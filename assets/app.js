require('./styles/app.css');

import Filter from './modules/filter.js';
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';

export function initSlider(sliderId, minInputId, maxInputId) {
    const slider = document.getElementById(sliderId);
    const minInput = document.getElementById(minInputId);
    const maxInput = document.getElementById(maxInputId);

    if (!slider || !minInput || !maxInput) return;

    const minValue = parseInt(slider.dataset.min, 10);
    const maxValue = parseInt(slider.dataset.max, 10);

    if (slider.noUiSlider) {
        slider.noUiSlider.destroy();
    }

    noUiSlider.create(slider, {
        start: [
            parseInt(minInput.value) || minValue,
            parseInt(maxInput.value) || maxValue
        ],
        connect: true,
        step: 1,
        range: {
            min: minValue,
            max: maxValue
        }
    });

    slider.noUiSlider.on('slide', (values, handle) => {
        if (handle === 0) minInput.value = Math.round(values[0]);
        if (handle === 1) maxInput.value = Math.round(values[1]);
    });

    slider.noUiSlider.on('end', () => {
        minInput.dispatchEvent(new Event('change'));
    });
}


document.addEventListener('DOMContentLoaded', () => {

    const filterElement = document.querySelector('.js-filter');
    if (filterElement) {
        new Filter(filterElement);
    } else {
        console.warn('Aucun élément avec la classe .js-filter trouvé');
    }

    // Initialiser tous les sliders présents
    initSlider('age-slider', 'ageMin', 'ageMax');
    initSlider('distance-slider', 'distanceMin', 'distanceMax');
});

