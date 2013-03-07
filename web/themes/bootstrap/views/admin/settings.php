<?php
/* @var $this AdminController */
/* @var $plugins SBPlugin[] */

$this->pageTitle=Yii::t('sourcebans', 'Settings');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Settings'),
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'Plugins'), 'url'=>'#plugins'),
);
?>
    <section class="tab-pane fade" id="pane-plugins">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'plugins-grid',
	'dataProvider'=>new CArrayDataProvider($plugins, array(
		'keyField'=>'class',
	)),
	'columns'=>array(
		array(
			'header'=>Yii::t('sourcebans', 'Name'),
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'name',
		),
		array(
			'header'=>Yii::t('sourcebans', 'Description'),
			'name'=>'description',
		),
		array(
			'header'=>Yii::t('sourcebans', 'Version'),
			'headerHtmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'name'=>'version',
		),
		array(
			'header'=>false,
			'headerHtmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'name'=>'enabled',
			'type'=>'html',
			'value'=>'CHtml::link(Yii::t("sourcebans", $data->enabled ? "Disable" : "Enable"), "#")',
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'rowHtmlOptionsExpression'=>'array(
		"class"=>$data->enabled ? "enabled" : "disabled",
	)',
	'selectableRows'=>0,
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>