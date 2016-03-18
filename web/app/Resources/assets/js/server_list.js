function initServerList (options) {
    var opts = $.extend({
        el: null,
        errorText: '',
        url: window.location.href
    }, options);

    $(opts.el).find('[data-server-id]').each(function () {
        var $this = $(this);

        $.get(opts.url.replace('__ID__', $this.data('serverId')), function (data) {
            $this.find('.js-server-hostname').text(data.error ? data.error.message : data.hostname);
            $this.find('.js-server-players').text(data.error ? opts.errorText : data.numplayers + '/' + data.maxplayers);
            $this.find('.js-server-map').text(data.error ? opts.errorText : data.map);
        }, 'json');
    });
}
