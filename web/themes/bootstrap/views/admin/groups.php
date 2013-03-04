    <section class="tab-pane fade" id="pane-list">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'server-groups-grid',
	'dataProvider'=>$server_groups->search(),
	'columns'=>array(
		array(
			'header'=>Yii::t('sourcebans', 'Server group'),
			'name'=>'name',
		),
		'immunity',
		'adminsCount',
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("groups/edit", array("id" => $data->primaryKey, "type" => "server"))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("groups/delete", array("id" => $data->primaryKey, "type" => "server"))',
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
		'adminsCount',
		array(
			'class'=>'CButtonColumn',
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("groups/edit", array("id" => $data->primaryKey, "type" => "web"))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("groups/delete", array("id" => $data->primaryKey, "type" => "web"))',
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
	'model'=>$server_group,
)) ?>
<?php echo $this->renderPartial('/groups/_web_form', array(
	'model'=>$group,
)) ?>

    </section>

<?php Yii::app()->clientScript->registerScript('type_change', '
  $("#type").change(function() {
    $form = $(this).val() == "web" ? $("#group-form") : $("#server-group-form");
    $("#group-form, #server-group-form").not($form).slideUp(250);
    $form.slideDown(250);
  }).trigger("change");
') ?>