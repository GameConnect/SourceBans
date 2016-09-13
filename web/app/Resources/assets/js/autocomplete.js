define(['on'], function (on) {
    'use strict';
    return function (selector, delay, callback) {
        var timeout, value;

        on(selector, 'keyup', function () {
            if (value == this.value) {
                return;
            }

            value = this.value;

            if (timeout) {
                clearTimeout(timeout);
            }

            timeout = setTimeout(callback.bind(this), delay);
        });
    };
});
