<table class="table table-bordered table-condensed docClass">
<colgroup>
	<col class="col-name" />
	<col class="col-value" />
</colgroup>
<?php if(!empty($view->controllerClass)): ?>
<tr>
  <th>Controller</th>
  <td><?php echo $this->renderTypeUrl($view->controllerClass); ?></td>
</tr>
<?php endif; ?>
<tr>
  <th>Package</th>
  <td><?php echo '{{index::'.$view->package.'|'.$view->package.'}}'; ?></td>
</tr>

<?php if(!empty($view->since)): ?>
<tr>
  <th>Since</th>
  <td><?php echo $view->since; ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($view->version)): ?>
<tr>
  <th>Version</th>
  <td><?php echo $view->version; ?></td>
</tr>
<?php endif; ?>
<tr>
  <th>Source Code</th>
  <td><?php echo $this->renderSourceLink($view->sourcePath); ?></td>
</tr>
<?php if(!empty($view->authors)): ?>
<tr>
  <th><?php echo (count($view->authors) == 1 ? "Author" : "Authors") ?></th>
  <td><?php echo CHtml::encode(implode(", ",$view->authors)); ?></td>
</tr>
<?php endif;?>
</table>

<div id="classDescription">
<?php echo $view->description; ?>
</div>