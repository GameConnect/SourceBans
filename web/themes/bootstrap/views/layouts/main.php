<?php Yii::app()->bootstrap->registerCoreCss() ?>
<?php Yii::app()->bootstrap->registerYiiCss() ?>
<?php Yii::app()->bootstrap->registerCoreScripts() ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/css/style.css') ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/sourcebans.js') ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/template.js') ?>
<?php Yii::app()->clientScript->registerLinkTag('shortcut icon', 'image/x-icon', Yii::app()->baseUrl . '/images/favicon.ico') ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::app()->language ?>">
  <head>
    <title><?php if(!empty($this->pageTitle)) echo CHtml::encode($this->pageTitle . Yii::app()->params['titleSeparator']) ?><?php echo CHtml::encode(Yii::app()->name) ?></title>
    <meta charset="UTF-8" />
  </head>
  <body class="container" id="<?php echo (!empty($this->pageId) ? $this->pageId : $this->id . '_' . $this->action->id); ?>">
    <header>
      <h1><?php echo CHtml::encode(Yii::app()->name) ?></h1>
      <div class="user">
<?php $this->widget('zii.widgets.CMenu', array(
	'id' => 'user',
	'items' => array(
		array(
			'active' => true,
			'label' => Yii::app()->user->name,
			'url' => array('site/account'),
			'visible' => !Yii::app()->user->isGuest,
		),
		array(
			'label' => Yii::t('sourcebans', 'Logout'),
			'url' => array('site/logout'),
			'visible' => !Yii::app()->user->isGuest,
		),
		array(
			'label' => Yii::t('sourcebans', 'Login'),
			'url' => array('site/login'),
			'visible' => Yii::app()->user->isGuest,
		),
	),
	'htmlOptions' => array(
		'class' => 'nav nav-pills',
	),
)) ?>
      </div>
    </header>
    
    <nav>
<?php $this->widget('zii.widgets.CMenu', array(
	'id' => 'tabs',
	'items' => array(
		array(
			'label' => Yii::t('sourcebans', 'Dashboard'),
			'url' => array('site/dashboard'),
			'linkOptions' => array('title' => Yii::t('sourcebans', 'This page shows an overview of your bans and servers.')),
		),
		array(
			'label' => Yii::t('sourcebans', 'Bans'),
			'url' => array('site/bans'),
			'linkOptions' => array('title' => Yii::t('sourcebans', 'All of the bans in the database can be viewed from here.')),
		),
		array(
			'label' => Yii::t('sourcebans', 'Servers'),
			'url' => array('site/servers'),
			'linkOptions' => array('title' => Yii::t('sourcebans', 'All of your servers and their status can be viewed here.')),
		),
		array(
			'label' => Yii::t('sourcebans', 'Submit ban'),
			'url' => array('site/submitban'),
			'linkOptions' => array('title' => Yii::t('sourcebans', 'You can submit a demo or screenshot of a suspected cheater here. It will then be up for review by one of the admins.')),
			'visible' => SourceBans::app()->settings->enable_submit,
		),
		array(
			'label' => Yii::t('sourcebans', 'Protest ban'),
			'url' => array('site/protestban'),
			'linkOptions' => array('title' => Yii::t('sourcebans', 'Here you can protest your ban. And prove your case as to why you should be unbanned.')),
			'visible' => SourceBans::app()->settings->enable_protest,
		),
		array(
			'label' => Yii::t('sourcebans', 'Administration'),
			'url' => array('admin/index'),
			'linkOptions' => array('title' => Yii::t('sourcebans', 'This is the control panel for SourceBans where you can setup new admins, add new servers, etc.')),
			'visible' => !Yii::app()->user->isGuest,
		),
	),
	'htmlOptions' => array(
		'class' => 'nav nav-tabs',
	),
)) ?>
    </nav>
    
    <header>
      <h2><?php echo CHtml::encode($this->pageTitle) ?></h2>
<?php $this->widget('bootstrap.widgets.TbBreadcrumbs', array(
	'links' => $this->breadcrumbs,
)) ?>
    </header>
    
<?php echo $content ?>

    
    <footer>
      <strong><?php echo Yii::t('sourcebans', 'Version') ?> <?php echo SourceBans::app()->version ?></strong>
      <p>"<?php echo SourceBans::app()->quote->text ?>" - <em><?php echo SourceBans::app()->quote->name ?></em></p>
    </footer>
  </body>
</html>