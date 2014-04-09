<?php

// ensure we get report on all possible php errors
error_reporting(-1);

// disable Yii error handling logic
defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER',false);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER',false);

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../framework/yii.php';

require_once($yiit);
require_once(dirname(__FILE__).'/TestCase.php');
require_once(dirname(__FILE__).'/DbTestCase.php');
