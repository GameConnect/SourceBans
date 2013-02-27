    <section>
      <?php echo CHtml::link(Yii::t('sourcebans', 'Advanced Search'),'#',array('class'=>'search-button')); ?>
      <div class="search-form" style="display:none">
      <?php $this->renderPartial('/bans/_search',array(
      	'model'=>$bans,
      )); ?>
      </div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'bans-grid',
	'dataProvider'=>$bans->search(array(
		'scopes' => $hideInactive ? 'active' : null,
		'with' => array('admin', 'country', 'server', 'server.game'),
	)),
	'columns'=>array(
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
			'name'=>'time',
			'type'=>'datetime',
		),
		'name',
		array(
			'header'=>Yii::t('sourcebans', 'Admin'),
			'headerHtmlOptions'=>array(
				'class'=>'SBAdmin_name',
			),
			'htmlOptions'=>array(
				'class'=>'SBAdmin_name',
			),
			'name'=>'admin.name',
			'value'=>'isset($data->admin) ? $data->admin->name : "CONSOLE"',
			'visible'=>!SourceBans::app()->settings->bans_hide_admin,
		),
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
	'afterAjaxUpdate'=>'js:createSections',
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'rowHtmlOptionsExpression'=>'array(
		"class"=>"header" . ($data->length && $data->time + $data->length * 60 < time() ? " expired" : ($data->unban_admin_id ? " unbanned" : "")),
		"data-key"=>$data->primaryKey,
		"data-name"=>$data->name,
		"data-steam"=>$data->steam,
		"data-ip"=>$data->ip,
		"data-datetime"=>Yii::app()->format->formatDatetime($data->time),
		"data-length"=>$data->length ? Yii::app()->format->formatLength($data->length*60) : Yii::t("sourcebans", "Permanent"),
		"data-reason"=>$data->reason,
		"data-admin-name"=>isset($data->admin) ? $data->admin->name : "CONSOLE",
		"data-server-id"=>$data->server->id,
	)',
	'selectableRows'=>0,
	'summaryText'=>CHtml::link(Yii::t('sourcebans', $hideInactive == 'true' ? 'Show inactive' : 'Hide inactive'), array('', 'hideinactive' => $hideInactive == 'true' ? 'false' : 'true'), array('class' => 'pull-left')) . '<em>' . Yii::t('sourcebans', 'Total bans') . ': ' . $total_bans . '</em>',
)) ?><!-- bans grid -->
    </section>
    
<script id="bans-section" type="text/x-template">
  <table class="table table-bordered table-condensed">
    <tbody>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Name') ?></th>
        <td><%=header.data("name") %></th>
      </tr>
      <tr>
        <th style="white-space: nowrap; width: 150px;">Steam ID</th>
        <td><%=header.data("steam") %></th>
      </tr>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'IP address') ?></th>
        <td><%=header.data("ip") %></th>
      </tr>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Invoked on') ?></th>
        <td><%=header.data("datetime") %></th>
      </tr>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Length') ?></th>
        <td><%=header.data("length") %></th>
      </tr>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Reason') ?></th>
        <td><%=header.data("reason") %></th>
      </tr>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Admin') ?></th>
        <td><%=header.data("adminName") %></th>
      </tr>
      <tr>
        <th><?php echo Yii::t('sourcebans', 'Server') ?></th>
        <td class="hostname_<%=header.data("serverId") %>">Querying server...</th>
      </tr>
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
') ?>

<?php Yii::app()->clientScript->registerScript('site_bans_createSections', '
  function createSections() {
    $("#bans-grid tr[data-key]").each(function(i, header) {
      $section = $("<tr class=\"row section\"><td colspan=\"" + header.cells.length + "\"><div class=\"span10\"></div></td></tr>").insertAfter($(header));
      
      $section.find("div").html($("#bans-section").template({
        header: $(header),
      }));
    });
    
    $(window).trigger("hashchange");
  }
  
  createSections();
') ?>