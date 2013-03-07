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
			'value'=>'Yii::t("sourcebans", "Querying server... ({ip}:{port})", array("{ip}" => $data->ip, "{port}" => $data->port))',
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
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("servers/edit", array("id" => $data->primaryKey))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("servers/delete", array("id" => $data->primaryKey))',
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
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/servers/_form', array(
	'action'=>array('servers/add'),
	'model'=>$server,
)) ?>

    </section>

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