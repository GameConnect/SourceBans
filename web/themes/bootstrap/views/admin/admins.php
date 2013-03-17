<?php
/* @var $this AdminController */
/* @var $admin SBAdmin */
/* @var $admins SBAdmin */

$this->pageTitle=Yii::t('sourcebans', 'Admins');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Admins'),
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'List admins'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_ADMINS')),
	array('label'=>Yii::t('sourcebans', 'Add admin'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_ADMINS')),
);
?>

<?php if(Yii::app()->user->data->hasPermission('LIST_ADMINS')): ?>
    <section class="tab-pane fade" id="pane-list">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'admins-grid',
	'dataProvider'=>$admins->search(),
	'columns'=>array(
		'name',
		'group.name',
		array(
			'name'=>'server_groups.name',
			'type'=>'ntext',
			'value'=>'($server_groups = $data->server_groups(array("order" => "name"))) ? implode("\n", $server_groups) : null',
		),
		array(
			'class'=>'CButtonColumn',
			'buttons'=>array(
				'update'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("EDIT_ADMINS")',
				),
				'delete'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("DELETE_ADMINS")',
				),
			),
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("admins/edit", array("id" => $data->primaryKey))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("admins/delete", array("id" => $data->primaryKey))',
			'visible'=>Yii::app()->user->data->hasPermission('DELETE_ADMINS', 'EDIT_ADMINS'),
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'nullDisplay'=>CHtml::tag('span',array('class'=>'null'),Yii::t('sourcebans', 'None')),
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('ADD_ADMINS')): ?>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/admins/_form', array(
	'action'=>array('admins/add'),
	'model'=>$admin,
)) ?>

    </section>
<?php endif ?>