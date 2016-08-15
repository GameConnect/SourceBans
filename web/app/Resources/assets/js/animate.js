define(['raf'], function (raf) {
    'use strict';
    return function (el, prop, end, duration, easing) {
        duration = duration || 0;
        easing   = easing   || function (rate) { return rate; };

        var start = el[prop];
        var delta = end - start;
        var time  = +new Date() + duration;
        var tick  = function () {
            var remaining = time - +new Date();
            if (remaining <= 0) {
                return;
            }

            var rate = 1 - remaining / duration;
            el[prop] = start + delta * easing(rate);

            raf(tick);
        };

        raf(tick);
    };
});
