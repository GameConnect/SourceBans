define(['ramda', 'axios'], function (R, axios) {
    'use strict';
    return function (selector, infoUrl, errorText) {
        var setRowData = function (row, data) {
            R.forEach(function (col) {
                col.textContent = (data.error ? data.error : data.hostname);
            })(row.getElementsByClassName('js-server-hostname'));
            R.forEach(function (col) {
                col.textContent = (data.error ? errorText : data.map);
            })(row.getElementsByClassName('js-server-map'));
            R.forEach(function (col) {
                col.textContent = (data.error ? errorText : data.numplayers + '/' + data.maxplayers);
            })(row.getElementsByClassName('js-server-players'));
        };

        R.forEach(function (row) {
            axios(infoUrl.replace('__ID__', row.getAttribute('data-server-id')))
                .then(function (res) { setRowData(row, res.data); })
                .catch(function (err) { setRowData(row, err.response.data); });
        })(document.querySelectorAll(selector + ' [data-server-id]'));
    };
});
