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
  <section class="tab-content span9" id="content">
<?php echo $content ?>

  </section>
</div>

<?php Yii::app()->clientScript->registerCoreScript('bbq') ?>
<?php Yii::app()->clientScript->registerScript('hashchange', '
  $(window).bind("hashchange", function(e) {
    var pane  = $.param.fragment().replace("/", "-"),
        $pane = $("#pane-" + pane);
    if(!$pane.length) {
      $pane = $(".tab-pane:first");
      if(!$pane.length)
        return;
      
      pane  = $pane.prop("id").substring(5);
    }
    
    var $active    = $(".tab-pane.active"),
    // Do not animate on first hashchange,
    // because $.support.transition is undefined until domready
        transition = $.support.transition && $pane.hasClass("fade");
    
    function next() {
      if($active.length) {
        $("#menu li.active").removeClass("active");
        $active.removeClass("active");
      }
      
      $("#menu li:has(a[href=\"#" + pane + "\"])").addClass("active");
      $pane.addClass("active");
      
      if(transition) {
        $pane[0].offsetWidth; // reflow for transition
      }
      $pane.addClass("in");
    }
    
    transition && $active.length
      ? $active.one($.support.transition.end, next)
      : next();
    
    $active.removeClass("in");
  }).trigger("hashchange");
') ?>

<?php $this->endContent() ?>