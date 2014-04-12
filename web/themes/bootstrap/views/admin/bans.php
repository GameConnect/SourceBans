<?php
/* @var $this AdminController */
/* @var $ban SBBan */
/* @var $appeals SBAppeal[] */
/* @var $reports SBReport[] */
?>

<?php if(Yii::app()->user->data->hasPermission('ADD_BANS')): ?>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/bans/_form', array(
	'action'=>array('bans/add'),
	'demo'=>$demo,
	'model'=>$ban,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-import">
<?php echo $this->renderPartial('/bans/_import') ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('BAN_APPEALS')): ?>
    <section class="tab-pane fade" id="pane-appeals">
      <ul class="menu nav nav-pills">
        <li class="active"><a href="#appeals"><?php echo Yii::t('sourcebans', 'views.admin.bans.appeals.active.title') ?></a></li>
        <li><a href="#appeals/archive"><?php echo Yii::t('sourcebans', 'views.admin.bans.appeals.archive.title') ?></a></li>
      </ul>
<?php echo $this->renderPartial('/appeals/_admin', array(
	'model'=>$appeals,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-appeals-archive">
      <ul class="menu nav nav-pills">
        <li><a href="#appeals"><?php echo Yii::t('sourcebans', 'views.admin.bans.appeals.active.title') ?></a></li>
        <li class="active"><a href="#appeals/archive"><?php echo Yii::t('sourcebans', 'views.admin.bans.appeals.archive.title') ?></a></li>
      </ul>
<?php echo $this->renderPartial('/appeals/_admin', array(
	'archive'=>true,
	'model'=>$appeals,
)) ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('BAN_REPORTS')): ?>
    <section class="tab-pane fade" id="pane-reports">
      <ul class="menu nav nav-pills">
        <li class="active"><a href="#reports"><?php echo Yii::t('sourcebans', 'views.admin.bans.reports.active.title') ?></a></li>
        <li><a href="#reports/archive"><?php echo Yii::t('sourcebans', 'views.admin.bans.reports.archive.title') ?></a></li>
      </ul>
<?php echo $this->renderPartial('/reports/_admin', array(
	'model'=>$reports,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-reports-archive">
      <ul class="menu nav nav-pills">
        <li><a href="#reports"><?php echo Yii::t('sourcebans', 'views.admin.bans.reports.active.title') ?></a></li>
        <li class="active"><a href="#reports/archive"><?php echo Yii::t('sourcebans', 'views.admin.bans.reports.archive.title') ?></a></li>
      </ul>
<?php echo $this->renderPartial('/reports/_admin', array(
	'archive'=>true,
	'model'=>$reports,
)) ?>

    </section>
<?php endif ?>