(function($) {
  // Disable caching of AJAX requests
  $.ajaxSetup({
    cache: false
  });
  
  // Returns whether all elements match a selector
  $.fn.are = function(selector) {
    return !!selector && this.filter(selector).length == this.length;
  };
  
  $.alert = function(message, type) {
    $('<div class="alert' + (type ? ' alert-' + type : '') + ' fade in">' + message + '</div>')
      .prepend('<button type="button" class="close" data-dismiss="alert">&times;</button>')
      .appendTo($('.page-alert').html(''))
      .alert();
  }
  
  // Disable links with href ending with "#"
  $(document).on('click', 'a[href$="#"]', function(e) {
    e.preventDefault();
  });
})(jQuery);