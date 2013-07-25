<?php
/* @var $this CommsController */
/* @var $plugin CommunityBansPlugin */
/* @var $dataProvider CArrayDataProvider */
?>

<form action="" method="post">

<?php $grid=$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'communityFriends-grid',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array(
			'class'=>'CCheckBoxColumn',
		),
		array(
			'header'=>'',
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'avatar',
			'type'=>'image',
		),
		array(
			'header'=>Yii::t('sourcebans', 'Name'),
			'name'=>'personaname',
			'type'=>'text',
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{view}',
			'viewButtonImageUrl'=>false,
			'viewButtonLabel'=>Yii::t('sourcebans', 'View Steam Profile'),
			'viewButtonOptions'=>array('target' => '_blank'),
			'viewButtonUrl'=>'$data["profileurl"]',
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'selectableRows'=>2,
)) ?><!-- communityFriends grid -->

<?php $this->widget('bootstrap.widgets.TbButton',array(
	'buttonType'=>'submit',
	'label'=>Yii::t('sourcebans', 'Ban'),
	'type'=>'danger',
)) ?>

<?php $this->widget('bootstrap.widgets.TbButton',array(
	'label'=>Yii::t('sourcebans', 'Refresh'),
	'type'=>'success',
	'url'=>'javascript:updateGridView()',
)) ?>

</form>

<?php Yii::app()->clientScript->registerScript('communityBans_friends', '
  function updateGridView() {
    $.fn.yiiGridView.update("' . $grid->id . '");
  }
  updateGridView();
') ?>