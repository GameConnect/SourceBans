<div class="summary docProperty">
<h3><?php echo 'Uses Properties'; ?></h3>


<table class="table table-bordered table-condensed">
<colgroup>
	<col class="col-property" />
	<col class="col-type" />
	<col class="col-description" />
	<col class="col-defined" />
</colgroup>
<tr>
  <th>Property</th><th>Type</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($view->uses as $property): ?>
<tr id="<?php echo $property->name; ?>">
  <td><?php echo $this->renderSubjectUrl($view->package.".".$view->name,$property->name); ?></td>
  <td><?php echo $this->renderTypeUrl($property->type); ?></td>
  <td><?php echo $property->introduction; ?></td>
  <td><?php echo $this->renderTypeUrl($property->definedBy); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>