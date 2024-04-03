import './bootstrap';
import.meta.glob([
    '../images/**',
    '../fonts/**',
]);

import TomSelect from 'tom-select';
document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('select[multiple]')
    Array.from(selects).forEach((e) => {
        new TomSelect(e, {
            plugins: ['remove_button'],
        })
    });
});