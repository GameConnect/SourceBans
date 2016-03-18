function initServerSelect (options) {
    var opts = $.extend({
        el: null,
        url: window.location.href
    }, options);

    $(opts.el).find('option').each(function () {
        var $this = $(this);
        if (!$this.prop('value')) {
            return;
        }

        $.get(opts.url.replace('__ID__', $this.prop('value')), function (data) {
            $this.text(data.error ? data.error.message : data.hostname);
        });
    });
}
