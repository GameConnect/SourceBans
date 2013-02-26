    <section class="pane" id="pane-list">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'groups-grid',
	'dataProvider'=>$groups->search(),
	'columns'=>array(
		'name',
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
<?php echo $this->renderPartial('/groups/_form', array(
	'model'=>$group,
)) ?>

    </section>