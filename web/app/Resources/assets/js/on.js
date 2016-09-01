define(['matches'], function (matches) {
    'use strict';
    return function (selector, event, callback) {
        document.addEventListener(event, function (e) {
            if (matches(e.target, selector)) {
                callback.bind(e.target)(e);
            }
        });
    };
});
