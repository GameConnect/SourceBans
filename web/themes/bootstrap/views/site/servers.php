<?php
/* @var $this SiteController */
/* @var $servers SBServer */
?>
    <div class="row">
    <section class="servers span12">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'servers-grid',
	'dataProvider'=>$servers,
	'columns'=>array(
		array(
			'header'=>Yii::t('sourcebans', 'Game'),
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
			'header'=>'OS',
			'headerHtmlOptions'=>array(
				'class'=>'ServerQuery_OS icon',
			),
			'htmlOptions'=>array(
				'class'=>'ServerQuery_OS icon',
			),
			'type'=>'html',
			'value'=>'CHtml::image(Yii::app()->theme->baseUrl . "/images/loading.gif", Yii::t("sourcebans", "N/A"))',
		),
		array(
			'header'=>'VAC',
			'headerHtmlOptions'=>array(
				'class'=>'ServerQuery_VAC icon',
			),
			'htmlOptions'=>array(
				'class'=>'ServerQuery_VAC icon',
			),
			'type'=>'html',
			'value'=>'CHtml::image(Yii::app()->theme->baseUrl . "/images/loading.gif", Yii::t("sourcebans", "N/A"))',
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
			'header'=>Yii::t('sourcebans', 'Map'),
			'headerHtmlOptions'=>array(
				'class'=>'ServerQuery_map',
			),
			'htmlOptions'=>array(
				'class'=>'ServerQuery_map',
			),
			'value'=>'Yii::t("sourcebans", "N/A")',
		),
	),
	'cssFile'=>false,
	'enablePagination'=>false,
	'enableSorting'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'rowHtmlOptionsExpression'=>'array(
		"class"=>"header",
		"data-key"=>$data->primaryKey,
		"data-game-folder"=>$data->game->folder,
	)',
	'selectableRows'=>isset($isDashboard) ? 1 : 0,
	'selectionChanged'=>'js:function(grid) {
		var $header = $("#" + grid + " tr.selected");
		var id      = $header.data("key");
		
		location.href = "' . $this->createUrl('site/servers', array('#' => '__ID__')) . '".replace("__ID__", id);
	}',
	'summaryText'=>false,
)) ?><!-- servers grid -->
    </section>
    </div>

<?php if(!isset($isDashboard)): ?>
<script id="servers-section" type="text/x-template">
  <table class="table table-condensed table-hover pull-left" style="width: 430px">
    <thead>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Name') ?></th>
        <th class="nowrap text-right"><?php echo Yii::t('sourcebans', 'Score') ?></th>
        <th class="nowrap"><?php echo Yii::t('sourcebans', 'Time') ?></th>
      </tr>
    </thead>
    <tbody>
<% if(server.players.length) { %>
<% $.each(server.players, function(i, player) { %>
      <tr class="player" data-name="<%=player.name %>">
        <td><%=player.name %></td>
        <td class="text-right"><%=player.score %></td>
        <td class="nowrap"><%=player.time %></td>
      </tr>
<% }); %>
<% } else { %>
      <tr>
        <td class="text-center" colspan="3"><em><?php echo Yii::t('sourcebans', 'No players in the server') ?></em></td>
      </tr>
<% } %>
    </tbody>
  </table>
  <div class="pull-right">
    <img alt="<%=(server.error ? "<?php echo Yii::t('sourcebans', 'Unknown') ?>" : server.map) %>" class="map-image img-rounded" src="<%=(server.error || !server.map_image ? "<?php echo Yii::app()->baseUrl ?>/images/maps/unknown.jpg" : server.map_image) %>" />
    <div class="well text-center">
      <p><%=server.ip %>:<%=server.port %></p>
      <a class="btn btn-success" href="steam://connect/<%=server.ip %>:<%=server.port %>"><?php echo Yii::t('sourcebans', 'Join game') ?></a>
      <a class="btn btn-info" href="javascript:queryServer(<%=server.id %>)"><?php echo Yii::t('sourcebans', 'Refresh') ?></a>
    </div>
  </div>
</script>

<?php Yii::app()->clientScript->registerScript('site_servers_hashchange', '
  $(window).bind("hashchange", function(e) {
    $("#servers-grid > table.table-accordion > tbody > tr.selected").removeClass("selected");
    $("#servers-grid tr.section div:first-child").slideUp(200, "linear");
    
    var id      = $.param.fragment();
    var $header = $("#servers-grid tr[data-key=\"" + id + "\"]");
    if(!$header.length)
      return;
    
    var $section = $header.next("tr.section");
    $header.addClass("selected");
    $section.find("div:first-child").slideDown(200, "linear");
  });
  
  $(document).on("click.yiiGridView", "#servers-grid tr.header", function(e) {
    var $this     = $(this);
    location.hash = $this.hasClass("selected") ? 0 : $this.data("key");
  });
') ?>

<?php if(!Yii::app()->user->isGuest): ?>
<?php $this->widget('bootstrap.widgets.TbDropdown', array(
	'id' => 'player-menu',
	'items' => array_merge(array(
		array(
			'itemOptions' => array('class' => 'player-name'),
		),
		array(
			'label' => Yii::t('sourcebans', 'Kick player'),
			'url' => '#',
			'linkOptions' => array('id' => 'player-kick'),
			'visible' => Yii::app()->user->data->hasFlag(SM_KICK),
		),
		array(
			'label' => Yii::t('sourcebans', 'Ban player'),
			'url' => '#',
			'linkOptions' => array('id' => 'player-ban'),
			'visible' => Yii::app()->user->data->hasPermission('ADD_BANS'),
		),
		array(
			'divider' => true,
		),
		array(
			'label' => Yii::t('sourcebans', 'View Steam Profile'),
			'url' => '#',
			'linkOptions' => array('id' => 'player-profile'),
		),
	), $this->menu),
)) ?>

<?php Yii::app()->clientScript->registerScript('site_servers_playerMenu', '
  $(document).on("click", function() {
    $("#player-menu").hide();
  });
  $(document).on("contextmenu", "#servers-grid .player", function(e) {
    e.preventDefault();
    var name = $(this).data("name");
    
    $("#player-menu .player-name").text(name);
    $("#player-menu")
      .data("name", name)
      .css({
        top: event.pageY - 12,
        left: event.pageX + 2
      })
      .show();
  });
  
  $("#player-profile").click(function() {
    var id = $("#servers-grid tr.selected").data("key");
    
    $.post("' . $this->createUrl('servers/getProfile', array('id' => '__ID__')) . '".replace("__ID__", id), {
      name: $("#player-menu").data("name")
    }, function(data) {
      if(data.error)
        return;
      
      location.href = "http://steamcommunity.com/profiles/" + data.id;
    }, "json");
  });
  
  $("#player-kick").click(function() {
    var id = $("#servers-grid tr.selected").data("key");
    if(!confirm("' . Yii::t('sourcebans', 'Are you sure you want to kick {name}?') . '".replace("{name}", $("#player-menu").data("name"))))
	    return;
    
    $.post("' . $this->createUrl('servers/kick', array('id' => '__ID__')) . '".replace("__ID__", id), {
      name: $("#player-menu").data("name")
    }, function() {
      queryServer(id);
    });
  });
') ?>
<?php endif ?>
<?php endif ?>

<?php Yii::app()->clientScript->registerScript('site_servers_queryServer', '
  function queryServer(id, callback) {
    if(typeof(id) == "function") {
      callback = id;
      id = 0;
    }
    else {
      id = id || 0;
    }
    
    $.getJSON("' . $this->createUrl(isset($isDashboard) ? 'servers/info' : 'servers/query', array('id' => '__ID__')) . '".replace("__ID__", id), function(servers) {
      if(!$.isArray(servers)) {
        servers = [servers];
      }
      
      $.each(servers, function(i, server) {
        var $header = $("#servers-grid tr[data-key=\"" + server.id + "\"]");
        $header.find(".ServerQuery_OS").html(server.error ? "' . Yii::t('sourcebans', 'N/A') . '" : "<img src=\"' . Yii::app()->baseUrl . '/images/os_" + server.os + ".png\" alt=\"" + (server.os == "w" ? "Windows" : "Linux") + "\" />");
        $header.find(".ServerQuery_VAC").html(server.error || !server.secure ? "' . Yii::t('sourcebans', 'N/A') . '" : "<img src=\"' .  Yii::app()->baseUrl . '/images/secure.png\" alt=\"Valve Anti-Cheat\" />");
        $header.find(".ServerQuery_hostname").html(server.error ? server.error.message : server.hostname);
        $header.find(".ServerQuery_map").text(server.error ? "' . Yii::t('sourcebans', 'N/A') . '" : server.map);
        $header.find(".ServerQuery_players").text(server.error ? "' . Yii::t('sourcebans', 'N/A') . '" : server.numplayers + "/" + server.maxplayers);
        
        if(server.players) {
          var $section = $header.next("tr.section");
          if(!$section.length) {
            $section = $("<tr class=\"section\"><td colspan=\"" + $header[0].cells.length + "\"><div></div></td></tr>").insertAfter($header);
          }
          
          $section.find("div").html($("#servers-section").template({
            server: server,
          }));
        }
        
        // Store server information in header
        if(!server.error) {
          $header.data({
            map: server.map,
            maxplayers: server.maxplayers,
            numplayers: server.numplayers,
            os: server.os,
            secure: server.secure
          });
        }
        
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
    
    queryServer(id, function(id) {
      // If all servers are queried
      if(!id || id == $.param.fragment()) {
        $(window).trigger("hashchange");
      }
    });
  });
') ?>