<?php if(empty($class->events)) return; ?>

<div class="summary docEvent">
<h3>Events</h3>

<p><a href="#" class="toggle">Hide inherited events</a></p>

<table class="table table-bordered table-condensed">
<colgroup>
	<col class="col-event" />
	<col class="col-description" />
	<col class="col-defined" />
</colgroup>
<tr>
  <th>Event</th><th>Description</th><th>Defined By</th>
</tr>
<?php foreach($class->events as $event): ?>
<tr<?php echo $event->isInherited?' class="inherited"':''; ?> id="<?php echo $event->name; ?>">
  <td><?php echo $this->renderSubjectUrl($event->definedBy,$event->name); ?></td>
  <td><?php echo $event->introduction; ?></td>
  <td><?php echo $this->renderTypeUrl($event->definedBy); ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>