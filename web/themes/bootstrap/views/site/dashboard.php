    <div class="row">
    <section class="span12 intro">
      <h3><?php echo CHtml::encode(SourceBans::app()->settings->dashboard_title) ?></h3><!-- intro title -->
      <?php echo SourceBans::app()->settings->dashboard_text ?><!-- intro text -->
    </section>
    </div>
    
<?php $this->renderPartial('servers', array(
	'isDashboard' => true,
	'servers' => $servers,
)) ?>
    
    <div class="row">
    <section class="span6 bans">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'bans-grid',
	'dataProvider'=>$bans,
	'columns'=>array(
		array(
			'header'=>Yii::t('sourcebans', 'Game'),
			'headerHtmlOptions'=>array(
				'class'=>'icon',
			),
			'htmlOptions'=>array(
				'class'=>'icon',
			),
			'name'=>'server.game.name',
			'type'=>'html',
			'value'=>'CHtml::image(Yii::app()->baseUrl . "/images/games/" . (isset($data->server) ? $data->server->game->icon : "web.png"), isset($data->server) ? $data->server->game->name : "SourceBans")',
		),
		array(
			'headerHtmlOptions'=>array(
				'class'=>'datetime',
			),
			'htmlOptions'=>array(
				'class'=>'datetime',
			),
			'name'=>'time',
			'type'=>'datetime',
		),
		'name',
		array(
			'headerHtmlOptions'=>array(
				'class'=>'length',
			),
			'htmlOptions'=>array(
				'class'=>'length',
			),
			'name'=>'length',
			'value'=>'$data->length ? Yii::app()->format->formatLength($data->length*60) : Yii::t("sourcebans", "Permanent")',
		),
	),
	'cssFile'=>false,
	'enablePagination'=>false,
	'enableSorting'=>false,
	'itemsCssClass'=>'items table table-condensed table-hover',
	'rowHtmlOptionsExpression'=>'array(
		"data-key"=>$data->primaryKey,
	)',
	'selectionChanged'=>'js:function(grid) {
		var $header = $("#" + grid + " tr.selected");
		var id      = $header.data("key");
		
		location.href = "' . $this->createUrl('site/bans', array('#' => '__ID__')) . '".replace("__ID__", id);
	}',
	'summaryText'=>'<em>' . Yii::t('sourcebans', 'Total bans') . ': ' . $total_bans . '</em>',
)) ?><!-- bans grid -->
    </section>
    
    <section class="span6 blocks">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'blocks-grid',
	'dataProvider'=>$blocks,
	'columns'=>array(
		array(
			'htmlOptions'=>array(
				'class'=>'icon',
			),
			'type'=>'image',
			'value'=>'Yii::app()->baseUrl . "/images/blocked.png"',
		),
		array(
			'headerHtmlOptions'=>array(
				'class'=>'datetime',
			),
			'htmlOptions'=>array(
				'class'=>'datetime',
			),
			'name'=>'time',
			'type'=>'datetime',
		),
		'ban.name',
	),
	'cssFile'=>false,
	'enablePagination'=>false,
	'enableSorting'=>false,
	'itemsCssClass'=>'items table table-condensed table-hover',
	'selectableRows'=>0,
	'summaryText'=>'<em>' . Yii::t('sourcebans', 'Total stopped') . ': ' . $total_blocks . '</em>',
)) ?><!-- blocks grid -->
    </section>
    </div>