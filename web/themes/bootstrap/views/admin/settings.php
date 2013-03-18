<?php
/* @var $this AdminController */
/* @var $plugins SBPlugin[] */
?>
    <section class="tab-pane fade" id="pane-settings">
<?php echo $this->renderPartial('/settings/_form') ?>

    </section>
    <section class="tab-pane fade" id="pane-plugins">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'plugins-grid',
	'dataProvider'=>new CArrayDataProvider($plugins, array(
		'keyField'=>'class',
	)),
	'columns'=>array(
		array(
			'header'=>Yii::t('sourcebans', 'Name'),
			'name'=>'name',
			'value'=>'!empty($data->name) ? $data->name : $data->class',
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
			'header'=>Yii::t('sourcebans', 'Author'),
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'author',
			'type'=>'raw',
			'value'=>'!empty($data->url) ? CHtml::link(CHtml::encode($data->author), $data->url, array("target"=>"_blank")) : CHtml::encode($data->author)',
		),
		array(
			'header'=>false,
			'headerHtmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'name'=>'settings',
			'type'=>'raw',
			'value'=>'$data->status && $data->getViewFile("settings") ? CHtml::link(Yii::t("sourcebans", "Settings"), array("plugins/settings", "id"=>$data->id)) : ""',
		),
		array(
			'header'=>false,
			'headerHtmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'name'=>'status',
			'type'=>'raw',
			'value'=>'CHtml::link(Yii::t("sourcebans", $data->action), "#", array("class"=>"link-action", "data-action"=>strtolower($data->action)))',
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'rowHtmlOptionsExpression'=>'array(
		"class"=>($data->isEnabled ? "enabled" : "disabled") . (empty($data->name) ? " error" : ""),
		"data-id"=>$data->id,
	)',
	'selectableRows'=>0,
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>

<?php Yii::app()->clientScript->registerCoreScript('jquery.ui') ?>
<?php Yii::app()->clientScript->registerScript('plugins_settings', '
  $(document).on("click", "#plugins-grid .link-action", function(e) {
    e.preventDefault();
    var data = {
      action: $(this).data("action"),
      id: $(this).parents("tr").data("id")
    };
    if(data["action"] == "uninstall" && !confirm("' . Yii::t('sourcebans', 'Are you sure you want to uninstall this plugin?\nThis will delete all its data!') . '"))
      return;
    
    $.post("' . $this->createUrl('plugins/__ACTION__', array('id' => '__ID__')) . '".replace(/__(\w+)__/g, function(str, key) {
      return data[key.toLowerCase()] || str;
    }), function(result) {
      if(!result)
        return;
      
      $.fn.yiiGridView.update("plugins-grid");
    });
  });
') ?>