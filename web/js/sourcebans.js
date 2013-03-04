(function($) {
  // Disable caching of AJAX requests
  $.ajaxSetup({
    cache: false
  });
  
  // Returns whether all elements match a selector
  $.fn.are = function(selector) {
    return !!selector && this.filter(selector).length == this.length;
  };
  
  // Disable links with href ending with "#"
  $('a[href$="#"]').click(function(e) {
    e.preventDefault();
  });
})(jQuery);