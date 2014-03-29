<?php

// ensure we get report on all possible php errors
error_reporting(-1);

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../framework/yiit.php';

require_once($yiit);
require_once(dirname(__FILE__).'/TestCase.php');
require_once(dirname(__FILE__).'/DbTestCase.php');
require_once(dirname(__FILE__).'/WebTestCase.php');
