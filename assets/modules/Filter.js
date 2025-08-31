import { initSlider } from '../app.js';

/**
 * @property {HTMLElement} pagination
 * @property {HTMLElement} content
 * @property {HTMLElement} sorting
 * @property {HTMLFormElement} form
 * @property {HTMLElement} errorMessageDiv
 */
export default class Filter{

    
    /**
     * @param {HTMLElement|null} element
     */
    constructor (element) {

        if (element === null){
            return
        }
        this.pagination = element.querySelector('.js-filter-pagination');
        this.content = element.querySelector('.js-filter-content');
        this.sorting = element.querySelector('.js-filter-sorting');
        this.form = element.querySelector('.js-filter-form');
        this.errorMessageDiv = document.getElementById('error-message');
        this.bindEvents()
    }
    /**
     * Ajoute les comportements aux différents éléments
     */
    bindEvents(){
        const aClickListener = e => {
            if (e.target.tagName === 'A') {
                e.preventDefault();
                this.loadUrl(e.target.getAttribute('href'))
            }
        } 
        if (this.sorting) {
            this.sorting.addEventListener('click', aClickListener)
        }
        if (this.pagination) {
            this.pagination.addEventListener('click', aClickListener)
        }
        
        this.form.querySelectorAll('input').forEach(input => {
            if (input.type === 'text'){
                // On ecoute l'evenement avec un délai
                let typingTimer;
                const debounceDelay = 400;

                input.addEventListener('input', () => {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    this.loadForm();
                }, debounceDelay);
            });
            } else {
                input.addEventListener('change', this.loadForm.bind(this))
            }
        })
        
        // bouton reset
        const resetBtn = this.form.querySelector('#reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', e => {
                e.preventDefault();
                // reset manuel des champs texte, select, checkbox
                this.form.querySelectorAll('input, select, textarea').forEach(el => {
                    switch(el.type) {
                        case 'checkbox':
                        case 'radio':
                            el.checked = false;
                            break;
                        case 'text':
                        case 'hidden':
                        case 'number':
                        case 'email':
                        default:
                            el.value = '';
                    }
                });
                syncMapSelection(this.form.querySelectorAll('input[name="departements[]"]'));
                // reset du slider noUiSlider si présent
                if (this.slider && this.slider.noUiSlider) {
                    this.slider.noUiSlider.reset();
                    const sliderInputs = this.slider.querySelectorAll('input');
                    sliderInputs.forEach(input => input.dispatchEvent(new Event('change)')));
                }

                // réinitialiser l’URL (pour history)
                const url = new URL(this.form.getAttribute('action') || window.location.href);
                history.replaceState({}, '', url.pathname);
                console.log('hey')
                // relancer les résultats par défaut
                this.loadForm();
            })
        }
    }

    async loadForm() {
        const data = new FormData(this.form)
        const url = new URL(this.form.getAttribute('action') || window.location.href)
        const params = new URLSearchParams()
        data.forEach((value, key) => {
            params.append(key, value)
        })
        return this.loadUrl(url.pathname + '?' + params.toString())
    }

    async loadUrl(url){
        const ajaxUrl = url + '&ajax=1'
        const response = await fetch(ajaxUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (response.status >= 200 && response.status < 300) {
            const data = await response.json()
            this.content.innerHTML = data.content
            if (this.sorting && data.sorting !== undefined) {
                this.sorting.innerHTML = data.sorting
            }
            if (this.pagination && data.pagination !== undefined) {
                this.pagination.innerHTML = data.pagination
            }
            initSlider()
            history.replaceState({}, '', url)
            
        } else {
            this.errorMessageDiv.textContent = 'Une erreur est survenue : ' + response.status;
            console.error(response)
        }
    }

}