<?php
/* @var $this AdminController */
/* @var $game SBGame */
/* @var $games SBGame */

$this->pageTitle=Yii::t('sourcebans', 'Games');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Games'),
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'List games'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_GAMES')),
	array('label'=>Yii::t('sourcebans', 'Add game'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_GAMES')),
);
?>

<?php if(Yii::app()->user->data->hasPermission('LIST_GAMES')): ?>
    <section class="tab-pane fade" id="pane-list">
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
			'sortable'=>false,
			'type'=>'html',
			'value'=>'CHtml::image(Yii::app()->baseUrl . "/images/games/" . $data->icon, $data->name)',
		),
		array(
			'class'=>'CButtonColumn',
			'buttons'=>array(
				'update'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("EDIT_GAMES")',
				),
				'delete'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("DELETE_GAMES")',
				),
			),
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("games/edit", array("id" => $data->primaryKey))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("games/delete", array("id" => $data->primaryKey))',
			'visible'=>Yii::app()->user->data->hasPermission('DELETE_GAMES', 'EDIT_GAMES'),
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
<?php if(Yii::app()->user->data->hasPermission('ADD_GAMES')): ?>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/games/_form', array(
	'action'=>array('games/add'),
	'model'=>$game,
)) ?>

    </section>
<?php endif ?>