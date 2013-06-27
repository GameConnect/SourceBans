<div class="clearfix" id="header">
  <h1 class="pull-left"><a href="<?php echo Yii::app()->baseUrl ?>/">SourceBans API</a></h1>
  <div class="pull-right">
    <div class="search pull-left">
      <input accesskey="q" autofocus="autofocus" class="span5" id="search" name="q" placeholder="Search" type="search" />
    </div>
    <ul class="nav nav-pills pull-right">
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <?php echo $this->version ?><?php if(count($this->versions) > 1): ?> <b class="caret"></b><?php endif ?>

        </a>
<?php if(count($this->versions) > 1): ?>
        <ul class="dropdown-menu pull-right">
<?php foreach($this->versions as $version): ?>
<?php if($version !== $this->version): ?>
          <li><?php echo CHtml::link($version, array('view', 'version' => $version, 'page' => $this->topic, 'lang' => $this->language)) ?></li>
<?php endif ?>
<?php endforeach ?>
        </ul>
<?php endif ?>
      </li>
    </ul>
  </div>
</div>

<div id="content">
<?php echo $content ?>

</div>