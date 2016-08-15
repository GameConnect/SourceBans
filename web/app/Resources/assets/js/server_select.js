define(['ramda', 'axios'], function (R, axios) {
    'use strict';
    return function (id, infoUrl) {
        var setOptionData = function (option, data) {
            option.textContent = (data.error ? data.error : data.hostname);
        };

        R.forEach(function (option) {
            axios(infoUrl.replace('__ID__', option.getAttribute('value')))
                .then(function (res) { setOptionData(option, res.data); })
                .catch(function (err) { setOptionData(option, err.response.data); });
        })(document.getElementById(id).querySelectorAll('option:not([value=""])'));
    };
});
