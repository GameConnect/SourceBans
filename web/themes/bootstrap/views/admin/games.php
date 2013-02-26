    <section class="pane" id="pane-list">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'games-grid',
	'dataProvider'=>$games->search(),
	'columns'=>array(
		'name',
		'folder',
		array(
			'headerHtmlOptions'=>array(
				'class'=>'icon',
			),
			'htmlOptions'=>array(
				'class'=>'icon',
			),
			'name'=>'icon',
			'type'=>'html',
			'value'=>'CHtml::image(Yii::app()->baseUrl . "/images/games/" . $data->icon, $data->name)',
		),
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
<?php echo $this->renderPartial('/games/_form', array(
	'model'=>$game,
)) ?>

    </section>