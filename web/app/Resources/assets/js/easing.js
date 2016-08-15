define(function () {
    'use strict';
    var easingFuncs = {};
    var easingTypes = {
        'Quad': function (p) {
            return Math.pow(p, 2);
        },
        'Cubic': function (p) {
            return Math.pow(p, 3);
        },
        'Quart': function (p) {
            return Math.pow(p, 4);
        },
        'Quint': function (p) {
            return Math.pow(p, 5);
        },
        'Expo': function (p) {
            return Math.pow(p, 6);
        },
        'Sine': function (p) {
            return 1 - Math.cos(p * Math.PI / 2);
        },
        'Circ': function (p) {
            return 1 - Math.sqrt(1 - p * p);
        },
        'Elastic': function (p) {
            return p === 0 || p === 1 ? p :
                -Math.pow(2, 8 * (p - 1)) * Math.sin(((p - 1) * 80 - 7.5) * Math.PI / 15);
        },
        'Back': function (p) {
            return p * p * (3 * p - 2);
        },
        'Bounce': function (p) {
            var pow2,
                bounce = 4;

            while (p < ((pow2 = Math.pow(2, --bounce)) - 1) / 11) {}
            return 1 / Math.pow(4, 3 - bounce) - 7.5625 * Math.pow((pow2 * 3 - 2) / 22 - p, 2);
        }
    };

    Object.keys(easingTypes).forEach(function (name) {
        var easeIn = easingTypes[name];

        easingFuncs['easeIn' + name] = easeIn;
        easingFuncs['easeOut' + name] = function (p) {
            return 1 - easeIn(1 - p);
        };
        easingFuncs['easeInOut' + name] = function (p) {
            return p < .5
                ? easeIn(p * 2) / 2
                : 1 - easeIn(p * -2 + 2) / 2;
        };
    });

    return easingFuncs;
});
