    <section class="pane" id="pane-list">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'servers-grid',
	'dataProvider'=>$servers->search(),
	'columns'=>array(
		array(
			'headerHtmlOptions'=>array(
				'class'=>'icon',
			),
			'htmlOptions'=>array(
				'class'=>'icon',
			),
			'name'=>'game.name',
			'type'=>'html',
			'value'=>'CHtml::image(Yii::app()->baseUrl . "/images/games/" . $data->game->icon, $data->game->name)',
		),
		'ip',
		'port',
		array(
			'class'=>'CButtonColumn',
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
    <section class="pane" id="pane-add">
<?php echo $this->renderPartial('/servers/_form', array(
	'model'=>$server,
)) ?>

    </section>