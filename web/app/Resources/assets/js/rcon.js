define(['animate', 'easing', 'axios'], function (animate, easing, axios) {
    'use strict';
    return function (rconUrl, successText) {
        var $console = document.getElementById('console');
        var $command = document.getElementById('command');
        var $form    = document.getElementById('command-form');
        var $submit  = document.getElementById('submit');
        var scrollConsole = function (duration) {
            var scrollTo = $console.scrollHeight - $console.clientHeight;
            animate($console, 'scrollTop', scrollTo, duration, easing.easeInOutCubic);
        };

        $form.addEventListener('submit', function (e) {
            e.preventDefault();
            $submit.disabled = true;

            var command = $command.value.trim();
            if (command == '') {
                return;
            }
            if (command == 'clr') {
                $console.textContent = '';
                $command.value = '';
                return;
            }

            $command.disabled = true;
            $console.textContent += '> ' + command + "\n";
            scrollConsole(200);

            var handleData = function (data) {
                $console.textContent += (data.error || data.result || successText) + "\n";
                scrollConsole(200);

                $command.value = '';
                $command.disabled = false;
            };

            axios.post(rconUrl, {command: command})
                .then(function (res) { handleData(res.data); })
                .catch(function (err) { handleData(err.response.data); });
        });
        $command.addEventListener('keyup', function (e) {
            // If Enter was pressed, ignore
            if (e.which == 13) {
                return;
            }

            $submit.disabled = (this.value.trim() == '');
        });

        scrollConsole(800);
    };
});
