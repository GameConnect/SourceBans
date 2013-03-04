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
			'value'=>'implode("\n", $data->server_groups(array("order" => "name")))',
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("admins/edit", array("id" => $data->primaryKey))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("admins/delete", array("id" => $data->primaryKey))',
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/admins/_form', array(
	'action'=>array('admins/add'),
	'model'=>$admin,
)) ?>

    </section>