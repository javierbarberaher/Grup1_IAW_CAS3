/**
 * Comportamiento JavaScript global de CAS3.
 *
 * Los formularios con atributo data-confirm muestran una confirmacion nativa
 * del navegador antes de enviarse. Si el usuario cancela, se bloquea el submit.
 */
document.addEventListener('submit', function (event) {
    var form = event.target;

    if (!form.matches('[data-confirm]')) {
        return;
    }

    if (!window.confirm(form.getAttribute('data-confirm'))) {
        event.preventDefault();
    }
});
