<?php
/* @var $this AdminController */
/* @var $actions SBAction */
/* @var $admin SBAdmin */
/* @var $admins SBAdmin */
?>

<?php if(Yii::app()->user->data->hasPermission('LIST_ADMINS')): ?>
    <section class="tab-pane fade" id="pane-list">
<?php $grid=$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'admins-grid',
	'dataProvider'=>$admins->search(),
	'columns'=>array(
		array(
			'class'=>'CCheckBoxColumn',
			'selectableRows'=>2,
		),
		'name',
		array(
			'headerHtmlOptions'=>array(
				'width'=>'192',
			),
			'name'=>'group.name',
		),
		array(
			'headerHtmlOptions'=>array(
				'width'=>'192',
			),
			'name'=>'server_groups.name',
			'type'=>'ntext',
			'value'=>'($server_groups = $data->server_groups(array("order" => "name"))) ? implode("\n", $server_groups) : null',
		),
		array(
			'class'=>'CButtonColumn',
			'buttons'=>array(
				'update'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("EDIT_ADMINS")',
				),
				'delete'=>array(
					'visible'=>'Yii::app()->user->data->hasPermission("DELETE_ADMINS")',
				),
			),
			'template'=>'{update} {delete}',
			'updateButtonLabel'=>Yii::t('sourcebans', 'Edit'),
			'updateButtonUrl'=>'Yii::app()->createUrl("admins/edit", array("id" => $data->primaryKey))',
			'deleteButtonUrl'=>'Yii::app()->createUrl("admins/delete", array("id" => $data->primaryKey))',
			'visible'=>Yii::app()->user->data->hasPermission('DELETE_ADMINS', 'EDIT_ADMINS'),
		),
	),
	'afterAjaxUpdate'=>'js:createSections',
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'nullDisplay'=>CHtml::tag('span',array('class'=>'null'),Yii::t('sourcebans', 'None')),
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'rowHtmlOptionsExpression'=>'array(
		"class"=>"header",
		"data-key"=>$data->primaryKey,
		"data-login-time"=>Yii::app()->format->formatDatetime($data->login_time),
		"data-community-id"=>$data->communityId,
		"data-flags"=>$data->flags,
		"data-immunity"=>$data->immunity,
		"data-permissions"=>isset($data->group) ? CJavaScript::encode(array_values(CHtml::listData($data->group->permissions,"name","name"))) : null,
	)',
	'selectionChanged'=>'js:function(grid) {
		var $header  = $("#" + grid + " tr.selected");
		var $section = $header.next("tr.section").find("div:first-child");
		
		$("#" + grid + " tr.section div:first-child").not($section).slideUp(200, "linear");
		if(!$header.length)
			return;
		
		$section.slideDown(200, "linear");
	}',
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('ADD_ADMINS')): ?>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/admins/_form', array(
	'action'=>array('admins/add'),
	'model'=>$admin,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-import">
<?php echo $this->renderPartial('/admins/_import') ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('OVERRIDES')): ?>
    <section class="tab-pane fade" id="pane-overrides">
      <p><?php echo Yii::t('sourcebans', 'Here you can change the permissions on any command, either globally, or for a specific group, without editing plugin source code.') ?></p>
      <p><?php echo Yii::t('sourcebans', 'See the {link} for more details.', array('{link}' => '<a href="http://wiki.alliedmods.net/Overriding_Command_Access_(SourceMod)" target="_blank">SourceMod wiki</a>')) ?></p>
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'overrides-grid',
	'dataProvider'=>$overrides->search(),
	'columns'=>array(
		array(
			//'footer'=>CHtml::dropDownList('SBOverride[type]', null, SBOverride::getTypes()),
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'type',
			'value'=>'($types = SBOverride::getTypes()) ? $types[$data->type] : null',
		),
		array(
			//'footer'=>CHtml::textField('SBOverride[name]'),
			'name'=>'name',
		),
		array(
			//'footer'=>CHtml::checkBoxList('SBOverride[flags]', null, SourceBans::app()->flags),
			'name'=>'flags',
			'type'=>'ntext',
			'value'=>'($flags = SourceBans::app()->flags) ? $flags[$data->flags] : null',
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'selectableRows'=>0,
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>
<?php endif ?>
    <section class="tab-pane fade" id="pane-actions">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'actions-grid',
	'dataProvider'=>$actions->search(),
	'columns'=>array(
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'name',
		),
		'message',
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap',
			),
			'name'=>'admin.name',
		),
		array(
			'headerHtmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'htmlOptions'=>array(
				'class'=>'nowrap text-right',
			),
			'name'=>'create_time',
			'type'=>'datetime',
		),
	),
	'cssFile'=>false,
	'itemsCssClass'=>'items table table-accordion table-condensed table-hover',
	'pager'=>array(
		'class'=>'bootstrap.widgets.TbPager',
	),
	'pagerCssClass'=>'pagination pagination-right',
	'rowHtmlOptionsExpression'=>'array(
		"data-id"=>$data->id,
	)',
	'selectableRows'=>0,
	'summaryCssClass'=>'',
	'summaryText'=>false,
)) ?>

    </section>
    
<script id="admins-section" type="text/x-template">
      <div class="row-fluid">
        <div class="span6">
          <h3><?php echo Yii::t('sourcebans', 'Server permissions') ?></h3>
<% if(header.data("flags")) { %>
          <ul>
<% for(var flag in flags) { %>
<% if(flag != "<?php echo SM_ROOT ?>" && (header.data("flags").indexOf("<?php echo SM_ROOT ?>") != -1 || header.data("flags").indexOf(flag) != -1)) { %>
            <li><%=flags[flag] %></li>
<% } %>
<% } %>
          </ul>
<% } else { %>
          <p><%=nullDisplay %></p>
<% } %>
        </div>
        <div class="span6">
          <h3><?php echo Yii::t('sourcebans', 'Web permissions') ?></h3>
<% if(header.data("permissions")) { %>
          <ul>
<% for(var permission in permissions) { %>
<% if(permission != "OWNER" && (header.data("permissions").indexOf("OWNER") != -1 || header.data("permissions").indexOf(permission) != -1)) { %>
            <li><%=permissions[permission] %></li>
<% } %>
<% } %>
          </ul>
<% } else { %>
          <p><%=nullDisplay %></p>
<% } %>
        </div>
</script>

<?php Yii::app()->clientScript->registerScript('admin_admins_createSections', '
  var flags = ' . CJavaScript::encode(SourceBans::app()->flags->toArray()) . ',
      permissions = ' . CJavaScript::encode(SourceBans::app()->permissions->toArray()) . ';
  
  function createSections() {
    var nullDisplay = "' . addslashes($grid->nullDisplay) . '";
    
    $("#admins-grid tr[data-key]").each(function(i, header) {
      $section = $("<tr class=\"section\"><td colspan=\"" + header.cells.length + "\"><div></div></td></tr>").insertAfter($(header));
      
      $section.find("div").html($("#admins-section").template({
        header: $(header),
        nullDisplay: nullDisplay
      }));
      $section.find("a").each(function() {
        this.href = this.href.replace("__ID__", $(header).data("key"));
      });
    });
  }
  
  createSections();
') ?>