<?php
date_default_timezone_set('GMT');

define('WEB_ROOT', dirname(__FILE__) . '/../');

// Paths that need to be writable
$paths = array(
  'assets'  => WEB_ROOT . 'assets',
  'config'  => WEB_ROOT . 'application/config',
  'demos'   => WEB_ROOT . 'demos',
  'games'   => WEB_ROOT . 'images/games',
  'maps'    => WEB_ROOT . 'images/maps',
  'runtime' => WEB_ROOT . 'application/runtime',
);