    <section class="pane" id="pane-list">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'admins-grid',
	'dataProvider'=>$admins->search(),
	'columns'=>array(
		'name',
		'group.name',
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
<?php echo $this->renderPartial('/admins/_form', array(
	'model'=>$admin,
)) ?>

    </section>