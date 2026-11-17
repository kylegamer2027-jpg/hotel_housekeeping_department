document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-confirm]');
    if (trigger && !window.confirm(trigger.getAttribute('data-confirm'))) {
        event.preventDefault();
    }
});
