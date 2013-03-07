<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo $this->pageTitle; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="css/style.css" rel="stylesheet" type="text/css" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
  </head>

  <body>
    <div class="container">

      <header>
        <h1>{{index|<?php echo Yii::app()->name; ?> API}}</h1>
      </header>

      <div id="content">
<?php echo $content; ?>
      </div>

    </div>

<script type="text/javascript">
  /*<![CDATA[*/
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
  /*]]>*/
</script>

  </body>
</html>