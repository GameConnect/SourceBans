define(function () {
    'use strict';
    return function () {
        var el    = document.getElementById('settings_form_enable_smtp'),
            event = document.createEvent('Event');
        event.initEvent('change', true, true);

        el.addEventListener('change', function () {
            document.getElementById('settings_form_smtp_host').disabled =
            document.getElementById('settings_form_smtp_port').disabled =
            document.getElementById('settings_form_smtp_username').disabled =
            document.getElementById('settings_form_smtp_password').disabled =
            document.getElementById('settings_form_smtp_secure_0').disabled =
            document.getElementById('settings_form_smtp_secure_1').disabled =
            document.getElementById('settings_form_smtp_secure_2').disabled = !this.checked;
        });
        el.dispatchEvent(event);
    };
});
