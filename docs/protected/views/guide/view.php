<div class="clearfix" id="header">
  <h1 class="pull-left"><a href="<?php echo Yii::app()->baseUrl ?>/">SourceBans User Guide</a></h1>
  <ul class="nav nav-pills pull-right">
    <li class="dropdown">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        <?php echo $this->languages[$this->language] ?><?php if(count($this->languages) > 1): ?> <b class="caret"></b><?php endif ?>

      </a>
<?php if(count($this->languages) > 1): ?>
      <ul class="dropdown-menu pull-right">
<?php foreach($this->languages as $id => $name): ?>
<?php if($id !== $this->language): ?>
        <li><?php echo CHtml::link(CHtml::encode($name), array('view', 'version' => $this->version, 'page' => $this->topic, 'lang' => $id)) ?></li>
<?php endif ?>
<?php endforeach ?>
      </ul>
<?php endif ?>
    </li>
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

<div class="row">
  <div class="span9 pull-right" id="content">
<?php echo $content ?>

  </div>
  
  <div class="span3" id="sidebar">
    <nav class="toc well">
      <ul class="nav nav-list">
<?php foreach($this->getTopics() as $title => $topics): ?>
        <li class="nav-header"><?php echo $title ?></li>
<?php foreach($topics as $path => $text): ?>
        <li<?php if($path === $this->topic): ?> class="active"<?php endif ?>>
          <?php echo CHtml::link(CHtml::encode($text), array('view', 'version' => $this->version, 'page' => $path, 'lang' => $this->language)) ?>

        </li>
<?php endforeach ?>
<?php endforeach ?>
      </ul>
    </nav>
  </div>
</div>