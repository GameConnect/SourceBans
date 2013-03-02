(function($) {
  // Disable caching of AJAX requests
  $.ajaxSetup({
    cache: false
  });
  
  // Disable links with href ending with "#"
  $('a[href$="#"]').click(function(e) {
    e.preventDefault();
  });
})(jQuery);