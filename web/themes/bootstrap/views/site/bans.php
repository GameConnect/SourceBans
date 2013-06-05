<?php
/* @var $this SiteController */
/* @var $bans SBBan */
/* @var $hideInactive string */
/* @var $search string */
/* @var $total_bans integer */
?>

<?php $summaryText = CHtml::link($hideInactive == 'true' ? Yii::t('sourcebans', 'Show inactive bans') : Yii::t('sourcebans', 'Hide inactive bans'), array('', 'hideinactive' => $hideInactive == 'true' ? 'false' : 'true')) . ' | <em>' . Yii::t('sourcebans', 'Total bans') . ': ' . $total_bans . '</em>'; ?>
<?php if(SourceBans::app()->settings->bans_public_export || (!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("OWNER"))): ?>
<?php $summaryText = '<div class="pull-left">' . CHtml::link(Yii::t('sourcebans', 'Export permanent Steam ID bans'), array('bans/export', 'type' => 'steam')) . ' | ' . CHtml::link(Yii::t('sourcebans', 'Export permanent IP address bans'), array('bans/export', 'type' => 'ip')) . '</div>' . $summaryText; ?>
<?php endif ?>

    <section>
      <div class="container" style="margin-bottom: 1em; width: 500px;">
      <?php echo CHtml::link(Yii::t('sourcebans', 'Advanced search'),'#',array('class'=>'search-button', 'style'=>'margin-left: 180px')); ?>
      <div class="search-form" style="display:none">
      <?php $this->renderPartial('/bans/_search',array(
      	'model'=>$bans,
      )); ?>
      </div><!-- search-form -->
      </div>

<?php $grid=$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'bans-grid',
	'dataProvider'=>$bans->search(array(
		'scopes' => $hideInactive ? 'active' : null,
		'search' => $search,
		'with' => array('admin', 'server', 'server.game'),
	)),
	'columns'=>array(
		array(
			'class'=>'CCheckBoxColumn',
			'selectableRows'=>2,
			'visible'=>!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission('DELETE_BANS'),
		),
		array(
			'header'=>Yii::t('sourcebans', 'Game') . '/' . Yii::t('sourcebans', 'Country'),
			'headerHtmlOptions'=>array(
				'class'=>'icon',
			),
			'htmlOptions'=>array(
				'class'=>'icon',
			),
			'type'=>'html',
			'value'=>'CHtml::image(Yii::app()->baseUrl . "/images/games/" . (isset($data->server) ? $data->server->game->icon : "web.png"), isset($data->server) ? $data->server->game->name : "SourceBans") . "&nbsp;" . (isset($data->country) ? CHtml::image(Yii::app()->baseUrl . "/images/countries/" . $data->country->code . ".gif", $data->country->name) : CHtml::image(Yii::app()->baseUrl . "/images/countries/unknown.gif", Yii::t("sourcebans", "Unknown")))',
		),
		array(
			'headerHtmlOptions'=>array(
				'class'=>'datetime',
			),
			'htmlOptions'=>array(
				'class'=>'datetime',
			),
			'name'=>'create_time',
			'type'=>'datetime',
		),
		'name',
		array(
			'header'=>Yii::t('sourcebans', 'Admin'),
			'headerHtmlOptions'=>array(
				'class'=>'SBAdmin_name span3',
			),
			'htmlOptions'=>array(
				'class'=>'SBAdmin_name span3',
			),
			'name'=>'admin.name',
			'value'=>'isset($data->admin) ? $data->admin->name : "CONSOLE"',
			'visible'=>!(Yii::app()->user->isGuest && SourceBans::app()->settings->bans_hide_admin),
		),
		array(
			'headerHtmlOptions'=>array(
				'class'=>'length',
			),
			'htmlOptions'=>array(
				'class'=>'length',
			),
			'name'=>'length',
			'value'=>'$data->isPermanent ? Yii::t("sourcebans", "Permanent") : Yii::app()->format->formatLength($data->length*60)',
		),
	),
	'afterAjaxUpdate'=>'js:createSections',
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'nullDisplay'=>CHtml::tag('span', array('class'=>'null'), Yii::t('zii', 'Not set')),
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'rowHtmlOptionsExpression'=>'array(
		"class"=>"header" . ($data->isExpired ? " expired" : ($data->isUnbanned ? " unbanned" : "")),
		"data-key"=>$data->primaryKey,
		"data-name"=>$data->name,
		"data-steam"=>$data->steam,
		"data-ip"=>$data->ip,
		"data-datetime"=>Yii::app()->format->formatDatetime($data->create_time),
		"data-length"=>$data->isPermanent ? Yii::t("sourcebans", "Permanent") : Yii::app()->format->formatLength($data->length*60),
		"data-reason"=>$data->reason,
		"data-admin-name"=>isset($data->admin) ? $data->admin->name : "CONSOLE",
		"data-server-id"=>$data->server_id,
		"data-community-id"=>$data->communityId,
	)',
	'selectableRows'=>0,
	'summaryText'=>$summaryText,
)) ?><!-- bans grid -->
    </section>
    
<script id="bans-section" type="text/x-template">
  <table class="table table-bordered table-condensed">
    <tbody>
      <tr>
        <th style="white-space: nowrap; width: 150px;"><?php echo Yii::t('sourcebans', 'Name') ?></th>
        <td><%=header.data("name") || nullDisplay %></td>
        <td class="ban-menu" rowspan="7">
<?php $this->widget('zii.widgets.CMenu', array(
	'items' => array(
		array(
			'label' => Yii::t('sourcebans', 'Edit'),
			'url' => array('bans/edit', 'id'=>'__ID__'),
			'visible' => !Yii::app()->user->isGuest,
		),
		array(
			'label' => Yii::t('sourcebans', 'Unban'),
			'url' => '#',
			'itemOptions' => array('class' => 'ban-menu-unban'),
			'visible' => !Yii::app()->user->isGuest,
		),
		array(
			'label' => Yii::t('sourcebans', 'Delete'),
			'url' => array('bans/delete', 'id'=>'__ID__'),
			'itemOptions' => array('class' => 'ban-menu-delete'),
			'visible' => !Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission('DELETE_BANS'),
		),
	),
	'htmlOptions' => array(
		'class' => 'nav nav-stacked nav-tabs',
	),
)) ?>
        </td>
      </tr>
      <tr>
        <th>Steam ID</th>
        <td>
          <%=header.data("steam") || nullDisplay %>
<% if(header.data("communityId")) { %>
          (<a href="http://steamcommunity.com/profiles/<%=header.data("communityId") %>" target="_blank"><?php echo Yii::t('sourcebans', 'View Steam Profile') ?></a>)
        </td>
      </tr>
<% } %>
        </td>
      </tr>
<?php if(!(Yii::app()->user->isGuest && SourceBans::app()->settings->bans_hide_ip)): ?>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'IP address') ?></th>
        <td><%=header.data("ip") || nullDisplay %></td>
      </tr>
<?php endif ?>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Invoked on') ?></th>
        <td><%=header.data("datetime") %></td>
      </tr>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Length') ?></th>
        <td><%=header.data("length") %></td>
      </tr>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Reason') ?></th>
        <td><%=header.data("reason") || nullDisplay %></td>
      </tr>
      <tr>
<?php if(!(Yii::app()->user->isGuest && SourceBans::app()->settings->bans_hide_admin)): ?>
        <th><?php echo Yii::t('sourcebans', 'Admin') ?></th>
        <td><%=header.data("adminName") %></td>
      </tr>
<?php endif ?>
<% if(header.data("serverId")) { %>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Server') ?></th>
        <td class="ServerQuery_hostname"><?php echo Yii::t('sourcebans', 'components.ServerQuery.loading') ?></td>
      </tr>
<% } %>
    </tbody>
  </table>
</script>

<?php Yii::app()->clientScript->registerScript('search', "
  $('.search-button').click(function(){
  	$('.search-form').slideToggle();
  	return false;
  });
  $('.search-form form').submit(function(){
  	$('#bans-grid').yiiGridView('update', {
  		data: $(this).serialize()
  	});
  	return false;
  });
"); ?>

<?php Yii::app()->clientScript->registerScript('site_bans_hashchange', '
  $(window).bind("hashchange", function(e) {
    var id       = $.param.fragment();
    var $header  = $("#bans-grid tr[data-key=\"" + id + "\"]");
    var $section = $header.next("tr.section").find("div");
    
    $("#bans-grid > table.table-accordion > tbody > tr.selected").removeClass("selected");
    $("#bans-grid tr.section div").not($section).slideUp(200, "linear");
    if(!$header.length)
      return;
    
    $header.addClass("selected");
    $section.slideDown(200, "linear");
  });
  
  $(document).on("click.yiiGridView", "#bans-grid tr.header", function(e) {
    var $this     = $(this);
    location.hash = $this.hasClass("selected") ? 0 : $this.data("key");
  });
  $(document).on("click.yiiGridView", "#bans-grid tr.header :checkbox", function(e) {
    e.stopImmediatePropagation();
  });
') ?>

<?php Yii::app()->clientScript->registerScript('site_bans_createSections', '
  function createSections() {
    var nullDisplay = "' . addslashes($grid->nullDisplay) . '";
    
    $("#bans-grid tr[data-key]").each(function(i, header) {
      $section = $("<tr class=\"section\"><td colspan=\"" + header.cells.length + "\"><div></div></td></tr>").insertAfter($(header));
      
      $section.find("div").html($("#bans-section").template({
        header: $(header),
        nullDisplay: nullDisplay
      }));
      $section.find("a").each(function() {
        this.href = this.href.replace("__ID__", $(header).data("key"));
      });
      if($(header).hasClass("expired") || $(header).hasClass("unbanned")) {
        $section.find(".ban-menu-unban").addClass("disabled");
      }
      else {
        $section.find(".ban-menu-unban a").prop("rel", $(header).data("key"));
      }
    });
    
    updateSections();
    $(window).trigger("hashchange");
  }
  function updateSections() {
    if(typeof(window.serverInfo) == "undefined")
      return;
    
    $.each(window.serverInfo, function(i, server) {
      var $section = $("#bans-grid tr[data-server-id=\"" + server.id + "\"]").next("tr.section");
      $section.find(".ServerQuery_hostname").html(server.error ? server.error.message : server.hostname);
      $("#SBBan_server_id option[value=\"" + server.id + "\"]").html(server.error ? server.error.message : server.hostname);
    });
  }
  
  $(document).on("click", ".ban-menu-unban a", function(e) {
    if($(this).parents("li").hasClass("disabled"))
      return;
    
    $.post("' . $this->createUrl('bans/unban', array("id" => "__ID__")) . '".replace("__ID__", $(this).prop("rel")), {
      reason: ""
    }, function() {
  	  $("#' . $grid->id . '").yiiGridView("update");
    });
  });
  $(document).on("click", ".ban-menu-delete a", function(e) {
    if(!confirm("' . Yii::t('zii', 'Are you sure you want to delete this item?') . '")) return false;
    $("#' . $grid->id . '").yiiGridView("update", {
      type: "POST",
      url: $(this).attr("href"),
      success: function(data) {
        $("#' . $grid->id . '").yiiGridView("update");
      }
    });
    return false;
  });
  
  createSections();
') ?>

<?php Yii::app()->clientScript->registerScript('site_bans_queryServer', '
  $.getJSON("' . $this->createUrl('servers/info') . '", function(servers) {
    window.serverInfo = servers;
    
    updateSections();
  });
') ?>