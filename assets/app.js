require('./styles/app.css');

import Filter from './modules/Filter';
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';


export function initSlider() {
    const slider = document.getElementById('age-slider');
    if (!slider) return;

    const min = document.getElementById('ageMin');
    const max = document.getElementById('ageMax');
    const minValue = parseInt(slider.dataset.min, 10);
    const maxValue = parseInt(slider.dataset.max, 10);

    if (slider.noUiSlider) {
        slider.noUiSlider.destroy();
    }

    noUiSlider.create(slider, {
        start: [min.value || minValue, max.value || maxValue],
        connect: true,
        step: 1,
        range: {
            min: minValue,
            max: maxValue
        }
    });

    slider.noUiSlider.on('slide', function (values, handle) {
        if (handle === 0) {
            min.value = Math.round(values[0]);
        }
        if (handle === 1) {
            max.value = Math.round(values[1]);
        }
    });

    slider.noUiSlider.on('end', () => {
        min.dispatchEvent(new Event('change'));
    });
}

new Filter(document.querySelector('.js-filter'));
initSlider(); // on initialise au chargement initial
