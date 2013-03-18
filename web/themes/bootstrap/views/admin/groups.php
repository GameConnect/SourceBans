<?php
/* @var $this AdminController */
/* @var $group SBGroup */
/* @var $groups SBGroups */
/* @var $server_group SBServerGroup */
/* @var $server_groups SBServerGroups */
?>

<?php if(Yii::app()->user->data->hasPermission('LIST_GROUPS')): ?>
    <section class="tab-pane fade" id="pane-list">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'server-groups-grid',
	'dataProvider'=>$server_groups->search(),
	'columns'=>array(
		array(
			'header'=>Yii::t('sourcebans', 'Server group'),
			'name'=>'name',
		),
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'immunity',
		),
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'adminsCount',
		),
		array(
			'class'=>'CButtonColumn',
			'buttons'=>array(
				'update'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("EDIT_GROUPS")',
				),
				'delete'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("DELETE_GROUPS")',
				),
			),
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("groups/edit", array("id" => $data->primaryKey, "type" => "server"))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("groups/delete", array("id" => $data->primaryKey, "type" => "server"))',
			'visible'=>Yii::app()->user->data->hasPermission('DELETE_GROUPS', 'EDIT_GROUPS'),
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

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'groups-grid',
	'dataProvider'=>$groups->search(),
	'columns'=>array(
		array(
			'header'=>Yii::t('sourcebans', 'Web group'),
			'name'=>'name',
		),
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'adminsCount',
		),
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("groups/edit", array("id" => $data->primaryKey, "type" => "web"))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("groups/delete", array("id" => $data->primaryKey, "type" => "web"))',
			'visible'=>Yii::app()->user->data->hasPermission('DELETE_GROUPS', 'EDIT_GROUPS'),
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
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('ADD_GROUPS')): ?>
    <section class="tab-pane fade" id="pane-add">
      <form class="form-horizontal">
        <div class="control-group">
          <label class="control-label required" for="type"><?php echo Yii::t('sourcebans', 'Type') ?></label>
          <div class="controls">
            <select id="type" name="type">
              <option value="server"><?php echo Yii::t('sourcebans', 'Server group') ?></option>
              <option value="web"><?php echo Yii::t('sourcebans', 'Web group') ?></option>
            </select>
          </div>
        </div>
      </form>
<?php echo $this->renderPartial('/groups/_server_form', array(
	'action'=>array('groups/add', 'type'=>'server'),
	'model'=>$server_group,
)) ?>
<?php echo $this->renderPartial('/groups/_web_form', array(
	'action'=>array('groups/add', 'type'=>'web'),
	'model'=>$group,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-import">
<?php echo $this->renderPartial('/groups/_import') ?>

    </section>
<?php endif ?>

<?php Yii::app()->clientScript->registerScript('type_change', '
  $("#type").change(function() {
    $form = $(this).val() == "web" ? $("#group-form") : $("#server-group-form");
    $("#group-form, #server-group-form").not($form).slideUp(250);
    $form.slideDown(250);
  }).trigger("change");
') ?>