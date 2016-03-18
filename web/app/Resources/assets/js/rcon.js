function initRcon (options) {
    var opts = $.extend({
        successText: '',
        url: window.location.href
    }, options);
    var scrollConsole = function (duration) {
        var $console = $('#console');
        $console.stop(true).animate({
            scrollTop: $console[0].scrollHeight - $console.height()
        }, duration, 'easeInOutCubic');
    };

    $('#command-form').submit(function (e) {
        e.preventDefault();
        $('#submit').prop('disabled', true);

        var $console = $('#console');
        var $command = $('#command');
        var command = $command.val().trim();
        if (command == '') {
            return;
        }
        if (command == 'clr') {
            $console.text('');
            $command.val('');
        } else {
            $command.prop('disabled', true);
            $console.text($console.text() + '> ' + command + "\n");
            scrollConsole(200);

            $.post(opts.url, {
                command: command
            }, function (data) {
                $console.text($console.text() + (data.error || data.result || opts.successText) + "\n");
                scrollConsole(200);

                $command.val('').prop('disabled', false);
            }, 'json');
        }
    });
    $('#command').keyup(function (e) {
        // If Enter was pressed, ignore
        if (e.which == 13) {
            return;
        }

        $('#submit').prop('disabled', $(this).val().trim() == '');
    });

    scrollConsole(800);
}
