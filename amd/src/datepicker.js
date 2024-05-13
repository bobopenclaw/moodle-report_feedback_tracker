export const init = () => {
    window.console.log('datepicker.js initialised');

    document.addEventListener('DOMContentLoaded', function() {
        var dateInputs = document.querySelectorAll('.date-picker');
        Array.prototype.forEach.call(dateInputs, function(input) {
            // Add a polyfill for browsers that don't support date input
            if (input.type !== 'date') {
                input.type = 'text';
                input.placeholder = 'YYYY-MM-DD';
                input.addEventListener('focus', function() {
                    var picker = 'no picker';
                    picker.show();
                });
            }
        });
    });

};