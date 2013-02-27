<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/framework/yii.php';
$config=dirname(__FILE__).'/application/config/api.php';

require_once($yii);
Yii::setPathOfAlias('application',dirname($config).DIRECTORY_SEPARATOR.'..');
Yii::import('application.components.CApiApplication');
Yii::createApplication('CApiApplication',$config)->run();
