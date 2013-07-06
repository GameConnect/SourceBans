<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo CHtml::encode($this->pageTitle) ?></title>
    <meta charset="UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo Yii::app()->baseUrl ?>/images/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl ?>/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl ?>/css/application.css" />
  </head>
  <body>
    <div class="container" id="page">
<?php echo $content ?>

    </div>
    <script>window.baseUrl = '<?php echo Yii::app()->baseUrl ?>';</script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    <script src="<?php echo Yii::app()->baseUrl ?>/js/jquery.mousewheel.js"></script>
    <script src="<?php echo Yii::app()->baseUrl ?>/js/application.js"></script>
  </body>
</html>