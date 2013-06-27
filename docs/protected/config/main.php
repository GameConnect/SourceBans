<?php

date_default_timezone_set('Europe/London');

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'SourceBans Documentation',
	'defaultController'=>'guide',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.components.*',
	),

	// application components
	'components'=>array(
		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'api/search'=>'api/search',
				'guide/<version:\d+\.\d+>/<lang:\w+>/<page>'=>'guide/view',
				'guide/<version:\d+\.\d+>/<lang:\w+>'=>'guide/view',
				'<controller>/<version:\d+\.\d+>'=>'<controller>/view',
				'<controller>/<page>'=>'<controller>/view',
				'<controller>'=>'<controller>/view',
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'defaultVersion'=>'2.0',
	),
);