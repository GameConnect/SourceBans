<?php
/* @var $this DefaultController */
/* @var $ban SBBan */
/* @var $bans SBBan */
/* @var $comment SBComment */
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
		'with' => array('admin', 'commentsCount', 'server', 'server.game'),
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
			'headerHtmlOptions'=>array(
				'class'=>'SBAdmin_name span3',
			),
			'htmlOptions'=>array(
				'class'=>'SBAdmin_name span3',
			),
			'name'=>'admin.name',
			'value'=>'isset($data->admin) ? $data->admin->name : Yii::app()->params["consoleName"]',
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
		"data-datetime-expired"=>$data->isPermanent ? null : Yii::app()->format->formatDatetime($data->create_time+$data->length*60),
		"data-length"=>$data->isPermanent ? Yii::t("sourcebans", "Permanent") : Yii::app()->format->formatLength($data->length*60),
		"data-reason"=>$data->reason,
		"data-admin-name"=>isset($data->admin) ? $data->admin->name : Yii::app()->params["consoleName"],
		"data-server-id"=>$data->server_id,
		"data-community-id"=>$data->communityId,
		"data-comments-count"=>$data->commentsCount,
	)',
	'selectableRows'=>0,
	'summaryText'=>$summaryText,
)) ?><!-- bans grid -->
    </section>
    
<script id="bans-section" type="text/x-template">
  <table class="table table-condensed pull-left">
    <tbody>
      <tr>
        <th><?php echo $ban->getAttributeLabel('name') ?></th>
        <td><%=header.data("name") || nullDisplay %></td>
      </tr>
      <tr>
        <th><?php echo $ban->getAttributeLabel('steam') ?></th>
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
        <th><?php echo $ban->getAttributeLabel('ip') ?></th>
        <td><%=header.data("ip") || nullDisplay %></td>
      </tr>
<?php endif ?>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Invoked on') ?></th>
        <td><%=header.data("datetime") %></td>
      </tr>
<% if(header.data("datetimeExpired")) { %>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Expires on') ?></th>
        <td><%=header.data("datetimeExpired") %></td>
      </tr>
<% } %>
      <tr>
        <th><?php echo $ban->getAttributeLabel('length') ?></th>
        <td><%=header.data("length") %></td>
      </tr>
      <tr>
        <th><?php echo $ban->getAttributeLabel('reason') ?></th>
        <td><%=header.data("reason") || nullDisplay %></td>
      </tr>
<?php if(!(Yii::app()->user->isGuest && SourceBans::app()->settings->bans_hide_admin)): ?>
      <tr>
        <th><?php echo $ban->getAttributeLabel('admin.name') ?></th>
        <td><%=header.data("adminName") %></td>
      </tr>
<?php endif ?>
<% if(header.data("serverId")) { %>
      <tr>
        <th><?php echo $ban->getAttributeLabel('server_id') ?></th>
        <td class="ServerQuery_hostname"><?php echo Yii::t('sourcebans', 'components.ServerQuery.loading') ?></td>
      </tr>
<% } %>
    </tbody>
  </table>
  <div class="ban-menu pull-right">
<?php $this->widget('zii.widgets.CMenu', array(
	'items' => array_merge(array(
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
		array(
			'label' => Yii::t('sourcebans', 'Comments'),
			'url' => array('comments/index', 'object_type'=>SBComment::TYPE_BAN, 'object_id'=>'__ID__'),
			'itemOptions' => array('class' => 'ban-menu-comments'),
			'visible' => !Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission('ADD_BANS'),
		),
	), $this->menu),
	'htmlOptions' => array(
		'class' => 'nav nav-stacked nav-pills',
	),
)) ?>

  </div>
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

<?php Yii::app()->clientScript->registerScript('default_bans_hashchange', '
  $(window).bind("hashchange", function(e) {
    var id       = $.param.fragment();
    var $header  = $("#bans-grid tr[data-key=\"" + id + "\"]");
    var $section = $header.next("tr.section").find("div:first-child");
    
    $("#bans-grid > table.table-accordion > tbody > tr.selected").removeClass("selected");
    $("#bans-grid tr.section div:first-child").not($section).slideUp(200, "linear");
    if(!$header.length)
      return;
    
    $header.addClass("selected");
    $section.slideDown(200, "linear");
    $("#SBComment_object_id").val(id);
  });
  
  $(document).on("click.yiiGridView", "#bans-grid tr.header", function(e) {
    var $this     = $(this);
    location.hash = $this.hasClass("selected") ? 0 : $this.data("key");
  });
  $(document).on("click.yiiGridView", "#bans-grid tr.header :checkbox", function(e) {
    e.stopImmediatePropagation();
  });
') ?>

<?php Yii::app()->clientScript->registerScript('default_bans_createSections', '
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
      $section.find(".ban-menu-comments a").append(" (" + $(header).data("commentsCount") + ")");
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
    if(!confirm("' . Yii::t('zii', 'Are you sure you want to delete this item?') . '"))
      return false;
    
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

<?php Yii::app()->clientScript->registerScript('default_bans_queryServer', '
  $.getJSON("' . $this->createUrl('servers/info') . '", function(servers) {
    window.serverInfo = servers;
    
    updateSections();
  });
') ?>

<?php if(!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission('ADD_BANS')): ?>
<div aria-hidden="true" class="modal fade hide" id="comments-dialog" role="dialog">
  <div class="modal-header">
    <button aria-hidden="true" class="close" data-dismiss="modal" type="button">&times;</button>
    <h3><?php echo Yii::t('sourcebans', 'Comments') ?></h3>
  </div>
  <div class="modal-body">
  </div>
  <div class="modal-footer">
<?php $this->renderPartial('/comments/_form', array('model' => $comment)) ?>

  </div>
</div>

<?php Yii::app()->clientScript->registerScript('default_bans_commentsDialog', '
  $(document).on("click", ".ban-menu-comments a", function(e) {
    e.preventDefault();
    $("#comments-dialog .modal-body").load($(this).attr("href"), function(data) {
      this.scrollTop = this.scrollHeight - $(this).height();
      tinyMCE.execCommand("mceFocus", false, "SBComment_message");
      
      $("#comments-dialog").modal({
        backdrop: "static"
      });
    });
  });
  $("#comment-form").submit(function(e) {
    e.preventDefault();
    var $this = $(this);
    
    $.post($this.attr("action"), $this.serialize(), function(result) {
      if(!result)
        return;
      
      $this.find(":submit").attr("disabled", true);
      tinyMCE.activeEditor.setContent("");
      
      $("#bans-grid tr.selected").next("tr.section").find(".ban-menu-comments a").trigger("click");
      $("#' . $grid->id . '").yiiGridView("update");
    }, "json");
  });
') ?>
<?php endif ?>