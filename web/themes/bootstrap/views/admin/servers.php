<?php
/* @var $this AdminController */
/* @var $server SBServer */
/* @var $servers SBServer */
?>

<?php if(Yii::app()->user->data->hasPermission('LIST_SERVERS')): ?>
    <section class="tab-pane fade" id="pane-list">
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
		array(
			'header'=>Yii::t('sourcebans', 'Hostname'),
			'headerHtmlOptions'=>array(
				'class'=>'ServerQuery_hostname',
			),
			'htmlOptions'=>array(
				'class'=>'ServerQuery_hostname',
			),
			'value'=>'Yii::t("sourcebans", "components.ServerQuery.loading") . " (" . $data->ip . ":" . $data->port . ")"',
		),
		array(
			'header'=>Yii::t('sourcebans', 'Players'),
			'headerHtmlOptions'=>array(
				'class'=>'ServerQuery_players',
			),
			'htmlOptions'=>array(
				'class'=>'ServerQuery_players',
			),
			'value'=>'Yii::t("sourcebans", "N/A")',
		),
		array(
			'class'=>'CButtonColumn',
			'buttons'=>array(
				'admins'=>array(
					'label'=>Yii::t('sourcebans', 'controllers.servers.admins.title'),
					'url'=>'Yii::app()->createUrl("servers/admins", array("id" => $data->primaryKey))',
					'imageUrl'=>false,
					'visible'=>'Yii::app()->user->data->hasPermission("LIST_ADMINS")',
				),
				'rcon'=>array(
					'label'=>Yii::t('sourcebans', 'RCON'),
					'url'=>'Yii::app()->createUrl("servers/rcon", array("id" => $data->primaryKey))',
					'imageUrl'=>false,
					'visible'=>'!empty($data->rcon) && Yii::app()->user->data->hasFlag(SM_RCON)',
				),
				'update'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("EDIT_SERVERS")',
				),
				'delete'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("DELETE_SERVERS")',
				),
			),
			'template'=>'{rcon} {admins} {update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("servers/edit", array("id" => $data->primaryKey))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("servers/delete", array("id" => $data->primaryKey))',
			'visible'=>Yii::app()->user->data->hasFlag(SM_RCON) || Yii::app()->user->data->hasPermission('DELETE_SERVERS', 'EDIT_SERVERS'),
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'rowHtmlOptionsExpression'=>'array(
		"data-key"=>$data->primaryKey,
	)',
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('ADD_SERVERS')): ?>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/servers/_form', array(
	'action'=>array('servers/add'),
	'model'=>$server,
)) ?>

    </section>
<?php endif ?>

<?php Yii::app()->clientScript->registerScript('admin_servers_queryServer', '
  function queryServer(id, callback) {
    if(typeof(id) == "function") {
      callback = id;
      id = 0;
    }
    else {
      id = id || 0;
    }
    
    $.getJSON("' . $this->createUrl('servers/info', array('id' => '__ID__')) . '".replace("__ID__", id), function(servers) {
      if(!$.isArray(servers)) {
        servers = [servers];
      }
      
      $.each(servers, function(i, server) {
        var $row = $("#servers-grid tr[data-key=\"" + server.id + "\"]");
        $row.find(".ServerQuery_hostname").html(server.error ? server.error.message : server.hostname);
        $row.find(".ServerQuery_players").text(server.error ? "' . Yii::t('sourcebans', 'N/A') . '" : server.numplayers + "/" + server.maxplayers);
        
        if(typeof(callback) == "function") {
          callback(server.id);
        }
      });
      
      if(!id && typeof(callback) == "function") {
        callback();
      }
    });
  }
  
  $("#servers-grid tr[data-key]").each(function() {
    var id = $(this).data("key");
    
    queryServer(id);
  });
') ?>