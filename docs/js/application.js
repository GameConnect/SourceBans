$('a.toggle').click(function(e) {
  e.preventDefault();
  if($(this).text().indexOf('Hide') != -1) {
    $(this).text($(this).text().replace(/Hide/, 'Show'));
    $(this).parents('.summary').find('.inherited').hide();
  }
  else {
    $(this).text($(this).text().replace(/Show/, 'Hide'));
    $(this).parents('.summary').find('.inherited').show();
  }
});
$('.sourceCode a.show').click(function(e) {
  e.preventDefault();
  if($(this).text() == 'hide') {
    $(this).text($(this).text().replace(/hide/, 'show'));
    $(this).parents('.sourceCode').find('div.code').slideUp(250);
  }
  else {
    $(this).text($(this).text().replace(/show/, 'hide'));
    $(this).parents('.sourceCode').find('div.code').slideDown(250);
  }
});

$(function() {
  if(!$('#search').length)
    return;
  
  $('#search')
    .autocomplete({
      delay: 250,
      messages: {
        noResults: '',
        results: function() {}
      },
      minLength: 3,
      select: function(event, ui) {
        if(!ui.item)
          return;
        
        location.href = window.baseUrl + '/api/' + ui.item.value.replace(/\(\)/, '').replace(/\./, '#');
      },
      source: function(request, response) {
        $.ajax({
          url: window.baseUrl + '/api/search',
          data: {
            q: request.term
          },
          dataType: 'json',
          success: function(data) {
            var match = new RegExp('(' + request.term + ')', 'i');
            
            response($.map(data, function(item, index) {
              return {
                label: item.replace(match, '<span class="match">$1</span>'),
                value: item
              }
            }));
          }
        });
      }
    })
    .on('focus', function() {
      $(this).autocomplete('search');
    })
    .data('ui-autocomplete')._renderItem = function(ul, item) {
      return $('<li>')
        .append($('<a>').html(item.label))
        .appendTo(ul);
    };
  
  // Block scrolling page at top and bottom of autocomplete results
  $(document).on('mousewheel', '.ui-autocomplete', function(e, d) {
    if((this.scrollTop === 0 && d > 0) ||
       (this.scrollTop === (this.scrollHeight - $(this).height()) && d < 0)) {
      e.preventDefault();
    }
  });
});