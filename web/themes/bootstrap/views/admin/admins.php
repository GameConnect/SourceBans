<?php
/* @var $this AdminController */
/* @var $actions SBAction */
/* @var $admin SBAdmin */
/* @var $admins SBAdmin */
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
    <section class="tab-pane fade" id="pane-import">
<?php echo $this->renderPartial('/admins/_import') ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('OVERRIDES')): ?>
    <section class="tab-pane fade" id="pane-overrides">
      <p><?php echo Yii::t('sourcebans', 'Here you can change the permissions on any command, either globally, or for a specific group, without editing plugin source code.') ?></p>
      <p><?php echo Yii::t('sourcebans', 'See the {link} for more details.', array('{link}' => '<a href="http://wiki.alliedmods.net/Overriding_Command_Access_(SourceMod)" target="_blank">SourceMod wiki</a>')) ?></p>
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'overrides-grid',
	'dataProvider'=>$overrides->search(),
	'columns'=>array(
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'type',
			'value'=>'($types = SBOverride::getTypes()) ? $types[$data->type] : null',
		),
		'name',
		array(
			'name'=>'flags',
			'type'=>'ntext',
			'value'=>'($flags = SourceBans::app()->flags) ? $flags[$data->flags] : null',
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'selectableRows'=>0,
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>
<?php endif ?>
    <section class="tab-pane fade" id="pane-actions">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'actions-grid',
	'dataProvider'=>$actions->search(),
	'columns'=>array(
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'name',
		),
		'message',
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'admin.name',
		),
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

    </section>