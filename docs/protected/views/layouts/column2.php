<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/main') ?>
<div class="span9 pull-right" id="content">
<?php echo $content ?>

</div>
<div class="span3" id="sidebar">
	<?php
		$this->beginWidget('zii.widgets.CPortlet', array(
			'title'=>'Operations',
		));
		$this->widget('zii.widgets.CMenu', array(
			'items'=>$this->menu,
			'htmlOptions'=>array('class'=>'operations'),
		));
		$this->endWidget();
	?>
</div>
<?php $this->endContent() ?>