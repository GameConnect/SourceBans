<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>(isset($archive) ? 'archived' : 'active') . '-protests-grid',
	'dataProvider'=>$model->search(array(
		'scopes'=>isset($archive) ? 'archived' : 'active',
	)),
	'columns'=>array(
		'ban.name',
		'ban.steam',
		'ban.ip',
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'name'=>'create_time',
			'type'=>'datetime',
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'nullDisplay'=>CHtml::tag('span',array('class'=>'null'),Yii::t('zii', 'Not set')),
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'rowHtmlOptionsExpression'=>'array(
		"data-id"=>$data->id,
	)',
	'selectableRows'=>0,
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>