<?php

Yii::setPathOfAlias('bootstrap',dirname(__FILE__).'/../extensions/bootstrap');

$parent=require(dirname(__FILE__).'/sourcebans.php');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return CMap::mergeArray(
	array(
		'onBeginRequest'=>array('SourceBans', 'onBeginRequest'),
		'onEndRequest'=>array('SourceBans', 'onEndRequest'),
		'theme'=>'bootstrap',

		'modules'=>array(
			// uncomment the following to enable the Gii tool
			/*
	 		'gii'=>array(
	 			'class'=>'system.gii.GiiModule',
				'password'=>'Enter Your Password Here',
	 			// If removed, Gii defaults to localhost only. Edit carefully to taste.
	 			'ipFilters'=>array('127.0.0.1','::1'),
	 			'generatorPaths'=>array(
	 				'bootstrap.gii',
	 			),
	 		),
			*/
		),

		// application components
		'components'=>array(
			'clientScript'=>array(
				'coreScriptPosition'=>CClientScript::POS_END,
				'defaultScriptFilePosition'=>CClientScript::POS_END,
				'defaultScriptPosition'=>CClientScript::POS_END,
			),
			'errorHandler'=>array(
				// use 'site/error' action to display errors
				'errorAction'=>'site/error',
			),
			'log'=>array(
				'routes'=>array(
					// uncomment the following to show log messages on web pages
					/*
					array(
						'class'=>'CWebLogRoute',
					),
					*/
				),
			),
			'urlManager'=>array(
				'urlFormat'=>'path',
				'showScriptName'=>!(defined('PRETTY_URLS') && PRETTY_URLS),
				'rules'=>array(
					''=>'site/index',
					'admin'=>'admin/index',
					'<action>'=>'site/<action>',
					'<controller:\w+>/<id:\d+>'=>'<controller>/view',
					'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
					'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
				),
			),
			'user'=>array(
				'class'=>'WebUser',
				// enable cookie-based authentication
				'allowAutoLogin'=>true,
			),
			// extensions
			'bootstrap'=>array(
				'class'=>'bootstrap.components.Bootstrap',
			),
		),

		// application-level parameters that can be accessed
		// using Yii::app()->params['paramName']
		'params'=>array(
			'titleSeparator'=>' « ',
		),
	),
	$parent
);