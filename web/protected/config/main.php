<?php

Yii::setPathOfAlias('bootstrap',dirname(__FILE__).'/../extensions/bootstrap');

date_default_timezone_set('Europe/Amsterdam');

$db=require dirname(__FILE__).'/database.php';

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'language'=>'en',
	'name'=>'SourceBans',
	'theme'=>'bootstrap',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

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
		'cache'=>array(
			'class'=>'CFileCache',
		),
		'clientScript'=>array(
			'coreScriptPosition'=>CClientScript::POS_END,
			'defaultScriptFilePosition'=>CClientScript::POS_END,
			'defaultScriptPosition'=>CClientScript::POS_END,
		),
		'db'=>array(
			'connectionString'=>'mysql:host='.$db['host'].';port='.$db['port'].';dbname='.$db['name'],
			'emulatePrepare'=>true,
			'username'=>$db['user'],
			'password'=>$db['pass'],
			'charset'=>'utf8',
			'tablePrefix'=>$db['prefix'],
			'autoConnect'=>false,
			'schemaCachingDuration'=>86400,
		),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'format'=>array(
			'class'=>'Formatter',
			'datetimeFormat'=>'m-d-y H:i',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'rules'=>array(
				''=>'site/index',
				'admin'=>'admin/index',
				'<action>'=>'site/<action>',
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
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
);