<table class="table table-bordered table-condensed docClass">
<colgroup>
	<col class="col-name" />
	<col class="col-value" />
</colgroup>
<?php if(!empty($class->package)): ?>
<tr>
  <th>Package</th>
  <td><?php echo '{{index::'.$class->package.'|'.$class->package.'}}'; ?></td>
</tr>
<?php endif;?>
<tr>
  <th>Inheritance</th>
  <td><?php echo $this->renderInheritance($class); ?></td>
</tr>
<?php if(!empty($class->interfaces)): ?>
<tr>
  <th>Implements</th>
  <td><?php echo $this->renderImplements($class); ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($class->subclasses)): ?>
<tr>
  <th>Subclasses</th>
  <td><?php echo $this->renderSubclasses($class); ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($class->since)): ?>
<tr>
  <th>Since</th>
  <td><?php echo $class->since; ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($class->version)): ?>
<tr>
  <th>Version</th>
  <td><?php echo $class->version; ?></td>
</tr>
<?php endif; ?>
<?php if(!empty($class->views)): ?>
<tr>
  <th>Views</th>
  <td><?php echo $this->renderViews($class); ?></td>
</tr>
<?php endif; ?>
<tr>
  <th>Source Code</th>
  <td><?php echo $this->renderSourceLink($class->sourcePath); ?></td>
</tr>
<?php if(!empty($class->authors)): ?>
<tr>
  <th><?php echo (count($class->authors) == 1 ? "Author" : "Authors") ?></th>
  <td><?php echo CHtml::encode(implode(", ",$class->authors)); ?></td>
</tr>
<?php endif;?>
</table>

<div id="classDescription">
<?php echo $class->description; ?>
</div>