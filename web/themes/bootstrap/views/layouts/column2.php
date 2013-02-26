<?php $this->beginContent('//layouts/main') ?>

<div class="row">
  <nav class="span3" id="sidebar">
<?php $this->widget('zii.widgets.CMenu', array(
	'id' => 'menu',
	'items' => $this->menu,
	'htmlOptions' => array(
		'class' => 'nav nav-stacked nav-tabs',
	),
)) ?>

  </nav>
  <section class="span9" id="content">
<?php echo $content ?>

  </section>
</div>

<?php Yii::app()->clientScript->registerCoreScript('bbq') ?>
<?php Yii::app()->clientScript->registerScript('hashchange', '
  $(window).bind("hashchange", function(e) {
    var pane  = $.param.fragment();
    var $pane = $("#pane-" + pane);
    if(!$pane.length) {
      $pane = $(".pane:first");
      pane  = $pane.prop("id").substring(5);
    }
    
    $("#menu li.active").removeClass("active");
    $(".pane").not($pane).hide();
    
    $("#menu li:has(a[href=\"#" + pane + "\"])").addClass("active");
    $pane.show();
  }).trigger("hashchange");
') ?>

<?php $this->endContent() ?>